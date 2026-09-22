<?php
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: register.html");
    exit();
}

$fullName = trim($_POST["fullName"] ?? "");
$enrollment = trim($_POST["enrollment"] ?? "");
$email = trim($_POST["email"] ?? "");
$mobile = trim($_POST["mobile"] ?? "");
$password = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirmPassword"] ?? "";
$course = $_POST["course"] ?? "";
$year = $_POST["year"] ?? "";
$gender = $_POST["gender"] ?? "";
$agree = isset($_POST["agree"]);
$errors = [];

if ($fullName === "") {
    $errors["fullName"] = "Full Name is required.";
} elseif (strlen($fullName) < 2) {
    $errors["fullName"] = "Full Name must contain at least 2 characters.";
} elseif (strlen($fullName) > 100) {
    $errors["fullName"] = "Full Name cannot exceed 100 characters.";
} elseif (!preg_match("/^[a-zA-Z .'-]+$/", $fullName)) {
    $errors["fullName"] = "Full Name can contain only letters and spaces.";
}

if ($enrollment === "") {
    $errors["enrollment"] = "Enrollment Number is required.";
} elseif (strlen($enrollment) < 3) {
    $errors["enrollment"] = "Enrollment Number must contain at least 3 characters.";
} elseif (strlen($enrollment) > 50) {
    $errors["enrollment"] = "Enrollment Number cannot exceed 50 characters.";
} elseif (!preg_match("/^[a-zA-Z0-9\/_-]+$/", $enrollment)) {
    $errors["enrollment"] = "Enrollment Number contains invalid characters.";
}

if ($email === "") {
    $errors["email"] = "Email Address is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors["email"] = "Please enter a valid email address.";
} elseif (strlen($email) > 150) {
    $errors["email"] = "Email Address cannot exceed 150 characters.";
}

if ($mobile === "") {
    $errors["mobile"] = "Mobile Number is required.";
} elseif (!preg_match("/^[0-9]+$/", $mobile)) {
    $errors["mobile"] = "Mobile Number must contain only digits.";
} elseif (strlen($mobile) !== 10) {
    $errors["mobile"] = "Mobile Number must contain exactly 10 digits.";
} elseif ($mobile[0] === "0") {
    $errors["mobile"] = "Mobile Number cannot start with 0.";
}

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

if ($confirmPassword === "") {
    $errors["confirmPassword"] = "Please confirm your password.";
} elseif ($password !== $confirmPassword) {
    $errors["confirmPassword"] = "Passwords do not match.";
}

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

$allowedYears = ["1", "2", "3", "4"];

if ($year === "") {
    $errors["year"] = "Please select your year.";
} elseif (!in_array($year, $allowedYears, true)) {
    $errors["year"] = "Please select a valid year.";
}

$allowedGenders = ["Male", "Female", "Other"];

if ($gender === "") {
    $errors["gender"] = "Please select your gender.";
} elseif (!in_array($gender, $allowedGenders, true)) {
    $errors["gender"] = "Please select a valid gender.";
}

if (!$agree) {
    $errors["terms"] = "You must agree to the Terms & Conditions and Privacy Policy.";
}

if (!empty($errors)) {
    redirectWithErrors($errors);
}

$csvFile = __DIR__ . "/students.csv";

if (file_exists($csvFile)) {
    $file = fopen($csvFile, "r");

    if ($file === false) {
        redirectWithErrors(["general" => "Unable to open students.csv."]);
    }

    fgetcsv($file);

    while (($row = fgetcsv($file)) !== false) {
        if (count($row) < 10) {
            continue;
        }

        if (strtolower(trim($row[2])) === strtolower($enrollment)) {
            fclose($file);
            redirectWithErrors(["enrollment" => "This Enrollment Number already exists."]);
        }

        if (strtolower(trim($row[3])) === strtolower($email)) {
            fclose($file);
            redirectWithErrors(["email" => "This Email Address is already registered."]);
        }

        if (trim($row[4]) === $mobile) {
            fclose($file);
            redirectWithErrors(["mobile" => "This Mobile Number is already registered."]);
        }
    }

    fclose($file);
}

if (!file_exists($csvFile)) {
    $file = fopen($csvFile, "w");

    if ($file === false) {
        redirectWithErrors(["general" => "Unable to create students.csv."]);
    }

    fputcsv($file, [
        "id",
        "full_name",
        "enrollment",
        "email",
        "mobile",
        "password",
        "course",
        "year",
        "gender",
        "created_at"
    ]);

    fclose($file);
}

$id = 1;
$file = fopen($csvFile, "r");

if ($file !== false) {
    fgetcsv($file);

    while (($row = fgetcsv($file)) !== false) {
        if (isset($row[0]) && is_numeric($row[0])) {
            $currentId = (int)$row[0];

            if ($currentId >= $id) {
                $id = $currentId + 1;
            }
        }
    }

    fclose($file);
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$createdAt = date("Y-m-d H:i:s");

$file = fopen($csvFile, "a");

if ($file === false) {
    redirectWithErrors(["general" => "Unable to open students.csv for writing."]);
}

$result = fputcsv($file, [
    $id,
    $fullName,
    $enrollment,
    $email,
    $mobile,
    $hashedPassword,
    $course,
    $year,
    $gender,
    $createdAt
]);

fclose($file);

if ($result === false) {
    redirectWithErrors(["general" => "Registration failed. Data could not be saved."]);
}

header("Location: register.html?success=" . urlencode("Your account has been created successfully."));
exit();

function redirectWithErrors($errors) {
    $json = json_encode($errors, JSON_UNESCAPED_UNICODE);
    $encoded = urlencode($json);
    header("Location: register.html?errors=" . $encoded);
    exit();
}
?>

