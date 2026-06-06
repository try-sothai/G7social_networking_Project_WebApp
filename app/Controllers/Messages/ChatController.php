<?php
require_once __DIR__ . "/../../../config/db_config.php";
require_once __DIR__ . "/../../Models/Messages/MessageModel.php";

header('Content-Type: application/json');

// Image compression function
function compressImage(string $source, string $destination, int $quality) {
    if (!extension_loaded('gd')) return false;
    $info = @getimagesize($source);
    if (!$info) return false;

    $image = null;

    if ($info['mime'] == 'image/jpeg') {
        $image = @imagecreatefromjpeg($source);
        if ($image) imagejpeg($image, $destination, $quality);
    } elseif ($info['mime'] == 'image/png') {
        $image = @imagecreatefrompng($source);
        if ($image) {
            imagealphablending($image, false);
            imagesavealpha($image, true);
            imagepng($image, $destination, 9);
        }
    } elseif ($info['mime'] == 'image/gif') {
        $image = @imagecreatefromgif($source);
        if ($image) imagegif($image, $destination);
    } elseif ($info['mime'] == 'image/webp') {
        if (function_exists('imagecreatefromwebp')) {
            $image = @imagecreatefromwebp($source);
            if ($image) imagewebp($image, $destination, $quality);
        }
    }

    if ($image) {
        return true;
    }
    return false;
}

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

        $afterId = intval($_GET['after_id'] ?? 0);
        $messages = $messageModel->getChatHistory($current_user_id, $receiver_id, $afterId);
        echo json_encode($messages);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Send message (with file upload)
if ($action === 'send' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request token.']);
        exit;
    }

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

        $uploadDir = __DIR__ . "/../../../public/uploads/chat/";

        // Create directory if not exists
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $file = $_FILES['chat_file'];

        // Validate real MIME type using finfo (cannot be spoofed)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($file['tmp_name']);

        $allowedMimes = [
            // Images
            'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif',
            'image/webp' => 'webp',
            // Videos
            'video/mp4' => 'mp4', 'video/quicktime' => 'mov', 'video/x-msvideo' => 'avi',
            'video/x-matroska' => 'mkv', 'video/webm' => 'webm',
            // Audio
            'audio/mpeg' => 'mp3', 'audio/wav' => 'wav', 'audio/ogg' => 'ogg',
            'audio/aac' => 'aac', 'audio/mp4' => 'm4a', 'audio/webm' => 'webm',
            // Documents
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'text/plain' => 'txt',
        ];

        if (!array_key_exists($realMime, $allowedMimes)) {
            echo json_encode(['status' => 'error', 'message' => 'File type not allowed.']);
            exit;
        }

        // Use the validated extension from mime type (not from filename)
        $safeExt = $allowedMimes[$realMime];
        $fileName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $safeExt;
        $uploadPath = $uploadDir . $fileName;

        // Determine file type from validated mime
        $ext = $safeExt;
        $imageExtensions = ['jpg', 'png', 'gif', 'webp'];
        $videoExtensions = ['mp4', 'mov', 'avi', 'mkv', 'webm'];
        $audioExtensions = ['mp3', 'wav', 'ogg', 'aac', 'm4a'];

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
            $type = 'file'; // Fallback to file mapping for DB ENUM compatibility
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
            $compressed = compressImage($file['tmp_name'], $uploadPath, 80); // Changed from 800 to 80
            if (!$compressed) {
                // If image compression fails, fall back to saving the original image
                move_uploaded_file($file['tmp_name'], $uploadPath);
            }
        } else {
            move_uploaded_file($file['tmp_name'], $uploadPath);
        }

        $fileUrl = $fileName;
    }

    // Handle call recording (audio file)
    if ($type === 'call' && isset($_FILES['call_recording'])) {
        $uploadDir = __DIR__ . "/../../../public/uploads/calls/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fileName = time() . '_call_' . uniqid() . '.webm';
        $uploadPath = $uploadDir . $fileName;
        move_uploaded_file($_FILES['call_recording']['tmp_name'], $uploadPath);
        $fileUrl = $fileName;
        $messageBody = "Voice message";
        $type = 'file'; // Fallback to file mapping for DB ENUM compatibility
    }

    // Ensure type matches DB schema exactly before saving
    if (!in_array($type, ['text', 'image', 'video', 'file'])) {
        $type = 'text';
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

// Update Peer ID in the database
if ($action === 'update_peer_id' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $peer_id = $_POST['peer_id'] ?? '';
        if ($peer_id && $current_user_id) {
            $stmt = $conn->prepare("UPDATE users SET PeerID = ? WHERE UserID = ?");
            $stmt->execute([$peer_id, $current_user_id]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Missing peer_id or user_id']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Fetch Peer ID for calling
if ($action === 'get_peer_id') {
    try {
        $user_id = intval($_GET['user_id'] ?? 0);
        if ($user_id) {
            $stmt = $conn->prepare("SELECT PeerID FROM users WHERE UserID = ?");
            $stmt->execute([$user_id]);
            $peer_id = $stmt->fetchColumn();
            echo json_encode(['success' => true, 'peer_id' => $peer_id]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Missing user_id']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Start a call
if ($action === 'start_call') {
    $receiver_id = intval($_POST['receiver_id'] ?? 0);
    $callType = $_POST['call_type'] ?? 'audio'; // audio or video

    // Create a call notification message
    $messageBody = ($callType === 'video' ? 'Video' : 'Audio') . ' call started';
    $messageModel->sendMessage($current_user_id, $receiver_id, $messageBody, 'text', null);

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

        // 1. Fetch the message first to check if it has an attached file
        $msgData = $messageModel->getMessageById($message_id);

        // 2. Update database (soft delete)
        $success = $messageModel->deleteMessage($message_id, $current_user_id);

        if ($success) {
            // 3. If there is a file, delete it from the server
            if ($msgData && !empty($msgData['FileUrl'])) {
                $baseDir = preg_match('/^\d+_call_/', $msgData['FileUrl']) ? 'calls/' : 'chat/';
                $filePath = __DIR__ . "/../../../public/uploads/" . $baseDir . basename($msgData['FileUrl']);
                if (is_file($filePath)) {
                    @unlink($filePath);
                }
            }
            echo json_encode(['success' => true, 'message' => 'Message deleted for everyone']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete message or unauthorized']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['error' => 'Invalid action']);
?>
