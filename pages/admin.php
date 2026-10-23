<?php
session_start();
require_once __DIR__ . "/../php/remember_me.php";

if (empty($_SESSION["logged_in"]) || empty($_SESSION["user_id"])) {
    header("Location: login.html?error=unauthorized");
    exit();
}
if (strtolower(trim($_SESSION["role"] ?? "")) !== "admin") {
    header("Location: dashboard.php?error=forbidden");
    exit();
}
if (!isset($conn)) require_once __DIR__ . "/../php/db_connect_mysqli.php";

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
function redirectAdmin($status) {
    header("Location: admin.php?status=" . urlencode($status));
    exit();
}

$error = "";
$statusMessages = [
    "added" => "Student added successfully.",
    "updated" => "Student updated successfully.",
    "deleted" => "Student deleted successfully."
];
$success = $statusMessages[$_GET["status"] ?? ""] ?? "";

$courses = $conn->query(
    "SELECT course_id, course_name FROM courses ORDER BY course_name"
)->fetch_all(MYSQLI_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"] ?? "")) {
        exit("Invalid request. Please refresh the page.");
    }

    $action = $_POST["action"] ?? "";
    $id = filter_var($_POST["id"] ?? 0, FILTER_VALIDATE_INT);

    try {
        if ($action === "delete" && $id > 0) {
            $stmt = $conn->prepare(
                "DELETE FROM students WHERE id=? AND role='student'"
            );
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $deleted = $stmt->affected_rows;
            $stmt->close();

            if ($deleted) redirectAdmin("deleted");
            $error = "Student not found or could not be deleted.";
        } elseif (in_array($action, ["add", "update"], true)) {
            $name = trim($_POST["full_name"] ?? "");
            $enrollment = trim($_POST["enrollment"] ?? "");
            $email = trim($_POST["email"] ?? "");
            $mobile = trim($_POST["mobile"] ?? "");
            $password = $_POST["password"] ?? "";
            $courseId = filter_var($_POST["course_id"] ?? 0, FILTER_VALIDATE_INT);
            $year = filter_var($_POST["year"] ?? 0, FILTER_VALIDATE_INT);
            $gender = $_POST["gender"] ?? "";

            $validCourse = false;
            foreach ($courses as $course) {
                if ((int)$course["course_id"] === (int)$courseId) {
                    $validCourse = true;
                    break;
                }
            }

            if ($name === "" || mb_strlen($name) > 100) {
                $error = "Enter a valid student name.";
            } elseif ($enrollment === "" || strlen($enrollment) > 50) {
                $error = "Enter a valid enrollment number.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Enter a valid email address.";
            } elseif ($mobile === "" || strlen($mobile) > 15) {
                $error = "Enter a valid mobile number.";
            } elseif (!$validCourse) {
                $error = "Select a valid course.";
            } elseif ($year === false || $year < 1 || $year > 10) {
                $error = "Enter a valid academic year.";
            } elseif (!in_array($gender, ["Male", "Female", "Other"], true)) {
                $error = "Select a valid gender.";
            } elseif ($action === "add" && strlen($password) < 8) {
                $error = "New student password must contain at least 8 characters.";
            } elseif ($action === "update" && (!$id || $id < 1)) {
                $error = "Invalid student ID.";
            } else {
                if ($action === "add") {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare(
                        "INSERT INTO students
                        (full_name,enrollment,email,password,mobile,course_id,year,gender,role)
                        VALUES (?,?,?,?,?,?,?,?,'student')"
                    );
                    $stmt->bind_param(
                        "sssssiis",
                        $name, $enrollment, $email, $hash, $mobile,
                        $courseId, $year, $gender
                    );
                } else {
                    $stmt = $conn->prepare(
                        "UPDATE students
                        SET full_name=?, enrollment=?, email=?, mobile=?,
                            course_id=?, year=?, gender=?
                        WHERE id=? AND role='student'"
                    );
                    $stmt->bind_param(
                        "ssss iisi",
                        $name, $enrollment, $email, $mobile,
                        $courseId, $year, $gender, $id
                    );
                }

                $stmt->execute();
                $stmt->close();
                redirectAdmin($action === "add" ? "added" : "updated");
            }
        }
    } catch (Throwable $e) {
        error_log($e->getMessage());
        $error = "Operation failed. Check for duplicate enrollment/email or database errors.";
    }
}

$edit = null;
$editId = filter_var($_GET["edit"] ?? 0, FILTER_VALIDATE_INT);
if ($editId && $editId > 0) {
    $stmt = $conn->prepare(
        "SELECT id,full_name,enrollment,email,mobile,course_id,year,gender
         FROM students WHERE id=? AND role='student'"
    );
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$q = trim($_GET["q"] ?? "");
$like = "%$q%";
$courseFilter = filter_var($_GET["course_id"] ?? 0, FILTER_VALIDATE_INT);
$courseFilter = $courseFilter > 0 ? $courseFilter : 0;

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total FROM students
     WHERE role='student'
     AND (full_name LIKE ? OR email LIKE ? OR enrollment LIKE ?)
     AND (course_id=? OR ?=0)"
);
$stmt->bind_param("sssii", $like, $like, $like, $courseFilter, $courseFilter);
$stmt->execute();
$totalStudents = (int)$stmt->get_result()->fetch_assoc()["total"];
$stmt->close();

$perPage = 10;
$totalPages = max(1, (int)ceil($totalStudents / $perPage));
$page = max(1, min((int)($_GET["page"] ?? 1), $totalPages));
$offset = ($page - 1) * $perPage;

$stmt = $conn->prepare(
    "SELECT s.id,s.full_name,s.enrollment,s.email,s.mobile,
            s.course_id,s.year,s.gender,c.course_name
     FROM students s
     LEFT JOIN courses c ON s.course_id=c.course_id
     WHERE s.role='student'
     AND (s.full_name LIKE ? OR s.email LIKE ? OR s.enrollment LIKE ?)
     AND (s.course_id=? OR ?=0)
     ORDER BY s.id DESC LIMIT ? OFFSET ?"
);
$stmt->bind_param(
    "ssssiis",
    $like, $like, $like, $courseFilter, $courseFilter, $perPage, $offset
);
$stmt->execute();
$students = $stmt->get_result();
?>


<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>StudentHub - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            font-family: Poppins, Arial, sans-serif;
            background: #f5f7fc
        }

        .admin-main {
            padding: 30px 0
        }

        .panel {
            background: white;
            border-radius: 12px;
            padding: 22px;
            margin-bottom: 24px;
            box-shadow: 0 3px 15px #0000000b
        }

        .panel-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 18px
        }

        .stat {
            background: #eef2ff;
            border-radius: 10px;
            padding: 20px;
            display: flex;
            gap: 15px;
            align-items: center
        }

        .stat i {
            font-size: 30px;
            color: #3157c8
        }

        .stat h3 {
            margin: 0;
            font-weight: 700
        }

        .form-control,
        .form-select {
            margin-bottom: 12px
        }

        .table th {
            background: #eaf0ff;
            white-space: nowrap
        }

        .table td {
            vertical-align: middle
        }

        .notice {
            padding: 12px;
            border-radius: 7px;
            margin-bottom: 15px
        }

        .success {
            background: #d8f5df;
            color: #14532d
        }

        .error {
            background: #ffe1e1;
            color: #842029
        }

        .section-link {
            display: block;
            text-decoration: none;
            padding: 12px;
            border: 1px solid #eee;
            border-radius: 8px;
            margin-bottom: 8px
        }

        .section-link:hover {
            background: #f2f5ff
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="admin.php">
                <img src="../img/logo.png" alt="StudentHub" width="48" height="48" class="me-3 rounded">
                <div><strong>StudentHub Portal</strong><br><small>Admin Dashboard</small></div>
            </a>
            <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarMenu"><span
                    class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarMenu">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="../index.html">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="admin.php">Manage Students</a></li>
                    <li class="nav-item"><a class="nav-link" href="events.php">Manage Events</a></li>
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">St_Dashboard</a></li>
                </ul>
                <div class="dropdown ms-lg-3">
                    <a href="#" class="text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                        Welcome,
                        <?= h($_SESSION["full_name"] ?? "Admin") ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item text-danger" href="../php/logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <main class="admin-main">
        <div class="container">
            <div class="mb-4">
                <h1 class="fw-bold">Admin Dashboard</h1>
                <p class="text-muted">Manage student records, search and filter student data.</p>
            </div>

            <?php if ($success): ?>
            <div class="notice success">
                <?= h($success) ?>
            </div>
            <?php endif; ?>
            <?php if ($error): ?>
            <div class="notice error">
                <?= h($error) ?>
            </div>
            <?php endif; ?>

            <section class="panel">
                <h2 class="panel-title"><i class="bi bi-bar-chart-fill me-2"></i>Overview</h2>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="stat"><i class="bi bi-people-fill"></i>
                            <div>
                                <p class="mb-1">Total Students</p>
                                <h3>
                                    <?= $totalStudents ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat"><i class="bi bi-calendar-event-fill"></i>
                            <div>
                                <p class="mb-1">Total Events</p>
                                <h3>25</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat"><i class="bi bi-person-plus-fill"></i>
                            <div>
                                <p class="mb-1">New Registrations</p>
                                <h3>12</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </section>


            <section class="panel">
                <h2 class="panel-title"><i class="bi bi-person-plus-fill me-2"></i><?= $edit ? "Edit Student" : "Add New Student" ?></h2>
                <form method="post" action="admin.php<?= $edit ? '?edit=' . (int)$edit["id"] : '' ?>">
                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION["csrf_token"]) ?>">
                    <input type="hidden" name="action" value="<?= $edit ? 'update' : 'add' ?>">
                    <?php if ($edit): ?>
                        <input type="hidden" name="id" value="<?= (int)$edit["id"] ?>">
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Full Name</label>
                            <input class="form-control" name="full_name" maxlength="100" required value="<?= h($edit["full_name"] ?? "") ?>">
                        </div>
                        <div class="col-md-4"><label class="form-label">Enrollment Number</label><input
                                class="form-control" name="enrollment" maxlength="50" required value="<?= h($edit["
                                enrollment"] ?? "" ) ?>"></div>
                        <div class="col-md-4"><label class="form-label">Email</label><input type="email"
                                class="form-control" name="email" required value="<?= h($edit[" email"] ?? "" ) ?>">
                        </div>
                        <div class="col-md-4"><label class="form-label">Mobile</label><input class="form-control"
                                name="mobile" maxlength="15" required value="<?= h($edit[" mobile"] ?? "" ) ?>"></div>
                        <div class="col-md-4">
                            <label class="form-label">Courses</label>
                            <select class="form-select" name="course_id">
                                <option value="0">All Courses</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?= (int)$c["course_id"] ?>" <?= $courseFilter === (int)$c["course_id"] ? "selected" : "" ?>>
                                        <?= h($c["course_name"]) ?>
                                    </option>
                                <?php endforeach; ?>
                                </select>
                        </div>
                        <div class="col-md-4"><label class="form-label">Year</label><input type="number"
                                class="form-control" name="year" min="1" max="10" required value="<?= h($edit[" year"]
                                ?? "" ) ?>"></div>
                        <div class="col-md-4"><label class="form-label">Gender</label><select class="form-select"
                                name="gender" required>
                                <option value="">Select Gender</option>
                                <?php foreach (["Male","Female","Other"] as $g): ?>
                                <option value="<?= $g ?>" <?=($edit["gender"] ?? "" )===$g ? "selected" : "" ?>>
                                    <?= $g ?>
                                </option>
                                <?php endforeach; ?>
                            </select></div>
                        <?php if (!$edit): ?>
                        <div class="col-md-4"><label class="form-label">Initial Password</label><input type="password"
                                class="form-control" name="password" minlength="8" required autocomplete="new-password">
                        </div>
                        <?php endif; ?>
                    </div>
                    <button class="btn btn-primary"><i class="bi bi-save me-1"></i>
                        <?= $edit ? "Update Student" : "Add Student" ?>
                    </button>
                    <?php if ($edit): ?><a href="admin.php" class="btn btn-outline-secondary">Cancel</a>
                    <?php endif; ?>
                </form>
            </section>

            
            <section class="panel">
                <h2 class="panel-title"><i class="bi bi-people-fill me-2"></i>Student Management</h2>
                <form method="get" action="admin.php" class="row align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Search name, email or enrollment</label>
                        <input class="form-control" name="q" value="<?= h($q) ?>" placeholder="Enter search term">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filter by Course</label>
                        <select class="form-select" name="course_id">
                            <option value="0">All Courses</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= (int)$c["course_id"] ?>" <?= $courseFilter === (int)$c["course_id"] ? "selected" : "" ?>>
                                    <?= h($c["course_name"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3"><button class="btn btn-primary">Search / Filter</button> <a
                            href="admin.php" class="btn btn-outline-secondary">Reset</a></div>
                </form>
                <p class="text-muted">Matching students:
                    <?= $totalStudents ?>
                </p>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Enrollment</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Course</th>
                                <th>Year</th>
                                <th>Gender</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($students->num_rows): while ($s=$students->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?= (int)$s["id"] ?>
                                </td>
                                <td>
                                    <?= h($s["full_name"]) ?>
                                </td>
                                <td>
                                    <?= h($s["enrollment"]) ?>
                                </td>
                                <td>
                                    <?= h($s["email"]) ?>
                                </td>
                                <td>
                                    <?= h($s["mobile"]) ?>
                                </td>
                                <td>
                                    <?= h($s["course_name"] ?? "N/A") ?>
                                </td>
                                                        
                                <td><?= (int)$s["year"] ?></td>
                                <td><?= h($s["gender"]) ?></td>
                                <td class="text-nowrap">
                                    <a href="?edit=<?= (int)$s["id"] ?>" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <form method="post" action="admin.php" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this student?')">
                                        <input type="hidden" name="csrf_token" value="<?= h($_SESSION["csrf_token"]) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int)$s["id"] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i> Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr>
                                <td colspan="9" class="text-center">No student records found.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span>Page
                        <?= $page ?> of
                        <?= $totalPages ?>
                    </span>
                    <div>
                        <?php if ($page>1): ?><a class="btn btn-outline-primary btn-sm"
                            href="?page=<?= $page-1 ?>&q=<?= urlencode($q) ?>&course_id=<?= $courseFilter ?>">Previous</a>
                        <?php endif; ?>
                        <?php if ($page<$totalPages): ?><a class="btn btn-outline-primary btn-sm"
                            href="?page=<?= $page+1 ?>&q=<?= urlencode($q) ?>&course_id=<?= $courseFilter ?>">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="panel">
                <h2 class="panel-title"><i class="bi bi-calendar-event-fill me-2"></i>Event Management</h2>
                <div class="row g-3">
                    <div class="col-md-6"><a href="events.html" class="section-link"><i
                                class="bi bi-calendar-plus me-2"></i>Create or Manage Events <i
                                class="bi bi-chevron-right float-end"></i></a></div>
                    <div class="col-md-6"><a href="feedback.html" class="section-link"><i
                                class="bi bi-chat-square-text me-2"></i>View Student Feedback <i
                                class="bi bi-chevron-right float-end"></i></a></div>
                </div>
            </section>
        </div>
    </main>

    <footer class="text-center py-4 bg-white">
        <p class="mb-0">&copy; 2026 StudentHub. All Rights Reserved.</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php $stmt->close(); ?>