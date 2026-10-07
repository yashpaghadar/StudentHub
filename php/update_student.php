<?php

require "db_connect.php";
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendError("Invalid request method.", 405);
}

$input = json_decode(file_get_contents("php://input"), true);

if (!is_array($input)) {
    sendError("Invalid request data.");
}

$id = (int)($input["id"] ?? 0);
$field = trim($input["field"] ?? "");

if ($id <= 0) {
    sendError("Invalid student ID.");
}

$stmt = $pdo->prepare(
    "SELECT id FROM students WHERE id = :id LIMIT 1"
);
$stmt->execute([":id" => $id]);

if (!$stmt->fetch()) {
    sendError("Student not found.", 404);
}


/* =========================================================
   COMPLETE STUDENT UPDATE
========================================================= */

if ($field === "complete") {

    $fullName = trim($input["full_name"] ?? "");
    $enrollment = trim($input["enrollment"] ?? "");
    $email = trim($input["email"] ?? "");
    $mobile = trim($input["mobile"] ?? "");
    $course = trim($input["course"] ?? "");
    $year = trim($input["year"] ?? "");
    $gender = trim($input["gender"] ?? "");

    validateStudentData(
        $fullName,
        $enrollment,
        $email,
        $mobile,
        $course,
        $year,
        $gender
    );

    checkDuplicates(
        $pdo,
        $id,
        $enrollment,
        $email,
        $mobile
    );

    $stmt = $pdo->prepare(
        "SELECT course_id
         FROM courses
         WHERE course_name = :course
         LIMIT 1"
    );

    $stmt->execute([":course" => $course]);
    $courseData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$courseData) {
        sendError("Selected course was not found.");
    }

    try {

        $stmt = $pdo->prepare(
            "UPDATE students SET
                full_name = :full_name,
                enrollment = :enrollment,
                email = :email,
                mobile = :mobile,
                course_id = :course_id,
                year = :year,
                gender = :gender
             WHERE id = :id"
        );

        $stmt->execute([
            ":full_name" => $fullName,
            ":enrollment" => $enrollment,
            ":email" => $email,
            ":mobile" => $mobile,
            ":course_id" => $courseData["course_id"],
            ":year" => $year,
            ":gender" => $gender,
            ":id" => $id
        ]);

        sendSuccess("Student data updated successfully.");

    } catch (PDOException $e) {
        sendError("Unable to update student data.", 500);
    }
}


/* =========================================================
   INDIVIDUAL FIELD UPDATE
========================================================= */

$allowedFields = [
    "full_name",
    "enrollment",
    "email",
    "mobile",
    "course",
    "year",
    "gender"
];

if (!in_array($field, $allowedFields, true)) {
    sendError("Invalid field selected.");
}

$value = trim($input["value"] ?? "");

if ($value === "") {
    sendError("Please enter a value.");
}


/* =========================================================
   FIELD VALIDATION
========================================================= */

if ($field === "full_name") {

    if (
        strlen($value) < 2 ||
        strlen($value) > 100 ||
        !preg_match("/^[a-zA-Z .'-]+$/", $value)
    ) {
        sendError("Please enter a valid Full Name.");
    }
}


if ($field === "enrollment") {

    if (
        strlen($value) < 3 ||
        strlen($value) > 50 ||
        !preg_match("/^[a-zA-Z0-9\/_-]+$/", $value)
    ) {
        sendError("Please enter a valid Enrollment Number.");
    }

    checkDuplicate(
        $pdo,
        "enrollment",
        $value,
        $id,
        "This Enrollment Number is already used."
    );
}


if ($field === "email") {

    if (
        !filter_var($value, FILTER_VALIDATE_EMAIL) ||
        strlen($value) > 150
    ) {
        sendError("Please enter a valid Email Address.");
    }

    checkDuplicate(
        $pdo,
        "email",
        $value,
        $id,
        "This Email Address is already used."
    );
}


if ($field === "mobile") {

    if (
        !preg_match("/^[0-9]{10}$/", $value) ||
        $value[0] === "0"
    ) {
        sendError(
            "Mobile Number must contain exactly 10 digits and cannot start with 0."
        );
    }

    checkDuplicate(
        $pdo,
        "mobile",
        $value,
        $id,
        "This Mobile Number is already used."
    );
}


if ($field === "course") {

    $allowedCourses = [
        "B.Tech IT",
        "B.Tech CSE",
        "BCA",
        "MCA",
        "Diploma IT"
    ];

    if (!in_array($value, $allowedCourses, true)) {
        sendError("Please select a valid course.");
    }

    $stmt = $pdo->prepare(
        "SELECT course_id
         FROM courses
         WHERE course_name = :course
         LIMIT 1"
    );

    $stmt->execute([":course" => $value]);
    $courseData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$courseData) {
        sendError("Selected course was not found.");
    }

    try {

        $stmt = $pdo->prepare(
            "UPDATE students
             SET course_id = :course_id
             WHERE id = :id"
        );

        $stmt->execute([
            ":course_id" => $courseData["course_id"],
            ":id" => $id
        ]);

        sendSuccess("Course updated successfully.");

    } catch (PDOException $e) {
        sendError("Unable to update course.", 500);
    }
}


if ($field === "year") {

    if (!in_array($value, ["1", "2", "3", "4"], true)) {
        sendError("Please select a valid year.");
    }
}


if ($field === "gender") {

    if (!in_array($value, ["Male", "Female", "Other"], true)) {
        sendError("Please select a valid gender.");
    }
}


/* =========================================================
   UPDATE SIMPLE FIELD
========================================================= */

try {

    $stmt = $pdo->prepare(
        "UPDATE students
         SET `$field` = :value
         WHERE id = :id"
    );

    $stmt->execute([
        ":value" => $value,
        ":id" => $id
    ]);

    sendSuccess("Student data updated successfully.");

} catch (PDOException $e) {

    sendError("Unable to update student data.", 500);
}


/* =========================================================
   FUNCTIONS
========================================================= */

function validateStudentData(
    $fullName,
    $enrollment,
    $email,
    $mobile,
    $course,
    $year,
    $gender
) {

    if (
        strlen($fullName) < 2 ||
        strlen($fullName) > 100 ||
        !preg_match("/^[a-zA-Z .'-]+$/", $fullName)
    ) {
        sendError("Please enter a valid Full Name.");
    }

    if (
        strlen($enrollment) < 3 ||
        strlen($enrollment) > 50 ||
        !preg_match("/^[a-zA-Z0-9\/_-]+$/", $enrollment)
    ) {
        sendError("Please enter a valid Enrollment Number.");
    }

    if (
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 150
    ) {
        sendError("Please enter a valid Email Address.");
    }

    if (
        !preg_match("/^[0-9]{10}$/", $mobile) ||
        $mobile[0] === "0"
    ) {
        sendError(
            "Mobile Number must contain exactly 10 digits and cannot start with 0."
        );
    }

    $courses = [
        "B.Tech IT",
        "B.Tech CSE",
        "BCA",
        "MCA",
        "Diploma IT"
    ];

    if (!in_array($course, $courses, true)) {
        sendError("Please select a valid course.");
    }

    if (!in_array($year, ["1", "2", "3", "4"], true)) {
        sendError("Please select a valid year.");
    }

    if (!in_array($gender, ["Male", "Female", "Other"], true)) {
        sendError("Please select a valid gender.");
    }
}


function checkDuplicates(
    $pdo,
    $id,
    $enrollment,
    $email,
    $mobile
) {

    checkDuplicate(
        $pdo,
        "enrollment",
        $enrollment,
        $id,
        "This Enrollment Number is already used."
    );

    checkDuplicate(
        $pdo,
        "email",
        $email,
        $id,
        "This Email Address is already used."
    );

    checkDuplicate(
        $pdo,
        "mobile",
        $mobile,
        $id,
        "This Mobile Number is already used."
    );
}


function checkDuplicate(
    $pdo,
    $field,
    $value,
    $id,
    $message
) {

    $allowed = [
        "enrollment",
        "email",
        "mobile"
    ];

    if (!in_array($field, $allowed, true)) {
        sendError("Invalid field.");
    }

    $stmt = $pdo->prepare(
        "SELECT id
         FROM students
         WHERE `$field` = :value
         AND id != :id
         LIMIT 1"
    );

    $stmt->execute([
        ":value" => $value,
        ":id" => $id
    ]);

    if ($stmt->fetch()) {
        sendError($message);
    }
}


function sendSuccess($message)
{
    echo json_encode([
        "success" => true,
        "message" => $message
    ]);

    exit();
}


function sendError($message, $code = 400)
{
    http_response_code($code);

    echo json_encode([
        "success" => false,
        "message" => $message
    ]);

    exit();
}

?>