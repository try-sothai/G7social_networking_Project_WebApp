<?php
session_start();
require_once '../../../config/db_config.php';
require_once '../../Models/Profile/ProfileModel.php';

$model = new ProfileModel($conn);
$user_id = $_SESSION['user_id'];

$u_d = $model->getUserData($user_id);
$stats = $model->getProfileStats($user_id);

include '../../Views/Profile/profile.php';
?>
