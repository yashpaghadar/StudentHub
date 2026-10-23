<?php
session_start();

$cookie = $_COOKIE["studenthub_remember"] ?? "";
$selector = "";

if ($cookie !== "" && substr_count($cookie, ":") === 1) {
    [$selector, $validator] = explode(":", $cookie, 2);

    if (strlen($selector) === 24 && ctype_xdigit($selector)) {
        require_once __DIR__ . "/db_connect_mysqli.php";
        $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE selector = ?");
        $stmt->bind_param("s", $selector);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }
}

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), "", [
        "expires" => time() - 42000,
        "path" => $params["path"],
        "domain" => $params["domain"],
        "secure" => $params["secure"],
        "httponly" => $params["httponly"],
        "samesite" => $params["samesite"] ?? "Lax"
    ]);
}

setcookie("studenthub_remember", "", [
    "expires" => time() - 3600,
    "path" => "/",
    "httponly" => true,
    "samesite" => "Lax",
    "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off"
]);

session_destroy();

header("Location: ../pages/login.html?logout=success");
exit();
?>