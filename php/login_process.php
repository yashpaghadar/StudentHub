<?php
session_start();
require_once __DIR__ . "/db_connect_mysqli.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pages/login.html");
    exit();
}

$enrollment = trim($_POST["enrollment"] ?? "");
$password = $_POST["password"] ?? "";
$rememberMe = isset($_POST["remember_me"]) && $_POST["remember_me"] === "1";

if ($enrollment === "" || $password === "") {
    header("Location: ../pages/login.html?error=empty");
    exit();
}
$stmt = $conn->prepare(
    "SELECT id, full_name, enrollment, password, role FROM students WHERE enrollment = ? LIMIT 1"
);
$stmt->bind_param("s", $enrollment);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student || !password_verify($password, $student["password"])) {
    $conn->close();
    header("Location: ../pages/login.html?error=invalid");
    exit();
}

$role = strtolower(trim($student["role"]));

if (!in_array($role, ["student", "admin"], true)) {
    $conn->close();
    header("Location: ../pages/login.html?error=invalid");
    exit();
}

session_regenerate_id(true);

$_SESSION["user_id"] = (int) $student["id"];
$_SESSION["enrollment"] = $student["enrollment"];
$_SESSION["full_name"] = $student["full_name"];
$_SESSION["role"] = $role;
$_SESSION["logged_in"] = true;
$_SESSION["last_activity"] = time();

if ($rememberMe) {
    $selector = bin2hex(random_bytes(12));
    $validator = bin2hex(random_bytes(32));
    $tokenHash = hash("sha256", $validator);
    $expiresAt = time() + (30 * 24 * 60 * 60);
    $expiresAtSql = date("Y-m-d H:i:s", $expiresAt);

    $stmt = $conn->prepare(
        "INSERT INTO remember_tokens (student_id, selector, token_hash, expires_at) VALUES (?, ?, ?, ?)"
    );
    $studentId = (int) $student["id"];
    $stmt->bind_param("isss", $studentId, $selector, $tokenHash, $expiresAtSql);
    $stmt->execute();
    $stmt->close();

    setcookie("studenthub_remember", $selector . ":" . $validator, [
        "expires" => $expiresAt,
        "path" => "/",
        "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
        "httponly" => true,
        "samesite" => "Lax"
    ]);
}

$conn->close();

if ($role === "admin") {
    header("Location: ../pages/admin.php");
} else {
    header("Location: ../pages/dashboard.php");
}
exit();
?>