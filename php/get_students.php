<?php

require "db_connect.php";

header("Content-Type: application/json; charset=UTF-8");

try {

    $sql = "SELECT
                students.id,
                students.full_name,
                students.enrollment,
                students.email,
                students.mobile,
                courses.course_name,
                students.year,
                students.gender,
                students.created_at
            FROM students
            INNER JOIN courses
                ON students.course_id = courses.course_id
            ORDER BY students.id DESC";

    $stmt = $pdo->prepare($sql);

    $stmt->execute();

    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "students" => $students
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Unable to load student data."
    ]);

}

?>