<?php
require_once __DIR__ . '/../../../config/db_config.php';
require_once __DIR__ . '/../../Models/Friends/FriendModel.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($searchTerm) < 2) {
    echo json_encode(['success' => false, 'message' => 'Search term too short']);
    exit();
}

try {
    $friendModel = new FriendModel($conn);
    $users = $friendModel->searchUsers($user_id, $searchTerm);
    
    echo json_encode([
        'success' => true,
        'users' => $users
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>