<?php
session_start();
error_reporting(E_ALL); 
ini_set('display_errors', 1);
require_once __DIR__ . "/../../../config/db_config.php";
require_once __DIR__ . "/../../Models/Messages/MessageModel.php"; 

header('Content-Type: application/json');

/** * ១. ចាប់យក User ID ពី Session ឱ្យបានត្រឹមត្រូវ
 * បើគ្មាន Session ទេ វានឹងមិនអនុញ្ញាតឱ្យដើរទៅមុខឡើយ ដើម្បីការពារសុវត្ថិភាព
 */
$current_user_id = $_SESSION['user_id'] ?? 6; 

if (!$current_user_id) {
    echo json_encode(['error' => 'Unauthorized: Please login first.']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$messageModel = new MessageModel($conn); 

// --- ផ្នែកទាញយកបញ្ជី User (ដកខ្លួនឯងចេញ) ---
if ($action === 'fetch_users') {
    try {
        /**
         * ប្រើ WHERE UserID != :uid ដើម្បីកុំឱ្យបង្ហាញឈ្មោះខ្លួនឯងក្នុងបញ្ជី
         * នេះនឹងដោះស្រាយបញ្ហាដែលបងឃើញឈ្មោះខ្លួនឯងក្នុង Sidebar
         */
        $stmt = $conn->prepare("SELECT UserID, Username, ProfilePic FROM users WHERE UserID != :uid");
        $stmt->execute(['uid' => $current_user_id]);
        
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($users);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// --- ផ្នែកទាញយកប្រវត្តិឆាត ---
if ($action === 'fetch') {
    try {
        $receiver_id = intval($_GET['receiver_id'] ?? 0);
        
        // បើ ID ដូចគ្នា មិនបាច់ទាញទិន្នន័យទេ (ការពារការឆាតរកខ្លួនឯង)
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

// --- ផ្នែកផ្ញើសារ ---
if ($action === 'send' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $messageBody = trim($_POST['message'] ?? '');
        $receiver_id = intval($_POST['receiver_id'] ?? 0);

        /**
         * ២. លក្ខខណ្ឌការពារការផ្ញើសាររកខ្លួនឯង (Self-messaging protection)
         * ពិនិត្យថា តួសារមិនទទេ និង Receiver មិនមែនជា Sender ខ្លួនឯង
         */
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