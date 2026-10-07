<?php
require "db_connect.php";
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendError("Invalid request method.", 405);
}

$input = json_decode(file_get_contents("php://input"), true);
$id = (int)($input["id"] ?? 0);

if ($id <= 0) {
    sendError("Invalid student ID.");
}

$stmt = $pdo->prepare("SELECT id FROM students WHERE id = :id LIMIT 1");
$stmt->execute([":id" => $id]);

if (!$stmt->fetch()) {
    sendError("Student not found.", 404);
}

try {
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = :id");
    $stmt->execute([":id" => $id]);

    if ($stmt->rowCount() === 0) {
        sendError("Student could not be deleted.", 500);
    }

    echo json_encode([
        "success" => true,
        "message" => "Student deleted permanently."
    ]);
} catch (PDOException $e) {
    sendError("Unable to delete student.", 500);
}

function sendError($message, $code = 400) {
    http_response_code($code);
    echo json_encode([
        "success" => false,
        "message" => $message
    ]);
    exit();
}
?>