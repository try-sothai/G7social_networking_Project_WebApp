<?php
session_start();
require_once '../../../config/db_config.php';
require_once '../../Models/Events/EventModel.php';

if (!isset($_SESSION['user_id'])) {
    // Redirect to login if not authenticated
    header("Location: ../../Views/Login/login_gmail.php");
    exit();
}

$action = $_POST['action'] ?? '';

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $event_datetime_raw = trim($_POST['event_datetime'] ?? '');
    $event_datetime = null;
    $event_image_path = null;

    if (!empty($event_datetime_raw)) {
        $dt = DateTime::createFromFormat('Y-m-d\TH:i', $event_datetime_raw);
        if ($dt) {
            $event_datetime = $dt->format('Y-m-d H:i:s');
        } else {
            // fallback: simple conversion
            $event_datetime = str_replace('T', ' ', $event_datetime_raw);
        }
    }

    // Basic validation
    if (empty($title) || empty($location) || empty($event_datetime)) {
        // Handle error - maybe redirect back with an error message
        header("Location: ../../Views/Events/create_event.php?error=missing_fields");
        exit();
    }

    // Validate past dates
    if (strtotime($event_datetime) < time()) {
        header("Location: ../../Views/Events/create_event.php?error=past_date");
        exit();
    }

    // Handle file upload
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['event_image'];
        // absolute path for safe file writes
        $uploadDir = __DIR__ . '/../../../public/uploads/events/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = 'event_' . $user_id . '_' . time() . '.' . $extension;
        $targetFile = $uploadDir . $filename;
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        $fileType = mime_content_type($file['tmp_name']);

        if (in_array($fileType, $allowedTypes) && move_uploaded_file($file['tmp_name'], $targetFile)) {
            // Path to store in DB should be relative to a known root, let's use a path from public
            $event_image_path = 'uploads/events/' . $filename;
        } else {
            // Handle file upload error
            header("Location: ../../Views/Events/create_event.php?error=upload_failed");
            exit();
        }
    }
    
    // Create an instance of the model and save the event
    $eventModel = new EventModel($conn);
    $success = $eventModel->createEvent($user_id, $title, $description, $location, $event_datetime, $event_image_path);

    if ($success) {
        // Notify all other users about the new event
        $eventModel->notifyAllUsersNewEvent($user_id);

        // Redirect to the events page on success
        header("Location: ../../Views/Events/events.php?success=event_created");
        exit();
    } else {
        // Handle database insertion error
        header("Location: ../../Views/Events/create_event.php?error=db_error");
        exit();
    }
} elseif ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $event_id = (int)($_POST['event_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $event_datetime_raw = trim($_POST['event_datetime'] ?? '');
    $event_datetime = null;
    $event_image_path = null;

    if ($event_id <= 0) {
        header("Location: ../../Views/Events/events.php?error=invalid_event");
        exit();
    }

    if (!empty($event_datetime_raw)) {
        $dt = DateTime::createFromFormat('Y-m-d\TH:i', $event_datetime_raw);
        if ($dt) {
            $event_datetime = $dt->format('Y-m-d H:i:s');
        } else {
            $event_datetime = str_replace('T', ' ', $event_datetime_raw);
        }
    }

    if (empty($title) || empty($location) || empty($event_datetime)) {
        header("Location: ../../Views/Events/create_event.php?mode=edit&id=$event_id&error=missing_fields");
        exit();
    }

    // Validate past dates
    if (strtotime($event_datetime) < time()) {
        header("Location: ../../Views/Events/create_event.php?mode=edit&id=$event_id&error=past_date");
        exit();
    }

    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['event_image'];
        $uploadDir = __DIR__ . '/../../../public/uploads/events/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = 'event_' . $user_id . '_' . time() . '.' . $extension;
        $targetFile = $uploadDir . $filename;
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        $fileType = mime_content_type($file['tmp_name']);

        if (in_array($fileType, $allowedTypes) && move_uploaded_file($file['tmp_name'], $targetFile)) {
            $event_image_path = 'uploads/events/' . $filename;
        } else {
            header("Location: ../../Views/Events/create_event.php?mode=edit&id=$event_id&error=upload_failed");
            exit();
        }
    }
    
    $eventModel = new EventModel($conn);
    $success = $eventModel->updateEvent($event_id, $user_id, $title, $description, $location, $event_datetime, $event_image_path);

    if ($success) {
        header("Location: ../../Views/Events/events.php?success=event_updated");
        exit();
    } else {
        header("Location: ../../Views/Events/create_event.php?mode=edit&id=$event_id&error=db_error");
        exit();
    }
} elseif ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $event_id = (int)($_POST['event_id'] ?? 0);
    
    $eventModel = new EventModel($conn);
    $eventModel->deleteEvent($event_id, $user_id);
    
    header("Location: ../../Views/Events/events.php?success=event_deleted");
    exit();
} elseif ($action === 'toggle_interest' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $event_id = (int)($_POST['event_id'] ?? 0);
    
    if ($event_id > 0) {
        $eventModel = new EventModel($conn);
        $eventModel->toggleEventInterest($event_id, $user_id);
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid event ID']);
    }
    exit();
} else {
    // Redirect if action is not valid
    header("Location: ../../Views/Events/events.php");
    exit();
}
