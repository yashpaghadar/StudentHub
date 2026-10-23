<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!empty($_SESSION["logged_in"]) && !empty($_SESSION["user_id"])) {
    return;
}

$cookie = $_COOKIE["studenthub_remember"] ?? "";

if ($cookie === "" || substr_count($cookie, ":") !== 1) {
    return;
}

[$selector, $validator] = explode(":", $cookie, 2);

if (!ctype_xdigit($selector) || strlen($selector) !== 24 ||
    !ctype_xdigit($validator) || strlen($validator) !== 64) {
    return;
}

require_once __DIR__ . "/db_connect_mysqli.php";

$stmt = $conn->prepare(
    "SELECT rt.student_id, rt.token_hash, rt.expires_at, s.full_name, s.enrollment, s.role
     FROM remember_tokens rt
     JOIN students s ON s.id = rt.student_id
     WHERE rt.selector = ? LIMIT 1"
);
$stmt->bind_param("s", $selector);
$stmt->execute();
$result = $stmt->get_result();
$record = $result->fetch_assoc();
$stmt->close();

if (!$record || strtotime($record["expires_at"]) <= time() ||
    !hash_equals($record["token_hash"], hash("sha256", $validator))) {
    if ($record) {
        $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE selector = ?");
        $stmt->bind_param("s", $selector);
        $stmt->execute();
        $stmt->close();
    }

    setcookie("studenthub_remember", "", [
        "expires" => time() - 3600,
        "path" => "/",
        "httponly" => true,
        "samesite" => "Lax",
        "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off"
    ]);

    $conn->close();
    return;
}

session_regenerate_id(true);

$_SESSION["user_id"] = (int) $record["student_id"];
$_SESSION["enrollment"] = $record["enrollment"];
$_SESSION["full_name"] = $record["full_name"];
$_SESSION["role"] = strtolower(trim($record["role"]));
$_SESSION["logged_in"] = true;
$_SESSION["last_activity"] = time();

$conn->close();
?>