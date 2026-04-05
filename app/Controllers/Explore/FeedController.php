<?php
session_start();
require_once '../../../config/db_config.php';
require_once '../../Models/Explore/ProfileModel.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Please login first.']);
    exit();
}

$current_user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'get_feed') {
    try {
        // កំណត់ចំនួន Post ដែលចង់ទាញយកម្តងៗ (Default គឺ 10)
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        
        $profileModel = new ProfileModel($conn);
        $feeds = $profileModel->getUserFeed($current_user_id, $limit);
        
        echo json_encode(['status' => 'success', 'data' => $feeds]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
?>