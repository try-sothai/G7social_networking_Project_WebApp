<?php
// app/Controllers/Friends/search_users.php
session_start();
require_once '../../../config/db_config.php';
require_once '../../Models/Friends/FriendModel.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'users' => []]);
    exit();
}

$user_id = $_SESSION['user_id'];
$search_term = $_GET['q'] ?? '';

if (strlen($search_term) < 2) {
    echo json_encode(['success' => false, 'users' => []]);
    exit();
}

$friendModel = new FriendModel($conn);
$users = $friendModel->searchUsers($user_id, $search_term);

echo json_encode(['success' => true, 'users' => $users]);
?>