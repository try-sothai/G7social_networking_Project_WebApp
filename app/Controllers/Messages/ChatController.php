<?php
session_start();
error_reporting(E_ALL); 
ini_set('display_errors', 1);
require_once __DIR__ . "/../../../config/db_config.php";
require_once __DIR__ . "/../../Models/Messages/MessageModel.php"; 

header('Content-Type: application/json');

$current_user_id = $_SESSION['user_id'] ?? 6; 

if (!$current_user_id) {
    echo json_encode(['error' => 'Unauthorized: Please login first.']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$messageModel = new MessageModel($conn); 

if ($action === 'fetch_users') {
    try {

        $stmt = $conn->prepare("SELECT UserID, Username, ProfilePic FROM users WHERE UserID != :uid");
        $stmt->execute(['uid' => $current_user_id]);
        
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($users);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'fetch') {
    try {
        $receiver_id = intval($_GET['receiver_id'] ?? 0);

        if ($receiver_id === $current_user_id) {
            echo json_encode([]);
            exit;
        }

        $messages = $messageModel->getChatHistory($current_user_id, $receiver_id); 
        echo json_encode($messages);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'send' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $messageBody = trim($_POST['message'] ?? '');
        $receiver_id = intval($_POST['receiver_id'] ?? 0);

        if (!empty($messageBody) && $receiver_id > 0 && $receiver_id !== $current_user_id) {
            $status = $messageModel->sendMessage($current_user_id, $receiver_id, $messageBody); 
            echo json_encode(['status' => $status ? 'success' : 'error']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid message or receiver.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
