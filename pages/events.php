<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../php/db_connect_mysqli.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection error.');
}

// ==========================================
// AUTHENTICATION AND ACCESS CONTROL
// ==========================================

$isLoggedIn = !empty($_SESSION['logged_in']) &&
              !empty($_SESSION['user_id']);

$isAdmin = $isLoggedIn &&
           isset($_SESSION['role']) &&
           $_SESSION['role'] === 'admin';

if (empty($_SESSION['event_csrf'])) {
    $_SESSION['event_csrf'] = bin2hex(random_bytes(32));
}

$message = '';
$error = '';

$uploadDir = __DIR__ . '/../uploads/events/';
$existingPosterDir = __DIR__ . '/../img/';

if (!is_dir($uploadDir) &&
    !mkdir($uploadDir, 0755, true) &&
    !is_dir($uploadDir)) {
    die('Unable to create the event poster directory.');
}

// ==========================================
// HELPER FUNCTIONS
// ==========================================

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function removePoster($filename, $uploadDir) {
    if ($filename && basename($filename) === $filename) {
        $path = $uploadDir . $filename;

        if (is_file($path)) {
            unlink($path);
        }
    }
}

function savePoster($field, $uploadDir) {
    if (!isset($_FILES[$field]) ||
        $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Poster upload failed.');
    }

    if ($_FILES[$field]['size'] > 2 * 1024 * 1024) {
        throw new RuntimeException('Poster must be 2 MB or smaller.');
    }

    $tmp = $_FILES[$field]['tmp_name'];

    if (!is_uploaded_file($tmp)) {
        throw new RuntimeException('Invalid uploaded file.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($allowed[$mime]) || !@getimagesize($tmp)) {
        throw new RuntimeException(
            'Only valid JPG, PNG and WEBP images are allowed.'
        );
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];

    if (!move_uploaded_file($tmp, $uploadDir . $filename)) {
        throw new RuntimeException('Unable to save poster.');
    }

    return $filename;
}

// ==========================================
// CREATE, UPDATE AND DELETE EVENTS
// ==========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!$isAdmin) {
            throw new RuntimeException(
                'Access denied. Only administrators can manage events.'
            );
        }

        if (!hash_equals(
            $_SESSION['event_csrf'],
            $_POST['csrf_token'] ?? ''
        )) {
            throw new RuntimeException(
                'Invalid request token. Refresh the page.'
            );
        }

        $action = $_POST['action'] ?? '';

        // DELETE EVENT
        if ($action === 'delete') {
            $id = filter_var(
                $_POST['id'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$id || $id < 1) {
                throw new RuntimeException('Invalid event ID.');
            }

            $stmt = $conn->prepare(
                "SELECT poster FROM events WHERE id = ?"
            );
            $stmt->bind_param('i', $id);
            $stmt->execute();

            $event = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$event) {
                throw new RuntimeException('Event not found.');
            }

            $stmt = $conn->prepare(
                "DELETE FROM events WHERE id = ?"
            );
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();

            // Remove uploaded posters only.
            // Existing images in img/ are preserved.
            removePoster($event['poster'], $uploadDir);

            $message = 'Event deleted successfully.';
        }

        // ADD OR UPDATE EVENT
        elseif ($action === 'save') {
            $id = filter_var(
                $_POST['id'] ?? 0,
                FILTER_VALIDATE_INT
            );

            if ($id === false || $id < 0) {
                throw new RuntimeException('Invalid event ID.');
            }

            $id = (int)$id;
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $date = $_POST['event_date'] ?? '';
            $location = trim($_POST['location'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $status = $_POST['status'] ?? 'Open';

            if (
                $title === '' ||
                strlen($title) > 150 ||
                $location === '' ||
                strlen($location) > 255 ||
                $category === '' ||
                strlen($category) > 100
            ) {
                throw new RuntimeException(
                    'Enter a valid title, location and category.'
                );
            }

            if (strlen($description) > 10000) {
                throw new RuntimeException(
                    'Description is too long.'
                );
            }

            $dateObject = DateTime::createFromFormat('!Y-m-d', $date);

            if (
                !$dateObject ||
                $dateObject->format('Y-m-d') !== $date
            ) {
                throw new RuntimeException('Enter a valid event date.');
            }

            if (!in_array($status, ['Open', 'Closed'], true)) {
                throw new RuntimeException('Invalid event status.');
            }

            $oldPoster = null;

            if ($id > 0) {
                $stmt = $conn->prepare(
                    "SELECT poster FROM events WHERE id = ?"
                );
                $stmt->bind_param('i', $id);
                $stmt->execute();

                $oldEvent = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$oldEvent) {
                    throw new RuntimeException('Event not found.');
                }

                $oldPoster = $oldEvent['poster'];
            }

            $newPoster = savePoster('poster', $uploadDir);
            $poster = $newPoster ?? $oldPoster;

            if ($id > 0) {
                $stmt = $conn->prepare(
                    "UPDATE events
                     SET title = ?,
                         description = ?,
                         event_date = ?,
                         location = ?,
                         category = ?,
                         status = ?,
                         poster = ?
                     WHERE id = ?"
                );

                $stmt->bind_param(
                    'sssssssi',
                    $title,
                    $description,
                    $date,
                    $location,
                    $category,
                    $status,
                    $poster,
                    $id
                );

                $stmt->execute();
                $stmt->close();

                if ($newPoster !== null) {
                    removePoster($oldPoster, $uploadDir);
                }

                $message = 'Event updated successfully.';
            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO events
                     (title, description, event_date, location,
                      category, status, poster)
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                );

                $stmt->bind_param(
                    'sssssss',
                    $title,
                    $description,
                    $date,
                    $location,
                    $category,
                    $status,
                    $poster
                );

                $stmt->execute();
                $stmt->close();

                $message = 'Event added successfully.';
            }
        } else {
            throw new RuntimeException('Invalid action.');
        }

    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

// ==========================================
// SEARCH AND FILTER EVENTS
// ==========================================

$search = trim($_GET['search'] ?? '');
$categoryFilter = trim($_GET['category'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

$sql = "SELECT * FROM events WHERE 1=1";
$types = '';
$params = [];

if ($search !== '') {
    $sql .= " AND (title LIKE ? OR description LIKE ? OR location LIKE ?)";

    $term = "%$search%";
    $types .= 'sss';

    array_push($params, $term, $term, $term);
}

if ($categoryFilter !== '') {
    $sql .= " AND category = ?";
    $types .= 's';
    $params[] = $categoryFilter;
}

if (in_array($statusFilter, ['Open', 'Closed'], true)) {
    $sql .= " AND status = ?";
    $types .= 's';
    $params[] = $statusFilter;
}

$sql .= " ORDER BY event_date ASC";

$stmt = $conn->prepare($sql);

if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$events = $stmt->get_result();

$categories = $conn->query(
    "SELECT DISTINCT category FROM events ORDER BY category"
);

// ==========================================
// LOAD EVENT FOR ADMIN EDITING
// ==========================================

$editEvent = null;

if ($isAdmin && isset($_GET['edit'])) {
    $editId = filter_var($_GET['edit'], FILTER_VALIDATE_INT);

    if ($editId && $editId > 0) {
        $stmtEdit = $conn->prepare(
            "SELECT * FROM events WHERE id = ?"
        );

        $stmtEdit->bind_param('i', $editId);
        $stmtEdit->execute();

        $editEvent = $stmtEdit->get_result()->fetch_assoc();
        $stmtEdit->close();
    }
}

$formEvent = $editEvent ?? [
    'id' => '',
    'title' => '',
    'description' => '',
    'event_date' => '',
    'location' => '',
    'category' => '',
    'status' => 'Open'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Events | StudentHUB</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Existing StudentHUB stylesheet -->
    <link rel="stylesheet" href="../css/style.css">

    <style>
        html,
        body {
            background: #ffffff !important;
            color: #212529;
        }

        .events-page {
            background: #ffffff;
            min-height: 100vh;
            padding: 28px 0 40px;
        }

        .events-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .events-page-header {
            padding-bottom: 5px;
        }

        .events-title {
            color: var(--sh-primary-deep, #084298);
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .events-subtitle {
            color: #6c757d;
            margin-bottom: 0;
        }

        .event-card,
        .event-guidelines {
            border: none;
            border-radius: 12px;
            background: #ffffff;
            box-shadow: var(--sh-shadow, 0 2px 10px rgba(0,0,0,.07));
        }

        .event-card {
            overflow: hidden;
            transition: transform .2s, box-shadow .2s;
        }

        .event-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 22px rgba(13,110,253,.14);
        }

        .event-image {
            width: 100%;
            height: 210px;
            display: block;
            object-fit: cover;
        }

        .event-card .card-body {
            padding: 20px;
        }

        .event-badge {
            display: inline-block;
            background: #eaf2ff;
            color: var(--sh-primary, #0d6efd);
            border: 1px solid #d5e5ff;
            padding: 6px 11px;
            border-radius: 20px;
            font-size: 11px;
        }

        .event-title {
            color: var(--sh-primary-deep, #084298);
            font-size: 19px;
            font-weight: 700;
            line-height: 1.4;
            margin: 12px 0;
            overflow-wrap: anywhere;
        }

        .event-info {
            margin-bottom: 15px;
        }

        .event-info p {
            color: #6c757d;
            font-size: 13px;
            margin-bottom: 8px;
            overflow-wrap: anywhere;
        }

        .event-info strong {
            color: #344563;
        }

        .event-description {
            color: #6c757d;
            font-size: 13px;
            line-height: 1.6;
            overflow-wrap: anywhere;
        }

        .event-card .card-footer {
            padding: 0 20px 20px;
            border-top: none;
            background: #ffffff;
        }

        .event-btn {
            border: none;
            border-radius: 7px;
            padding: 9px 17px;
            font-size: 13px;
            font-weight: 600;
            background: var(--sh-primary, #0d6efd);
            color: #ffffff;
        }

        .event-filter-card {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            box-shadow: var(--sh-shadow, 0 2px 10px rgba(0,0,0,.05));
        }

        .event-grid > div {
            display: flex;
        }

        .event-grid .event-card {
            width: 100%;
        }

        @media (max-width: 576px) {
            .events-container {
                padding: 0 14px;
            }

            .events-title {
                font-size: 26px;
            }

            .event-image {
                height: 190px;
            }
        }
    </style>
</head>

<body>

<!-- Existing StudentHUB Navbar -->
<nav
      class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top"
    >
      <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="#">
          <img
            src="../img/logo.png"
            alt="StudentHub Logo"
            width="55"
            height="55"
            class="me-3 rounded"
          />

          <div>
            <h4 class="mb-0 fw-bold">StudentHub Portal</h4>
            <small class="text-light">Event Page</small>
          </div>
        </a>

        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navbarMenu"
        >
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMenu">
          <ul class="navbar-nav ms-auto align-items-lg-center">
            <li class="nav-item">
              <a class="nav-link" href="../index.html">Home</a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="about.html">About</a>
            </li>

            <li class="nav-item">
              <a class="nav-link active" href="events.html">Events</a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="dashboard.php"> Student Dashboard </a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="admin.html">Admin</a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="contact.html">Support</a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="faq.html">FAQ</a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="feedback.html">Feedback</a>
            </li>

            <li class="nav-item ms-lg-3">
              <a class="btn btn-warning me-2" href="login.html"> Login </a>
            </li>

            <li class="nav-item">
              <a class="btn btn-warning" href="register.html"> Register </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    
<main class="events-page">
    <div class="events-container">

        <!-- PAGE HEADING -->
        <div class="events-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h1 class="events-title">StudentHUB Events</h1>
                <p class="events-subtitle">
                    Discover upcoming events, workshops and activities.
                </p>
            </div>

            <?php if ($isAdmin): ?>
                <span class="badge bg-danger p-2">Admin Access</span>
            <?php elseif ($isLoggedIn): ?>
                <span class="badge bg-primary p-2">Student Access</span>
            <?php else: ?>
                <span class="badge bg-secondary p-2">Guest Access</span>
            <?php endif; ?>
        </div>

        <!-- FEEDBACK MESSAGES -->
        <?php if ($message !== ''): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= h($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= h($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- ADMIN ADD / UPDATE FORM -->
        <?php if ($isAdmin): ?>
            <div class="card mb-4">

                <div class="card-header">
                    <?= $editEvent ? 'Update Event' : 'Add New Event' ?>
                </div>

                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">

                        <input type="hidden" name="action" value="save">

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= h($_SESSION['event_csrf']) ?>"
                        >

                        <input
                            type="hidden"
                            name="id"
                            value="<?= h($formEvent['id']) ?>"
                        >

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Event Title</label>
                                <input
                                    class="form-control"
                                    name="title"
                                    maxlength="150"
                                    required
                                    value="<?= h($formEvent['title']) ?>"
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Event Date</label>
                                <input
                                    type="date"
                                    class="form-control"
                                    name="event_date"
                                    required
                                    value="<?= h($formEvent['event_date']) ?>"
                                >
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea
                                    class="form-control"
                                    name="description"
                                    rows="3"
                                    maxlength="10000"
                                ><?= h($formEvent['description']) ?></textarea>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Location</label>
                                <input
                                    class="form-control"
                                    name="location"
                                    maxlength="255"
                                    required
                                    value="<?= h($formEvent['location']) ?>"
                                >
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Category</label>
                                <input
                                    class="form-control"
                                    name="category"
                                    maxlength="100"
                                    required
                                    placeholder="Technical, Cultural, Workshop"
                                    value="<?= h($formEvent['category']) ?>"
                                >
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="Open"
                                        <?= $formEvent['status'] === 'Open' ? 'selected' : '' ?>>
                                        Open
                                    </option>
                                    <option value="Closed"
                                        <?= $formEvent['status'] === 'Closed' ? 'selected' : '' ?>>
                                        Closed
                                    </option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">
                                    Event Poster (JPG, PNG, WEBP; maximum 2 MB)
                                </label>

                                <input
                                    type="file"
                                    class="form-control"
                                    name="poster"
                                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                >

                                <small class="text-muted">
                                    Leave empty to keep the existing poster.
                                </small>
                            </div>

                            <div class="col-12 d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <?= $editEvent ? 'Update Event' : 'Add Event' ?>
                                </button>

                                <?php if ($editEvent): ?>
                                    <a href="events.php" class="btn btn-outline-secondary">
                                        Cancel
                                    </a>
                                <?php endif; ?>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- SEARCH AND FILTER: AVAILABLE TO EVERYONE -->
        <div class="card event-filter-card mb-4">
            <div class="card-body">

                <form method="GET" class="row g-3">

                    <div class="col-md-5">
                        <label class="form-label">Search Events</label>
                        <input
                            class="form-control"
                            name="search"
                            placeholder="Search title, description or location"
                            value="<?= h($search) ?>"
                        >
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category">
                            <option value="">All Categories</option>

                            <?php while ($cat = $categories->fetch_assoc()): ?>
                                <option
                                    value="<?= h($cat['category']) ?>"
                                    <?= $categoryFilter === $cat['category'] ? 'selected' : '' ?>
                                >
                                    <?= h($cat['category']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">All Statuses</option>
                            <option value="Open"
                                <?= $statusFilter === 'Open' ? 'selected' : '' ?>>
                                Open
                            </option>
                            <option value="Closed"
                                <?= $statusFilter === 'Closed' ? 'selected' : '' ?>>
                                Closed
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button class="btn btn-primary" type="submit">
                            Search
                        </button>
                        <a href="events.php" class="btn btn-outline-secondary">
                            Reset
                        </a>
                    </div>

                </form>
            </div>
        </div>

        <!-- EVENT LIST -->
        <div class="row g-4 event-grid">

            <?php if ($events->num_rows === 0): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        No events found. Try changing your search or filters.
                    </div>
                </div>
            <?php endif; ?>

            <?php while ($event = $events->fetch_assoc()): ?>

                <?php
                $posterFile = $event['poster'] ?? '';
                $posterUrl = '';

                if (
                    $posterFile !== '' &&
                    basename($posterFile) === $posterFile
                ) {
                    if (is_file($uploadDir . $posterFile)) {
                        $posterUrl = '../uploads/events/' .
                                     rawurlencode($posterFile);
                    } elseif (is_file($existingPosterDir . $posterFile)) {
                        $posterUrl = '../img/' .
                                     rawurlencode($posterFile);
                    }
                }
                ?>

                <div class="col-12 col-md-6 col-lg-4">
                    <article class="card event-card">

                        <?php if ($posterUrl !== ''): ?>
                            <img
                                src="<?= h($posterUrl) ?>"
                                class="event-image"
                                alt="<?= h($event['title']) ?>"
                                loading="lazy"
                            >
                        <?php endif; ?>

                        <div class="card-body">

                            <span class="event-badge">
                                <?= h($event['category']) ?>
                            </span>

                            <h2 class="event-title">
                                <?= h($event['title']) ?>
                            </h2>

                            <div class="event-info">
                                <p>
                                    <strong>Date:</strong>
                                    <?= h($event['event_date']) ?>
                                </p>

                                <p>
                                    <strong>Location:</strong>
                                    <?= h($event['location']) ?>
                                </p>

                                <p>
                                    <strong>Status:</strong>
                                    <span class="badge <?= $event['status'] === 'Open' ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= h($event['status']) ?>
                                    </span>
                                </p>
                            </div>

                            <p class="event-description mb-0">
                                <?= nl2br(h($event['description'])) ?>
                            </p>

                        </div>

                        <!-- ADMIN-ONLY ACTIONS -->
                        <?php if ($isAdmin): ?>
                            <div class="card-footer d-flex gap-2">

                                <a
                                    class="btn btn-warning btn-sm"
                                    href="events.php?edit=<?= (int)$event['id'] ?>"
                                >
                                    Edit
                                </a>

                                <form
                                    method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this event?');"
                                >
                                    <input
                                        type="hidden"
                                        name="action"
                                        value="delete"
                                    >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int)$event['id'] ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= h($_SESSION['event_csrf']) ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="btn btn-danger btn-sm"
                                    >
                                        Delete
                                    </button>
                                </form>

                            </div>
                        <?php endif; ?>

                    </article>
                </div>

            <?php endwhile; ?>

        </div>

        <footer class="text-center text-muted mt-5">
            StudentHUB &mdash; Event Management System
        </footer>

    </div>
</main>

<!-- Existing StudentHUB navbar loader -->
<script src="../js/navbar.js"></script>

<!-- Bootstrap dismissible alerts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
