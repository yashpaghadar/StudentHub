
<?php
$csvFile = __DIR__ . "/students.csv";
$students = [];
$error = "";

if (!file_exists($csvFile)) {
    $error = "No student data is available yet.";
} else {
    $file = fopen($csvFile, "r");

    if ($file === false) {
        $error = "Unable to open students.csv.";
    } else {
        $header = fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            if (count($row) >= 10) {
                $students[] = $row;
            }
        }

        fclose($file);
    }
}
?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub | Students</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background-color: #f8f9fa;
        }
        .student-card {
            border: 0;
            border-radius: 16px;
            overflow: hidden;
        }
        .table th {
            white-space: nowrap;
        }
        .table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">
            <i class="bi bi-mortarboard-fill me-2"></i>
            StudentHub
        </a>
        <a href="register.html" class="btn btn-warning">
            <i class="bi bi-person-plus me-1"></i>
            Add Student
        </a>
    </div>
</nav>

<div class="container-fluid py-5">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Student Records</h2>
                <p class="text-muted mb-0">All registered students</p>
            </div>
            <div class="mt-3 mt-md-0">
                <span class="badge bg-primary fs-6">
                    Total Students: <?= count($students) ?>
                </span>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-warning shadow-sm">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php elseif (empty($students)): ?>
            <div class="alert alert-info shadow-sm">
                <i class="bi bi-info-circle-fill me-2"></i>
                No student records found.
            </div>
        <?php else: ?>
            <div class="card student-card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-primary">
                                <tr>
                                    <th>ID</th>
                                    <th>Full Name</th>
                                    <th>Enrollment</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Course</th>
                                    <th>Year</th>
                                    <th>Gender</th>
                                    <th>Registered On</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td class="fw-semibold">
                                            <?= htmlspecialchars($student[0]) ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($student[1]) ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?= htmlspecialchars($student[2]) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($student[3]) ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($student[4]) ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($student[6]) ?>
                                        </td>
                                        <td>
                                            <?php
                                            $yearNames = [
                                                "1" => "First Year",
                                                "2" => "Second Year",
                                                "3" => "Third Year",
                                                "4" => "Fourth Year"
                                            ];
                                            echo htmlspecialchars($yearNames[$student[7]] ?? $student[7]);
                                            ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($student[8]) ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($student[9]) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<footer class="bg-primary text-white text-center py-3 mt-5">
    <p class="mb-0">&copy; 2026 StudentHub. All Rights Reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

