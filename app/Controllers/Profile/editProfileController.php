<?php
session_start(); // Start session at the very top
require_once '../../../config/db_config.php';
require_once '../../Models/Profile/editProfileModel.php';

$model = new editProfileModel($conn);
$user_id = $_SESSION['user_id'] ?? null; // Protect if user is not logged in

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Session expired']);
    exit();
}

// Fetch existing data first (to keep the old image name if user does not upload a new one)
$u_d = $model->getUserData($user_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Set Content-Type header to JSON immediately on POST request
    header('Content-Type: application/json');

    $username = $_POST['username'] ?? '';
    $bio = $_POST['bio'] ?? '';
    $Locations = $_POST['Locations'] ?? '';
    $interests = isset($_POST['interests']) ? json_decode($_POST['interests'], true) : [];
    
    // Keep the old image filenames as defaults
    $profile_pic = $u_d['ProfilePic'] ?? null; 
    $cover_pic = $u_d['CoverPic'] ?? null;

    // If a new profile picture is being uploaded
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
        $file_name = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['profile_pic']['name']);
        $upload_path = "../../../public/img/" . $file_name;
        
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_path)) {
            $profile_pic = $file_name; 
        }
    }

    // If a new cover photo is being uploaded
    if (isset($_FILES['cover_pic']) && $_FILES['cover_pic']['error'] === 0) {
        $cover_file_name = time() . '_cover_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['cover_pic']['name']);
        $cover_upload_path = "../../../public/img/" . $cover_file_name;
        
        if (move_uploaded_file($_FILES['cover_pic']['tmp_name'], $cover_upload_path)) {
            $cover_pic = $cover_file_name; 
        }
    }

    $result = $model->updateProfile($user_id, $username, $bio, $profile_pic, $Locations, $cover_pic);
    $model->updateInterests($user_id, $interests);
    
    // Return a clean JSON response (no mixed HTML output)
    echo json_encode(['success' => $result]);
    exit();
}

// Page render section (runs only on initial page load)
$stats = $model->getProfileStats($user_id);
require_once '../../Views/Profile/editProfile.php';
?>