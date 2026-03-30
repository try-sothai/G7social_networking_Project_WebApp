<?php
session_start();
require_once '../../../config/db_config.php';
require_once '../../Models/Events/EventModel.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'app/Views/Login/login_gmail.php');
    exit();
}

$eventModel = new EventModel($conn);
$user_id = (int) $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

function normalizeEventDateTime(string $raw): ?string {
    $raw = trim($raw);
    if ($raw === '') {
        return null;
    }

    $dt = DateTime::createFromFormat('Y-m-d\TH:i', $raw);
    if ($dt instanceof DateTime) {
        return $dt->format('Y-m-d H:i:s');
    }

    $fallback = str_replace('T', ' ', $raw);
    return $fallback !== '' ? $fallback : null;
}

function uploadEventImage(array $file, int $userId): ?string {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $uploadDir = __DIR__ . '/../../../public/uploads/events/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
    $fileType = mime_content_type($file['tmp_name']);
    if (!in_array($fileType, $allowedTypes, true)) {
        return null;
    }

    $extension = strtolower((string) pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($extension === '') {
        $extension = 'jpg';
    }

    $filename = 'event_' . $userId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $targetFile = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
        return null;
    }

    return 'uploads/events/' . $filename;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'app/Views/Events/events.php');
    exit();
}

if ($action === 'create') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $event_datetime = normalizeEventDateTime((string) ($_POST['event_datetime'] ?? ''));

    if ($title === '' || $location === '' || $event_datetime === null) {
        header('Location: ' . BASE_URL . 'app/Views/Events/create_event.php?error=missing_fields');
        exit();
    }

    $event_image_path = null;
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
        $event_image_path = uploadEventImage($_FILES['event_image'], $user_id);
        if ($event_image_path === null) {
            header('Location: ' . BASE_URL . 'app/Views/Events/create_event.php?error=upload_failed');
            exit();
        }
    }

    $success = $eventModel->createEvent($user_id, $title, $description, $location, $event_datetime, $event_image_path);
    if ($success) {
        header('Location: ' . BASE_URL . 'app/Views/Events/events.php?success=event_created');
        exit();
    }

    header('Location: ' . BASE_URL . 'app/Views/Events/create_event.php?error=db_error');
    exit();
}

if ($action === 'update') {
    $event_id = (int) ($_POST['event_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $event_datetime = normalizeEventDateTime((string) ($_POST['event_datetime'] ?? ''));

    if ($event_id <= 0 || $title === '' || $location === '' || $event_datetime === null) {
        header('Location: ' . BASE_URL . 'app/Views/Events/create_event.php?error=missing_fields');
        exit();
    }

    $oldEvent = $eventModel->getEventByIdForUser($event_id, $user_id);
    if (!$oldEvent) {
        header('Location: ' . BASE_URL . 'app/Views/Events/events.php?error=not_found');
        exit();
    }

    $newImagePath = null;
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
        $newImagePath = uploadEventImage($_FILES['event_image'], $user_id);
        if ($newImagePath === null) {
            header('Location: ' . BASE_URL . 'app/Views/Events/create_event.php?mode=edit&id=' . $event_id . '&error=upload_failed');
            exit();
        }
    }

    $success = $eventModel->updateEventForUser($event_id, $user_id, $title, $description, $location, $event_datetime, $newImagePath);

    if ($success && $newImagePath !== null && !empty($oldEvent['ImagePath'])) {
        $oldAbs = __DIR__ . '/../../../public/' . ltrim((string) $oldEvent['ImagePath'], '/');
        if (is_file($oldAbs)) {
            @unlink($oldAbs);
        }
    }

    if ($success) {
        header('Location: ' . BASE_URL . 'app/Views/Events/events.php?success=event_updated');
        exit();
    }

    header('Location: ' . BASE_URL . 'app/Views/Events/create_event.php?mode=edit&id=' . $event_id . '&error=db_error');
    exit();
}

if ($action === 'delete') {
    $event_id = (int) ($_POST['event_id'] ?? 0);
    if ($event_id <= 0) {
        header('Location: ' . BASE_URL . 'app/Views/Events/events.php?error=invalid_event');
        exit();
    }

    $event = $eventModel->getEventByIdForUser($event_id, $user_id);
    if (!$event) {
        header('Location: ' . BASE_URL . 'app/Views/Events/events.php?error=not_found');
        exit();
    }

    $deleted = $eventModel->deleteEventForUser($event_id, $user_id);
    if ($deleted) {
        if (!empty($event['ImagePath'])) {
            $abs = __DIR__ . '/../../../public/' . ltrim((string) $event['ImagePath'], '/');
            if (is_file($abs)) {
                @unlink($abs);
            }
        }
        header('Location: ' . BASE_URL . 'app/Views/Events/events.php?success=event_deleted');
        exit();
    }

    header('Location: ' . BASE_URL . 'app/Views/Events/events.php?error=db_error');
    exit();
}

header('Location: ' . BASE_URL . 'app/Views/Events/events.php?error=invalid_action');
exit();
?>
