<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

function apiResponse($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

try {
    require_once __DIR__ . '/../php/db_connect_mysqli.php';

    if (!isset($conn) || !($conn instanceof mysqli)) {
        apiResponse(false, 'Database connection error.', null, 500);
    }

    $method = $_SERVER['REQUEST_METHOD'];

    // GET: Retrieve, search and filter events.
    if ($method === 'GET') {
        $search = trim($_GET['search'] ?? '');
        $category = trim($_GET['category'] ?? '');
        $status = trim($_GET['status'] ?? '');

        $sql = "SELECT id,title,description,event_date,location,
                category,status,poster,created_at,updated_at
                FROM events WHERE 1=1";
        $types = '';
        $params = [];

        if ($search !== '') {
            $sql .= " AND (title LIKE ? OR description LIKE ? OR location LIKE ?)";
            $term = "%$search%";
            $types .= 'sss';
            array_push($params, $term, $term, $term);
        }

        if ($category !== '') {
            $sql .= " AND category = ?";
            $types .= 's';
            $params[] = $category;
        }

        if (in_array($status, ['Open', 'Closed'], true)) {
            $sql .= " AND status = ?";
            $types .= 's';
            $params[] = $status;
        }

        $sql .= " ORDER BY event_date ASC";
        $stmt = $conn->prepare($sql);

        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $events = [];

        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }

        apiResponse(true, 'Events retrieved successfully.', $events);
    }

    if ($method !== 'POST') {
        header('Allow: GET, POST');
        apiResponse(false, 'Method not allowed.', null, 405);
    }

    // Only administrators may modify events.
    $isAdmin = !empty($_SESSION['logged_in'])
        && !empty($_SESSION['user_id'])
        && ($_SESSION['role'] ?? '') === 'admin';

    if (!$isAdmin) {
        apiResponse(false, 'Administrator login required.', null, 403);
    }

    // CSRF protection.
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) ||
        empty($_SESSION['event_csrf']) ||
        !hash_equals($_SESSION['event_csrf'], $token)) {
        apiResponse(false, 'Invalid request token. Refresh the page.', null, 403);
    }

    $action = $_POST['action'] ?? '';

    // DELETE event.
    if ($action === 'delete') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            apiResponse(false, 'Invalid event ID.', null, 422);
        }

        $stmt = $conn->prepare("SELECT poster FROM events WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $event = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$event) {
            apiResponse(false, 'Event not found.', null, 404);
        }

        $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        $poster = $event['poster'] ?? '';
        $uploadDir = __DIR__ . '/../img/';;

        if (
            preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/i', $poster) &&
            is_file($uploadDir . $poster)
        ) {
            @unlink($uploadDir . $poster);
        }

        apiResponse(true, 'Event deleted successfully.');
    }

    // CREATE or UPDATE event.
    if (!in_array($action, ['create', 'update', 'save'], true)) {
        apiResponse(false, 'Invalid action.', null, 400);
    }

    $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);

    if ($id === false || $id < 0) {
        apiResponse(false, 'Invalid event ID.', null, 422);
    }

    $id = (int)$id;
    $isUpdate = $action === 'update' || ($action === 'save' && $id > 0);

    if ($action === 'update' && $id < 1) {
        apiResponse(false, 'Invalid event ID.', null, 422);
    }

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $date = $_POST['event_date'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $status = $_POST['status'] ?? 'Open';

    if ($title === '' || mb_strlen($title) > 150 ||
        mb_strlen($description) > 10000 ||
        $location === '' || mb_strlen($location) > 255 ||
        $category === '' || mb_strlen($category) > 100) {
        apiResponse(false, 'Please enter valid event details.', null, 422);
    }

    $dateObject = DateTime::createFromFormat('!Y-m-d', $date);

    if (!$dateObject || $dateObject->format('Y-m-d') !== $date) {
        apiResponse(false, 'Enter a valid event date.', null, 422);
    }

    if (!in_array($status, ['Open', 'Closed'], true)) {
        apiResponse(false, 'Invalid event status.', null, 422);
    }

    $uploadDir = __DIR__ . '/../img/';

    if (!is_dir($uploadDir) &&
        !mkdir($uploadDir, 0755, true) &&
        !is_dir($uploadDir)) {
        apiResponse(false, 'Unable to create poster directory.', null, 500);
    }

    $oldPoster = null;

    if ($isUpdate) {
        if ($id < 1) {
            apiResponse(false, 'Invalid event ID.', null, 422);
        }

        $stmt = $conn->prepare("SELECT poster FROM events WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $oldEvent = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$oldEvent) {
            apiResponse(false, 'Event not found.', null, 404);
        }

        $oldPoster = $oldEvent['poster'];
    }

    // Validate an optional uploaded poster.
    $newPoster = null;

    if (isset($_FILES['poster']) &&
        $_FILES['poster']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['poster'];

        if ($file['error'] !== UPLOAD_ERR_OK ||
            $file['size'] > 2 * 1024 * 1024 ||
            !is_uploaded_file($file['tmp_name'])) {
            apiResponse(false, 'Poster must be a valid upload up to 2 MB.', null, 422);
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        if (!isset($allowed[$mime]) ||
            @getimagesize($file['tmp_name']) === false) {
            apiResponse(false, 'Only valid JPG, PNG and WEBP images are allowed.', null, 422);
        }

        $newPoster = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];

        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $newPoster)) {
            apiResponse(false, 'Unable to save poster.', null, 500);
        }
    }

    $poster = $newPoster ?? $oldPoster;

    if ($isUpdate) {
        if ($newPoster !== null) {
            $stmt = $conn->prepare(
                "UPDATE events SET title=?,description=?,event_date=?,
                 location=?,category=?,status=?,poster=? WHERE id=?"
            );
            $stmt->bind_param(
                'sssssssi',
                $title, $description, $date, $location,
                $category, $status, $poster, $id
            );
        } else {
            $stmt = $conn->prepare(
                "UPDATE events SET title=?,description=?,event_date=?,
                 location=?,category=?,status=? WHERE id=?"
            );
            $stmt->bind_param(
                'ssssssi',
                $title, $description, $date, $location,
                $category, $status, $id
            );
        }

        $stmt->execute();
        $stmt->close();

        if ($newPoster !== null && $oldPoster &&
            basename($oldPoster) === $oldPoster &&
            is_file($uploadDir . $oldPoster)) {
            @unlink($uploadDir . $oldPoster);
        }

        apiResponse(true, 'Event updated successfully.', ['id' => $id]);
    }

    $stmt = $conn->prepare(
        "INSERT INTO events
         (title,description,event_date,location,category,status,poster)
         VALUES (?,?,?,?,?,?,?)"
    );
    $stmt->bind_param(
        'sssssss',
        $title, $description, $date, $location, $category, $status, $poster
    );
    $stmt->execute();
    $newId = $conn->insert_id;
    $stmt->close();

    apiResponse(true, 'Event added successfully.', ['id' => $newId], 201);

} catch (Throwable $e) {
    error_log('StudentHUB Events API: ' . $e->getMessage());
    apiResponse(false, 'A server error occurred. Check the PHP error log.', null, 500);
}