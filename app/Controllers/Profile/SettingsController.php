<?php
session_start();
require_once '../../../config/db_config.php';
require_once '../../Models/Profile/SettingsModel.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$settingsModel = new SettingsModel($conn);

if ($action === 'change_password') {
    $currentPwd = $_POST['current_password'] ?? '';
    $newPwd = $_POST['new_password'] ?? '';

    if (empty($currentPwd) || empty($newPwd)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all fields.']);
        exit();
    }

    $currentHash = $settingsModel->getPasswordHash($userId);

    // Verify password (supports both new password_hash and old md5/plain-text formats)
    if (password_verify($currentPwd, $currentHash) || md5($currentPwd) === $currentHash || $currentPwd === $currentHash) {
        $newHash = password_hash($newPwd, PASSWORD_DEFAULT);
        $updated = $settingsModel->updatePassword($userId, $newHash);

        if ($updated) {
            echo json_encode(['success' => true, 'message' => 'Password updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update password.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Incorrect current password.']);
    }
    exit();
}

if ($action === 'delete_account') {
    $deleted = $settingsModel->deleteAccount($userId);
    if ($deleted) {
        // Logout will be handled on the frontend upon success
        echo json_encode(['success' => true, 'message' => 'Account deleted.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete account.']);
    }
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
?>