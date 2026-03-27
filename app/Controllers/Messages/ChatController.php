<?php
session_start();
require_once __DIR__ . "/../../../config/db_config.php";
require_once __DIR__ . "/../../Models/Messages/MessageModel.php";

header('Content-Type: application/json');

$current_user_id = (int)($_SESSION['user_id'] ?? 0);

if (!$current_user_id) {
    echo json_encode(['error' => 'Unauthorized: Please login first.']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$messageModel = new MessageModel($conn);
$messageModel->touchPresence($current_user_id);

// Fetch friends with last message
if ($action === 'fetch_friends') {
    try {
        $friends = $messageModel->getFriendsWithLastMessage($current_user_id);
        echo json_encode(['success' => true, 'friends' => $friends]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Search friends
if ($action === 'search_friends') {
    try {
        $searchTerm = $_GET['q'] ?? '';
        if (strlen($searchTerm) < 2) {
            echo json_encode(['success' => true, 'friends' => []]);
            exit;
        }
        $friends = $messageModel->searchFriends($current_user_id, $searchTerm);
        echo json_encode(['success' => true, 'friends' => $friends]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Search all users (for adding)
if ($action === 'search_all_users') {
    try {
        $searchTerm = $_GET['q'] ?? '';
        if (strlen($searchTerm) < 2) {
            echo json_encode(['success' => true, 'users' => []]);
            exit;
        }
        $users = $messageModel->searchAllUsers($current_user_id, $searchTerm);
        echo json_encode(['success' => true, 'users' => $users]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Fetch chat history
if ($action === 'fetch') {
    try {
        $receiver_id = intval($_GET['receiver_id'] ?? 0);

        if ($receiver_id === $current_user_id) {
            echo json_encode([]);
            exit;
        }

        // Mark messages as read
        $messageModel->markAsRead($current_user_id, $receiver_id);

        $messages = $messageModel->getChatHistory($current_user_id, $receiver_id);
        echo json_encode($messages);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Send message (with file upload)
if ($action === 'send' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $messageBody = $_POST['message'] ?? '';
    $receiver_id = intval($_POST['receiver_id'] ?? 0);
    $type = $_POST['type'] ?? 'text';
    $fileUrl = null;

    // Handle file upload
    if (isset($_FILES['chat_file'])) {
        $uploadError = $_FILES['chat_file']['error'];
        if ($uploadError !== UPLOAD_ERR_OK) {
            $errMsg = 'Unable to upload file';
            if ($uploadError === UPLOAD_ERR_INI_SIZE || $uploadError === UPLOAD_ERR_FORM_SIZE) {
                $errMsg = 'File is too large. Increase upload_max_filesize/post_max_size in php.ini.';
            } elseif ($uploadError === UPLOAD_ERR_PARTIAL) {
                $errMsg = 'File was only partially uploaded. Please try again.';
            } elseif ($uploadError === UPLOAD_ERR_NO_FILE) {
                $errMsg = 'No file was uploaded.';
            }
            echo json_encode(['status' => 'error', 'message' => $errMsg]);
            exit;
        }

        $uploadDir = "../../../public/uploads/chat/";

        // Create directory if not exists
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $file = $_FILES['chat_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileName = time() . '_' . uniqid() . '.' . $ext;
        $uploadPath = $uploadDir . $fileName;

        // Determine file type
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $videoExtensions = ['mp4', 'mov', 'avi', 'mkv', 'webm', 'flv', '3gp', 'ogv', 'mts', 'ts', 'm2ts'];
        $audioExtensions = ['mp3', 'wav', 'ogg', 'aac', 'm4a', 'flac', 'wma'];

        // File size limits (in bytes)
        $maxImageSize = 50 * 1024 * 1024;     // 50 MB for images
        $maxVideoSize = 200 * 1024 * 1024;    // 200 MB for videos
        $maxAudioSize = 50 * 1024 * 1024;     // 50 MB for audio
        $maxFileSize = 100 * 1024 * 1024;     // 100 MB for other files

        if (in_array($ext, $imageExtensions)) {
            $type = 'image';
            $maxSize = $maxImageSize;
        } elseif (in_array($ext, $videoExtensions)) {
            $type = 'video';
            $maxSize = $maxVideoSize;
        } elseif (in_array($ext, $audioExtensions)) {
            $type = 'audio';
            $maxSize = $maxAudioSize;
        } else {
            $type = 'file';
            $maxSize = $maxFileSize;
        }

        // Check file size
        if ($file['size'] > $maxSize) {
            $maxSizeMB = round($maxSize / (1024 * 1024), 1);
            echo json_encode(['status' => 'error', 'message' => "File size exceeds {$maxSizeMB}MB limit"]);
            exit;
        }

        // Compress image if it's an image
        if ($type === 'image') {
            compressImage($file['tmp_name'], $uploadPath, 800);
        } else {
            move_uploaded_file($file['tmp_name'], $uploadPath);
        }

        $fileUrl = $fileName;
    }

    // Handle call recording (audio file)
    if ($type === 'call' && isset($_FILES['call_recording'])) {
        $uploadDir = "../../../public/uploads/calls/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = time() . '_call_' . uniqid() . '.webm';
        $uploadPath = $uploadDir . $fileName;
        move_uploaded_file($_FILES['call_recording']['tmp_name'], $uploadPath);
        $fileUrl = $fileName;
        $messageBody = "Voice message";
    }

    $messageId = $messageModel->sendMessage($current_user_id, $receiver_id, $messageBody, $type, $fileUrl);

    echo json_encode([
        'status' => $messageId ? 'success' : 'error',
        'message_id' => $messageId,
        'type' => $type,
        'file_url' => $fileUrl
    ]);
    exit;
}

// Start a call
if ($action === 'start_call') {
    $receiver_id = intval($_POST['receiver_id'] ?? 0);
    $callType = $_POST['call_type'] ?? 'audio'; // audio or video

    // Create a call notification message
    $messageBody = ($callType === 'video' ? 'Video' : 'Audio') . ' call started';
    $messageModel->sendMessage($current_user_id, $receiver_id, $messageBody, 'call', null);

    echo json_encode([
        'success' => true,
        'call_id' => uniqid(),
        'message' => 'Call initiated'
    ]);
    exit;
}

// Edit message
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $message_id = intval($_POST['message_id'] ?? 0);
        $new_text = trim($_POST['message'] ?? '');

        if (!$message_id || $new_text === '') {
            echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
            exit;
        }

        $messageData = $messageModel->getMessageById($message_id);
        if (!$messageData || $messageData['SenderID'] != $current_user_id) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized to edit this message']);
            exit;
        }

        $editWindow = 5 * 60; // seconds
        $sentTimestamp = strtotime($messageData['SentAt']);
        if (time() - $sentTimestamp > $editWindow) {
            echo json_encode(['success' => false, 'error' => 'Edit window expired']);
            exit;
        }

        $success = $messageModel->editMessage($message_id, $current_user_id, $new_text);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Message updated']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update message']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Delete message
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $message_id = intval($_POST['message_id'] ?? 0);

        if (!$message_id) {
            echo json_encode(['success' => false, 'error' => 'Invalid message ID']);
            exit;
        }

        $success = $messageModel->deleteMessage($message_id, $current_user_id);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Message deleted']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete message or unauthorized']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Image compression function
function compressImage($source, $destination, $quality) {
    $info = getimagesize($source);

    if ($info['mime'] == 'image/jpeg') {
        $image = imagecreatefromjpeg($source);
        imagejpeg($image, $destination, $quality);
    } elseif ($info['mime'] == 'image/png') {
        $image = imagecreatefrompng($source);
        imagepng($image, $destination, 9);
    } elseif ($info['mime'] == 'image/gif') {
        $image = imagecreatefromgif($source);
        imagegif($image, $destination);
    }

    imagedestroy($image);
    return true;
}

echo json_encode(['error' => 'Invalid action']);
?>
