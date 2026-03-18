<?php
session_start();
require_once '../../../config/db_config.php';
require_once '../../Models/Profile/ProfileModel.php';

$model = new ProfileModel($conn);
$user_id = $_SESSION['user_id'];

// ទាញយកទិន្នន័យ
$u_d = $model->getUserData($user_id);
$stats = $model->getProfileStats($user_id);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $result = $model->updateProfile($user_id, $_POST['username'], $_POST['bio']);
    echo json_encode(['success' => $result]);
    exit(); // ឈប់ត្រឹមនេះ ដើម្បីកុំឱ្យវា include View នាំឱ្យខូច JSON
}

// បើមិនមែន POST ទេ ឱ្យបង្ហាញទំព័រធម្មតា
include '../../Views/Profile/profile.php';
?>