<?php
session_start();
require_once '../../../config/db_config.php';
require_once '../../Models/Profile/SettingsModel.php';

// ការពារអ្នកមិនទាន់ Login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../Views/Login/login_gmail.php");
    exit();
}

$action = $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];
$model = new SettingsModel($conn);

// ប្រសិនបើ Action គឺប្តូរលេខសម្ងាត់
if ($action === 'update_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // លក្ខខណ្ឌផ្ទៀងផ្ទាត់
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['settings_error'] = "All fields are required.";
        header("Location: ../../Views/Profile/settings.php");
        exit();
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['settings_error'] = "New password and confirm password do not match.";
        header("Location: ../../Views/Profile/settings.php");
        exit();
    }

    if (strlen($new_password) < 8) {
        $_SESSION['settings_error'] = "New password must be at least 8 characters long.";
        header("Location: ../../Views/Profile/settings.php");
        exit();
    }

    $current_hash = $model->getPasswordHash($user_id);

    // ផ្ទៀងផ្ទាត់លេខសម្ងាត់ចាស់
    if (!password_verify($current_password, $current_hash) && !hash_equals($current_hash, $current_password)) {
        $_SESSION['settings_error'] = "Current password is incorrect.";
        header("Location: ../../Views/Profile/settings.php");
        exit();
    }

    // បំប្លែង (Hash) និងរក្សាទុកលេខសម្ងាត់ថ្មី
    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    if ($model->updatePassword($user_id, $new_hash)) {
        $_SESSION['settings_success'] = "Password updated successfully.";
    } else {
        $_SESSION['settings_error'] = "Failed to update password. Please try again.";
    }

    header("Location: ../../Views/Profile/settings.php");
    exit();
}

// ប្រសិនបើ Action គឺលុបគណនី (Delete Account)
if ($action === 'delete_account') {
    if ($model->deleteAccount($user_id)) {
        // លុប Session និងរុញទៅកាន់ទំព័រ Login
        session_unset();
        session_destroy();
        header("Location: ../../Views/Login/login_gmail.php?error=Account_deleted_successfully");
        exit();
    } else {
        $_SESSION['settings_error'] = "Failed to delete account. Please try again.";
        header("Location: ../../Views/Profile/settings.php");
        exit();
    }
}
?>