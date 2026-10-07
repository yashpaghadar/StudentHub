<?php

require "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pages/register.html");
    exit();
}

/* =========================================================
   GET FORM DATA
========================================================= */

$fullName = trim($_POST["fullName"] ?? "");
$enrollment = trim($_POST["enrollment"] ?? "");
$email = trim($_POST["email"] ?? "");
$mobile = trim($_POST["mobile"] ?? "");
$password = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirmPassword"] ?? "";
$course = trim($_POST["course"] ?? "");
$year = $_POST["year"] ?? "";
$gender = $_POST["gender"] ?? "";
$agree = isset($_POST["agree"]);

$errors = [];


/* =========================================================
   FULL NAME VALIDATION
========================================================= */

if ($fullName === "") {

    $errors["fullName"] = "Full Name is required.";

} elseif (strlen($fullName) < 2) {

    $errors["fullName"] = "Full Name must contain at least 2 characters.";

} elseif (strlen($fullName) > 100) {

    $errors["fullName"] = "Full Name cannot exceed 100 characters.";

} elseif (!preg_match("/^[a-zA-Z .'-]+$/", $fullName)) {

    $errors["fullName"] = "Full Name can contain only letters and spaces.";

}


/* =========================================================
   ENROLLMENT VALIDATION
========================================================= */

if ($enrollment === "") {

    $errors["enrollment"] = "Enrollment Number is required.";

} elseif (strlen($enrollment) < 3) {

    $errors["enrollment"] = "Enrollment Number must contain at least 3 characters.";

} elseif (strlen($enrollment) > 50) {

    $errors["enrollment"] = "Enrollment Number cannot exceed 50 characters.";

} elseif (!preg_match("/^[a-zA-Z0-9\/_-]+$/", $enrollment)) {

    $errors["enrollment"] = "Enrollment Number contains invalid characters.";

}


/* =========================================================
   EMAIL VALIDATION
========================================================= */

if ($email === "") {

    $errors["email"] = "Email Address is required.";

} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    $errors["email"] = "Please enter a valid email address.";

} elseif (strlen($email) > 150) {

    $errors["email"] = "Email Address cannot exceed 150 characters.";

}


/* =========================================================
   MOBILE VALIDATION
========================================================= */

if ($mobile === "") {

    $errors["mobile"] = "Mobile Number is required.";

} elseif (!preg_match("/^[0-9]+$/", $mobile)) {

    $errors["mobile"] = "Mobile Number must contain only digits.";

} elseif (strlen($mobile) !== 10) {

    $errors["mobile"] = "Mobile Number must contain exactly 10 digits.";

} elseif ($mobile[0] === "0") {

    $errors["mobile"] = "Mobile Number cannot start with 0.";

}


/* =========================================================
   PASSWORD VALIDATION
========================================================= */

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


/* =========================================================
   CONFIRM PASSWORD VALIDATION
========================================================= */

if ($confirmPassword === "") {

    $errors["confirmPassword"] = "Please confirm your password.";

} elseif ($password !== $confirmPassword) {

    $errors["confirmPassword"] = "Passwords do not match.";

}


/* =========================================================
   COURSE VALIDATION
========================================================= */

$allowedCourses = [
    "B.Tech IT",
    "B.Tech CSE",
    "BCA",
    "MCA",
    "Diploma IT"
];

if ($course === "") {

    $errors["course"] = "Please select your course.";

} elseif (!in_array($course, $allowedCourses, true)) {

    $errors["course"] = "Please select a valid course.";

}


/* =========================================================
   YEAR VALIDATION
========================================================= */

$allowedYears = ["1", "2", "3", "4"];

if ($year === "") {

    $errors["year"] = "Please select your year.";

} elseif (!in_array($year, $allowedYears, true)) {

    $errors["year"] = "Please select a valid year.";

}


/* =========================================================
   GENDER VALIDATION
========================================================= */

$allowedGenders = [
    "Male",
    "Female",
    "Other"
];

if ($gender === "") {

    $errors["gender"] = "Please select your gender.";

} elseif (!in_array($gender, $allowedGenders, true)) {

    $errors["gender"] = "Please select a valid gender.";

}


/* =========================================================
   TERMS & CONDITIONS
========================================================= */

if (!$agree) {

    $errors["terms"] =
        "You must agree to the Terms & Conditions and Privacy Policy.";

}


/* =========================================================
   STOP IF VALIDATION ERRORS EXIST
========================================================= */

if (!empty($errors)) {

    redirectWithErrors($errors);

}


/* =========================================================
   CHECK DUPLICATE ENROLLMENT
========================================================= */

$stmt = $pdo->prepare(
    "SELECT id
     FROM students
     WHERE enrollment = :enrollment
     LIMIT 1"
);

$stmt->execute([
    ":enrollment" => $enrollment
]);

if ($stmt->fetch()) {

    redirectWithErrors([
        "enrollment" => "This Enrollment Number already exists."
    ]);

}


/* =========================================================
   CHECK DUPLICATE EMAIL
========================================================= */

$stmt = $pdo->prepare(
    "SELECT id
     FROM students
     WHERE email = :email
     LIMIT 1"
);

$stmt->execute([
    ":email" => $email
]);

if ($stmt->fetch()) {

    redirectWithErrors([
        "email" => "This Email Address is already registered."
    ]);

}


/* =========================================================
   CHECK DUPLICATE MOBILE
========================================================= */

$stmt = $pdo->prepare(
    "SELECT id
     FROM students
     WHERE mobile = :mobile
     LIMIT 1"
);

$stmt->execute([
    ":mobile" => $mobile
]);

if ($stmt->fetch()) {

    redirectWithErrors([
        "mobile" => "This Mobile Number is already registered."
    ]);

}


/* =========================================================
   GET COURSE ID
========================================================= */

$stmt = $pdo->prepare(
    "SELECT course_id
     FROM courses
     WHERE course_name = :course
     LIMIT 1"
);

$stmt->execute([
    ":course" => $course
]);

$courseData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$courseData) {

    redirectWithErrors([
        "course" => "Selected course was not found."
    ]);

}

$courseId = $courseData["course_id"];


/* =========================================================
   HASH PASSWORD
========================================================= */

$hashedPassword = password_hash(
    $password,
    PASSWORD_DEFAULT
);


/* =========================================================
   INSERT STUDENT INTO MYSQL
========================================================= */

$sql = "INSERT INTO students
        (
            full_name,
            enrollment,
            email,
            mobile,
            password,
            course_id,
            year,
            gender
        )
        VALUES
        (
            :full_name,
            :enrollment,
            :email,
            :mobile,
            :password,
            :course_id,
            :year,
            :gender
        )";

$stmt = $pdo->prepare($sql);

try {

    $stmt->execute([
        ":full_name" => $fullName,
        ":enrollment" => $enrollment,
        ":email" => $email,
        ":mobile" => $mobile,
        ":password" => $hashedPassword,
        ":course_id" => $courseId,
        ":year" => $year,
        ":gender" => $gender
    ]);

    header(
        "Location: ../pages/register.html?success=" .
        urlencode("Your account has been created successfully.")
    );

    exit();

} catch (PDOException $e) {

    redirectWithErrors([
        "general" => "Registration failed. Please try again."
    ]);

}


/* =========================================================
   REDIRECT WITH ERRORS
========================================================= */

function redirectWithErrors($errors)
{
    $json = json_encode(
        $errors,
        JSON_UNESCAPED_UNICODE
    );

    $encoded = urlencode($json);

    header(
        "Location: ../pages/register.html?errors=" .
        $encoded
    );

    exit();
}

?>