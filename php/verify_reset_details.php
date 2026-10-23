<?php
session_start();
require_once __DIR__ . "/db_connect_mysqli.php";

header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store, no-cache, must-revalidate");

function respond($success, $message, $status = 200)
{
    http_response_code($status);
    echo json_encode(["success" => $success, "message" => $message]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond(false, "Invalid request method.", 405);
}

$input = json_decode(file_get_contents("php://input"), true);

if (!is_array($input)) {
    respond(false, "Invalid request data.", 400);
}

$enrollment = trim($input["enrollment"] ?? "");
$email = trim($input["email"] ?? "");
$mobile = trim($input["mobile"] ?? "");

if ($enrollment === "" || $email === "" || $mobile === "") {
    respond(false, "Please provide all three account details.", 400);
}

if (strlen($enrollment) > 50 || strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match("/^[0-9]{10}$/", $mobile)) {
    respond(false, "Please enter valid account details.", 400);
}

/* LIMIT REPEATED VERIFICATION ATTEMPTS IN THIS SESSION */
if (!isset($_SESSION["reset_attempts"]) || !is_array($_SESSION["reset_attempts"])) {
    $_SESSION["reset_attempts"] = ["count" => 0, "started" => time()];
}

if (time() - $_SESSION["reset_attempts"]["started"] >= 900) {
    $_SESSION["reset_attempts"] = ["count" => 0, "started" => time()];
}

if ($_SESSION["reset_attempts"]["count"] >= 5) {
    respond(false, "Too many attempts. Please try again in 15 minutes.", 429);
}

$_SESSION["reset_attempts"]["count"]++;

$stmt = $conn->prepare(
    "SELECT id FROM students WHERE enrollment = ? AND email = ? AND mobile = ? LIMIT 1"
);
$stmt->bind_param("sss", $enrollment, $email, $mobile);
$stmt->execute();
$stmt->bind_result($userId);
$matched = $stmt->fetch();
$stmt->close();
$conn->close();

if (!$matched) {
    unset($_SESSION["password_reset_user_id"], $_SESSION["password_reset_expires"]);
    respond(false, "The supplied details could not be verified. Please check them and try again.", 400);
}

/* CREATE TEMPORARY SERVER-SIDE RESET AUTHORIZATION */
session_regenerate_id(true);
$_SESSION["password_reset_user_id"] = (int) $userId;
$_SESSION["password_reset_expires"] = time() + 600;
$_SESSION["password_reset_verified"] = true;
$_SESSION["reset_attempts"] = ["count" => 0, "started" => time()];

respond(true, "Account details verified successfully.");