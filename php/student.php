<?php
require "db_connect.php";

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

    $courseStmt = $pdo->prepare(
        "SELECT course_name FROM courses ORDER BY course_id"
    );
    $courseStmt->execute();
    $courses = $courseStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Unable to load student data.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub | Student Info</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        body {
            background: #f5f7fb;
            color: #1f2937;
        }

        .student-page {
            max-width: 1400px;
            margin: 0 auto;
            padding: 55px 30px 70px;
        }

        .student-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .student-header .header-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 18px;
            border-radius: 20px;
            background: #e8f0ff;
            color: #1769ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
        }

        .student-header h1 {
            margin: 0;
            font-size: 42px;
            font-weight: 800;
            color: #171b22;
        }

        .student-header p {
            margin: 12px 0 0;
            color: #667085;
            font-size: 17px;
        }

        .student-count {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 18px;
            padding: 8px 16px;
            border-radius: 30px;
            background: #eaf2ff;
            color: #1769ff;
            font-weight: 600;
        }

        .students-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }

        .student-card {
            background: #ffffff;
            border: 1px solid #e7eaf0;
            border-radius: 18px;
            padding: 26px;
            box-shadow: 0 5px 20px rgba(16, 24, 40, 0.06);
            transition: 0.25s ease;
        }

        .student-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(16, 24, 40, 0.10);
        }

        .student-avatar {
            width: 70px;
            height: 70px;
            margin: 0 auto 16px;
            border-radius: 50%;
            background: #e8f0ff;
            color: #1769ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
        }

        .student-name {
            text-align: center;
            font-size: 23px;
            font-weight: 750;
            color: #171b22;
            margin-bottom: 5px;
        }

        .student-course {
            text-align: center;
            color: #1769ff;
            font-weight: 600;
            margin-bottom: 22px;
        }

        .student-divider {
            height: 1px;
            background: #e5e7eb;
            margin-bottom: 20px;
        }

        .student-info {
            display: flex;
            flex-direction: column;
            gap: 13px;
        }

        .student-info-row {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .student-info-row i {
            width: 20px;
            color: #1769ff;
            margin-top: 3px;
        }

        .student-info-row div {
            flex: 1;
        }

        .student-info-label {
            display: block;
            font-size: 12px;
            color: #8a94a6;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.4px;
            margin-bottom: 2px;
        }

        .student-info-value {
            color: #273142;
            font-weight: 500;
            word-break: break-word;
        }

        .student-actions {
            margin-top: 24px;
        }

        .student-actions .btn {
            width: 100%;
            border-radius: 10px;
            padding: 10px 15px;
            font-weight: 600;
        }

        .register-section {
            text-align: center;
            margin-top: 45px;
        }

        .register-section .btn {
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
        }

        @media (max-width: 1100px) {
            .students-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 700px) {
            .student-page {
                padding: 35px 16px 50px;
            }

            .student-header h1 {
                font-size: 32px;
            }

            .students-grid {
                grid-template-columns: 1fr;
            }

            .student-card {
                padding: 22px;
            }
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
    <main class="student-page">
        <div class="student-header">
            <div class="header-icon">
                <i class="bi bi-people-fill"></i>
            </div>
            <h1>Student Information</h1>
            <p>
                Student data retrieved directly from the MySQL database.
            </p>
            <div class="student-count">
                <i class="bi bi-people"></i>
                <?= count($students) ?> Registered Students
            </div>
        </div>
        <div id="pageMessage"></div>

        <?php if (empty($students)): ?>
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle me-2"></i>
                No student records found.
            </div>
        <?php else: ?>
            <div class="students-grid">
                <?php foreach ($students as $student): ?>
                    <div class="student-card">
                        <div class="student-avatar">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div class="student-name">
                            <?= htmlspecialchars($student["full_name"]) ?>
                        </div>
                        <div class="student-course">
                            <?= htmlspecialchars($student["course_name"]) ?>
                        </div>
                        <div class="student-divider"></div>
                        <div class="student-info">
                            <div class="student-info-row">
                                <i class="bi bi-person-vcard"></i>
                                <div>
                                    <span class="student-info-label">Enrollment</span>
                                    <span class="student-info-value">
                                        <?= htmlspecialchars($student["enrollment"]) ?>
                                    </span>
                                </div>
                            </div>

                            <div class="student-info-row">
                                <i class="bi bi-envelope"></i>
                                <div>
                                    <span class="student-info-label">Email</span>
                                    <span class="student-info-value">
                                        <?= htmlspecialchars($student["email"]) ?>
                                    </span>
                                </div>
                            </div>

                            <div class="student-info-row">
                                <i class="bi bi-phone"></i>
                                <div>
                                    <span class="student-info-label">Mobile</span>
                                    <span class="student-info-value">
                                        <?= htmlspecialchars($student["mobile"]) ?>
                                    </span>
                                </div>
                            </div>

                            <div class="student-info-row">
                                <i class="bi bi-mortarboard"></i>
                                <div>
                                    <span class="student-info-label">Course</span>
                                    <span class="student-info-value">
                                        <?= htmlspecialchars($student["course_name"]) ?>
                                    </span>
                                </div>
                            </div>

                            <div class="student-info-row">
                                <i class="bi bi-calendar3"></i>
                                <div>
                                    <span class="student-info-label">Year</span>
                                    <span class="student-info-value">
                                        Year <?= htmlspecialchars($student["year"]) ?>
                                    </span>
                                </div>
                            </div>

                            <div class="student-info-row">
                                <i class="bi bi-gender-ambiguous"></i>
                                <div>
                                    <span class="student-info-label">Gender</span>
                                    <span class="student-info-value">
                                        <?= htmlspecialchars($student["gender"]) ?>
                                    </span>
                                </div>
                            </div>

                            <div class="student-info-row">
                                <i class="bi bi-clock-history"></i>
                                <div>
                                    <span class="student-info-label">Registered</span>
                                    <span class="student-info-value">
                                        <?= htmlspecialchars($student["created_at"]) ?>
                                    </span>
                                </div>
                            </div>

                        </div>

                        <div class="student-actions">
                            <button type="button" class="btn btn-primary update-btn" data-id="<?= $student["id"] ?>"
                                data-name="<?= htmlspecialchars($student["full_name"], ENT_QUOTES) ?>"
                                data-enrollment="<?= htmlspecialchars($student["enrollment"], ENT_QUOTES) ?>"
                                data-email="<?= htmlspecialchars($student["email"], ENT_QUOTES) ?>"
                                data-mobile="<?= htmlspecialchars($student["mobile"], ENT_QUOTES) ?>"
                                data-course="<?= htmlspecialchars($student["course_name"], ENT_QUOTES) ?>"
                                data-year="<?= $student["year"] ?>"
                                data-gender="<?= htmlspecialchars($student["gender"], ENT_QUOTES) ?>">
                                <i class="bi bi-pencil-square me-2"></i>
                                Update Data
                            </button>
                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

        <div class="register-section">
            <a href="/StudentHUB/pages/register.html" class="btn btn-outline-primary">
                <i class="bi bi-person-plus me-2"></i>
                Register New Student
            </a>
        </div>

    </main>

    <!-- UPDATE MODAL -->

    <div class="modal fade" id="updateStudentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2"></i>
                        Update Student Data
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>

                <div class="modal-body">
                    <div id="updateMessage"></div>
                    <form id="updateStudentForm">
                        <input type="hidden" id="updateId">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="updateName" class="form-label fw-semibold">
                                    Full Name
                                </label>
                                <input type="text" id="updateName" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label for="updateEnrollment" class="form-label fw-semibold">
                                    Enrollment Number
                                </label>
                                <input type="text" id="updateEnrollment" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label for="updateEmail" class="form-label fw-semibold">
                                    Email
                                </label>
                                <input type="email" id="updateEmail" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label for="updateMobile" class="form-label fw-semibold">
                                    Mobile Number
                                </label>
                                <input type="text" id="updateMobile" class="form-control" maxlength="10" required>
                            </div>

                            <div class="col-md-6">
                                <label for="updateCourse" class="form-label fw-semibold">
                                    Course
                                </label>
                                <select id="updateCourse" class="form-select" required>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?= htmlspecialchars($course["course_name"]) ?>">
                                            <?= htmlspecialchars($course["course_name"]) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="updateYear" class="form-label fw-semibold">
                                    Year
                                </label>
                                <select id="updateYear" class="form-select" required>
                                    <option value="1">First Year</option>
                                    <option value="2">Second Year</option>
                                    <option value="3">Third Year</option>
                                    <option value="4">Fourth Year</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="updateGender" class="form-label fw-semibold">
                                    Gender
                                </label>

                                <select id="updateGender" class="form-select" required>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                        </div>

                    </form>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="button" id="removeStudentButton" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i>
                        Remove Data
                    </button>

                    <button type="button" id="saveUpdateButton" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>
                        Save Changes
                    </button>

                </div>

            </div>
        </div>
    </div>

    <footer class="text-center py-4 bg-dark text-white mt-5">
        <p class="mb-0">
            &copy; 2026 StudentHub. All Rights Reserved.
        </p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const modalElement = document.getElementById("updateStudentModal");
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            const form = document.getElementById("updateStudentForm");
            const saveButton = document.getElementById("saveUpdateButton");
            const removeButton = document.getElementById("removeStudentButton");
            const updateMessage = document.getElementById("updateMessage");

            document.querySelectorAll(".update-btn").forEach((button) => {
                button.addEventListener("click", function () {
                    document.getElementById("updateId").value = this.dataset.id;
                    document.getElementById("updateName").value = this.dataset.name;
                    document.getElementById("updateEnrollment").value = this.dataset.enrollment;
                    document.getElementById("updateEmail").value = this.dataset.email;
                    document.getElementById("updateMobile").value = this.dataset.mobile;
                    document.getElementById("updateCourse").value = this.dataset.course;
                    document.getElementById("updateYear").value = this.dataset.year;
                    document.getElementById("updateGender").value = this.dataset.gender;

                    updateMessage.innerHTML = "";
                    modal.show();
                });
            });

            removeButton.addEventListener("click", async function () {
                const studentId = document.getElementById("updateId").value;
                const studentName = document.getElementById("updateName").value;

                const confirmed = confirm(
                    `Are you sure you want to permanently remove ${studentName}?`
                );

                if (!confirmed) {
                    return;
                }

                removeButton.disabled = true;
                saveButton.disabled = true;

                removeButton.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2"></span>Removing...';

                updateMessage.innerHTML = "";

                try {
                    const response = await fetch("delete_student.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            id: studentId
                        })
                    });

                    const result = await response.json();

                    if (!response.ok || !result.success) {
                        throw new Error(
                            result.message || "Unable to remove student."
                        );
                    }

                    updateMessage.innerHTML = `
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>
                Student data removed successfully.
            </div>
        `;

                    setTimeout(() => {
                        modal.hide();
                        window.location.reload();
                    }, 1000);

                } catch (error) {
                    console.error("Delete error:", error);

                    updateMessage.innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                ${error.message}
            </div>
        `;
                }

                removeButton.disabled = false;
                saveButton.disabled = false;

                removeButton.innerHTML =
                    '<i class="bi bi-trash me-2"></i>Remove Data';
            });

            saveButton.addEventListener("click", async function () {
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                const requestData = {
                    id: document.getElementById("updateId").value,
                    field: "complete",
                    full_name: document.getElementById("updateName").value.trim(),
                    enrollment: document.getElementById("updateEnrollment").value.trim(),
                    email: document.getElementById("updateEmail").value.trim(),
                    mobile: document.getElementById("updateMobile").value.trim(),
                    course: document.getElementById("updateCourse").value,
                    year: document.getElementById("updateYear").value,
                    gender: document.getElementById("updateGender").value
                };

                saveButton.disabled = true;
                saveButton.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';

                updateMessage.innerHTML = "";

                try {
                    const response = await fetch("update_student.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify(requestData)
                    });

                    const result = await response.json();

                    if (!response.ok || !result.success) {
                        throw new Error(
                            result.message || "Unable to update student."
                        );
                    }

                    updateMessage.innerHTML = `
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    ${result.message || "Student updated successfully."}
                </div>
            `;

                    setTimeout(() => {
                        modal.hide();
                        window.location.reload();
                    }, 1000);

                } catch (error) {
                    console.error("Update error:", error);

                    updateMessage.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    ${error.message}
                </div>
            `;
                }

                saveButton.disabled = false;
                saveButton.innerHTML =
                    '<i class="bi bi-check-circle me-2"></i>Save Changes';
            });
        });
    </script>


</body>

</html>