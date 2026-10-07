<?php
require "db_connect.php";
header("Content-Type: application/json; charset=UTF-8");

try {
    $stmt = $pdo->prepare(
        "SELECT course_id, course_name
         FROM courses
         ORDER BY course_id"
    );
    $stmt->execute();

    echo json_encode([
        "success" => true,
        "courses" => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Unable to load courses."
    ]);
}
?>