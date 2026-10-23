<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/db_connect_mysqli.php";

try {
    $sql = "SELECT course_id, course_name FROM courses ORDER BY course_id";
    $result = $conn->query($sql);
    $courses = [];

    while ($row = $result->fetch_assoc()) {
        $courses[] = [
            "course_id" => (int)$row["course_id"],
            "course_name" => $row["course_name"]
        ];
    }

    echo json_encode([
        "success" => true,
        "courses" => $courses
    ]);
} catch (Throwable $e) {
    error_log("Course loading error: " . $e->getMessage());
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Unable to load courses."
    ]);
}
?>