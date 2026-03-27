<?php
session_start();
require_once '../../../config/db_config.php';
require_once '../../Models/Profile/ProfileModel.php';

$model = new ProfileModel($conn);
$user_id = $_SESSION['user_id'];

$u_d = $model->getUserData($user_id);
$stats = $model->getProfileStats($user_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $result = $model->updateProfile(
        $user_id,
        $_POST['username'] ?? '',
        $_POST['bio'] ?? '',
        $_POST['profile_pic'] ?? ($u_d['ProfilePic'] ?? null),
        $_POST['Locations'] ?? ''
    );

    echo json_encode(['success' => $result]);
    exit();
}

include '../../Views/Profile/profile.php';
?>
