<?php
// app/Controllers/Friends/friend_actions.php
session_start();
require_once '../../../config/db_config.php';
require_once '../../Models/Friends/FriendModel.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];
$friendModel = new FriendModel($conn);

$action = $_POST['action'] ?? '';
$friend_id = $_POST['friend_id'] ?? 0;

if (!$friend_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid user']);
    exit();
}

$result = ['success' => false, 'message' => 'Invalid action'];

switch ($action) {
    case 'send_request':
        $result = $friendModel->sendFriendRequest($user_id, $friend_id);
        break;
        
    case 'accept_request':
        $result = $friendModel->acceptFriendRequest($user_id, $friend_id);
        break;
        
    case 'reject_request':
        $result = $friendModel->rejectFriendRequest($user_id, $friend_id);
        break;
        
    case 'remove_friend':
        $result = $friendModel->removeFriend($user_id, $friend_id);
        break;
        
    default:
        $result['message'] = 'Unknown action';
}

echo json_encode($result);
?>