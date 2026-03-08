<?php
session_start();
require_once '../../../config/db_config.php';
require_once '../../Models/Profile/ProfileModel.php';

// សុវត្ថិភាព
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../Login/login_gmail.php");
    exit();
}

$model = new ProfileModel($conn);
$user_id = $_SESSION['user_id'];

// ទាញយកទិន្នន័យ
$u_d = $model->getUserData($user_id);
$stats = $model->getProfileStats($user_id);

// បញ្ជូនទៅកាន់ View
include '../../Views/Profile/profile.php';
?>