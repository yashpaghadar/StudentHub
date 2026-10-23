<?php
session_start();
require_once __DIR__ . "/db_connect_mysqli.php";

header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store, no-cache, must-revalidate");

function respond($success, $message, $status = 200, $code = null)
{
    http_response_code($status);
    $result = ["success" => $success, "message" => $message];

    if ($code !== null) {
        $result["code"] = $code;
    }

    echo json_encode($result);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond(false, "Invalid request method.", 405);
}

/* VERIFY TEMPORARY RESET AUTHORIZATION */
$userId = $_SESSION["password_reset_user_id"] ?? 0;
$expires = $_SESSION["password_reset_expires"] ?? 0;
$verified = $_SESSION["password_reset_verified"] ?? false;

if (!$verified || !is_numeric($userId) || (int) $userId <= 0 || !is_numeric($expires) || time() > (int) $expires) {
    unset(
        $_SESSION["password_reset_user_id"],
        $_SESSION["password_reset_expires"],
        $_SESSION["password_reset_verified"]
    );
    respond(false, "Your verification has expired. Please verify your account details again.", 401, "verification_expired");
}

$input = json_decode(file_get_contents("php://input"), true);

if (!is_array($input)) {
    respond(false, "Invalid request data.", 400);
}

$password = $input["new_password"] ?? "";
$confirmPassword = $input["confirm_new_password"] ?? "";

/* VALIDATE NEW PASSWORD */
if (!is_string($password) || !is_string($confirmPassword) || $password === "" || $confirmPassword === "") {
    respond(false, "Both password fields are required.", 400);
}

if (strlen($password) < 8 || strlen($password) > 100) {
    respond(false, "Password must contain between 8 and 100 characters.", 400);
}

if (!preg_match("/[A-Z]/", $password) || !preg_match("/[a-z]/", $password) || !preg_match("/[0-9]/", $password) || !preg_match("/[\W_]/", $password)) {
    respond(false, "Password must include uppercase, lowercase, a number and a special character.", 400);
}

if ($password !== $confirmPassword) {
    respond(false, "New password and confirm password do not match.", 400);
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

if ($hashedPassword === false) {
    respond(false, "Unable to secure your password. Please try again.", 500);
}

/* UPDATE PASSWORD */
try {
    $conn->begin_transaction();

    $stmt = $conn->prepare("UPDATE students SET password = ? WHERE id = ? LIMIT 1");
    $userId = (int) $userId;
    $stmt->bind_param("si", $hashedPassword, $userId);
    $stmt->execute();
    $updated = $stmt->affected_rows;
    $stmt->close();

    if ($updated !== 1) {
        $conn->rollback();
        $conn->close();
        unset(
            $_SESSION["password_reset_user_id"],
            $_SESSION["password_reset_expires"],
            $_SESSION["password_reset_verified"]
        );
        respond(false, "The account could not be updated. Please verify your details again.", 400);
    }

    /* REVOKE REMEMBER-ME TOKENS FOR THIS ACCOUNT */
    $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE student_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    $conn->close();

    /* MAKE THE RESET AUTHORIZATION ONE-TIME */
    unset(
        $_SESSION["password_reset_user_id"],
        $_SESSION["password_reset_expires"],
        $_SESSION["password_reset_verified"],
        $_SESSION["reset_attempts"]
    );

    respond(true, "Password reset successfully. You can now log in with your new password.");
} catch (Throwable $e) {
    if (isset($conn) && $conn instanceof mysqli) {
        try {
            $conn->rollback();
            $conn->close();
        } catch (Throwable $ignored) {
        }
    }

    error_log("Password reset failed: " . $e->getMessage());
    respond(false, "Password reset failed. Please try again later.", 500);
}