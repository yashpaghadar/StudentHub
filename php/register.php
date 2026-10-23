<?php
require_once __DIR__ . "/db_connect_mysqli.php";
require_once __DIR__ . "/admin_config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pages/register.html");
    exit();
}

/* VALIDATE REQUESTED ROLE */
$role = strtolower(trim($_POST["role"] ?? "student"));

if (!in_array($role, ["student", "admin"], true)) {
    $conn->close();
    header("Location: ../pages/register.html?error=invalid_role");
    exit();
}

/* VERIFY ADMIN INVITATION CODE */
$invitationCode = $_POST["admin_invitation_code"] ?? "";

if ($role === "admin" && !hash_equals(ADMIN_INVITATION_CODE, $invitationCode)) {
    $conn->close();
    header("Location: ../pages/register.html?error=invalid_admin_code");
    exit();
}

/* GET FORM DATA */
$fullName = trim($_POST["fullName"] ?? "");
$enrollment = trim($_POST["enrollment"] ?? "");
$email = trim($_POST["email"] ?? "");
$mobile = trim($_POST["mobile"] ?? "");
$password = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirmPassword"] ?? "";
$course = filter_var($_POST["course"] ?? "", FILTER_VALIDATE_INT);
$year = trim($_POST["year"] ?? "");
$gender = trim($_POST["gender"] ?? "");
$agree = isset($_POST["agree"]);
$errors = [];

/* VALIDATE FULL NAME */
if ($fullName === "") {
    $errors["fullName"] = "Full Name is required.";
} elseif (strlen($fullName) < 2) {
    $errors["fullName"] = "Full Name must contain at least 2 characters.";
} elseif (strlen($fullName) > 100) {
    $errors["fullName"] = "Full Name cannot exceed 100 characters.";
} elseif (!preg_match("/^[a-zA-Z .'-]+$/", $fullName)) {
    $errors["fullName"] = "Full Name can contain only letters and spaces.";
}

/* VALIDATE ENROLLMENT NUMBER OR EMPLOYEE ID */
$idLabel = $role === "admin" ? "Employee ID" : "Enrollment Number";

if ($enrollment === "") {
    $errors["enrollment"] = "$idLabel is required.";
} elseif (strlen($enrollment) < 3) {
    $errors["enrollment"] = "$idLabel must contain at least 3 characters.";
} elseif (strlen($enrollment) > 50) {
    $errors["enrollment"] = "$idLabel cannot exceed 50 characters.";
} elseif (!preg_match("/^[a-zA-Z0-9\/_-]+$/", $enrollment)) {
    $errors["enrollment"] = "$idLabel contains invalid characters.";
}

/* VALIDATE EMAIL */
if ($email === "") {
    $errors["email"] = "Email Address is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors["email"] = "Please enter a valid email address.";
} elseif (strlen($email) > 150) {
    $errors["email"] = "Email Address cannot exceed 150 characters.";
}

/* VALIDATE MOBILE NUMBER */
if ($mobile === "") {
    $errors["mobile"] = "Mobile Number is required.";
} elseif (!preg_match("/^[0-9]+$/", $mobile)) {
    $errors["mobile"] = "Mobile Number must contain only digits.";
} elseif (strlen($mobile) !== 10) {
    $errors["mobile"] = "Mobile Number must contain exactly 10 digits.";
} elseif ($mobile[0] === "0") {
    $errors["mobile"] = "Mobile Number cannot start with 0.";
}

/* VALIDATE PASSWORD */
if ($password === "") {
    $errors["password"] = "Password is required.";
} elseif (strlen($password) < 8) {
    $errors["password"] = "Password must contain at least 8 characters.";
} elseif (strlen($password) > 100) {
    $errors["password"] = "Password cannot exceed 100 characters.";
} elseif (!preg_match("/[A-Z]/", $password)) {
    $errors["password"] = "Password must contain at least one uppercase letter.";
} elseif (!preg_match("/[a-z]/", $password)) {
    $errors["password"] = "Password must contain at least one lowercase letter.";
} elseif (!preg_match("/[0-9]/", $password)) {
    $errors["password"] = "Password must contain at least one number.";
} elseif (!preg_match("/[\W_]/", $password)) {
    $errors["password"] = "Password must contain at least one special character.";
}

/* CONFIRM PASSWORD */
if ($confirmPassword === "") {
    $errors["confirmPassword"] = "Please confirm your password.";
} elseif ($password !== $confirmPassword) {
    $errors["confirmPassword"] = "Passwords do not match.";
}

/* VALIDATE COURSE AND YEAR FOR STUDENTS ONLY */
$courseId = null;
$dbYear = null;

if ($role === "student") {
    $courseId = ($course !== false && $course !== null && $course > 0) ? $course : 0;

    if ($courseId <= 0) {
        $errors["course"] = "Please select a valid course.";
    } else {
        $stmt = $conn->prepare("SELECT course_id FROM courses WHERE course_id = ? LIMIT 1");
        $stmt->bind_param("i", $courseId);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows !== 1) {
            $errors["course"] = "Please select a valid course.";
        }

        $stmt->close();
    }

    $allowedYears = ["1", "2", "3", "4"];

    if ($year === "") {
        $errors["year"] = "Please select your year.";
    } elseif (!in_array($year, $allowedYears, true)) {
        $errors["year"] = "Please select a valid year.";
    } else {
        $dbYear = $year;
    }
}

/* VALIDATE GENDER */
$allowedGenders = ["Male", "Female", "Other"];

if ($gender === "") {
    $errors["gender"] = "Please select your gender.";
} elseif (!in_array($gender, $allowedGenders, true)) {
    $errors["gender"] = "Please select a valid gender.";
}

/* VALIDATE TERMS */
if (!$agree) {
    $errors["terms"] = "You must agree to the Terms & Conditions and Privacy Policy.";
}

if (!empty($errors)) {
    $conn->close();
    redirectWithErrors($errors);
}

/* CHECK DUPLICATE EMPLOYEE ID OR ENROLLMENT */
$stmt = $conn->prepare("SELECT id FROM students WHERE enrollment = ? LIMIT 1");
$stmt->bind_param("s", $enrollment);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    $conn->close();
    redirectWithErrors(["enrollment" => "This $idLabel already exists."]);
}
$stmt->close();

/* CHECK DUPLICATE EMAIL */
$stmt = $conn->prepare("SELECT id FROM students WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    $conn->close();
    redirectWithErrors(["email" => "This Email Address is already registered."]);
}
$stmt->close();

/* CHECK DUPLICATE MOBILE */
$stmt = $conn->prepare("SELECT id FROM students WHERE mobile = ? LIMIT 1");
$stmt->bind_param("s", $mobile);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    $conn->close();
    redirectWithErrors(["mobile" => "This Mobile Number is already registered."]);
}
$stmt->close();

/* HASH PASSWORD */
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

if ($hashedPassword === false) {
    $conn->close();
    redirectWithErrors(["general" => "Unable to secure your password. Please try again."]);
}

/* INSERT STUDENT OR ADMIN */
$sql = "INSERT INTO students
    (full_name, enrollment, email, mobile, password, course_id, year, gender, role)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "sssssisss",
    $fullName,
    $enrollment,
    $email,
    $mobile,
    $hashedPassword,
    $courseId,
    $dbYear,
    $gender,
    $role
);

try {
    $stmt->execute();
    $stmt->close();
    $conn->close();

    header("Location: ../pages/register.html?success=" . urlencode(
        $role === "admin" ? "Admin account created successfully." : "Student account created successfully."
    ));
    exit();
} catch (mysqli_sql_exception $e) {
    error_log("Registration failed: " . $e->getMessage());
    $errorCode = $e->getCode();
    $stmt->close();
    $conn->close();

    if ($errorCode === 1062) {
        redirectWithErrors(["general" => "This employee ID, enrollment number, email, or mobile number is already registered."]);
    }

    redirectWithErrors(["general" => "Registration failed. Please check your details and try again."]);
}

/* REDIRECT WITH VALIDATION ERRORS */
function redirectWithErrors($errors)
{
    $json = json_encode($errors, JSON_UNESCAPED_UNICODE);
    header("Location: ../pages/register.html?errors=" . urlencode($json));
    exit();
}
