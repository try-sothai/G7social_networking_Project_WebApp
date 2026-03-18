<?php
session_start(); // បើក session នៅខាងលើគេបង្អស់
require_once '../../../config/db_config.php';
require_once '../../Models/Profile/editProfileModel.php';

$model = new editProfileModel($conn);
$user_id = $_SESSION['user_id'] ?? null; // ការពារបើមិនទាន់ Login

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Session expired']);
    exit();
}

// ទាញទិន្នន័យចាស់ទុកសិន (ដើម្បីយកឈ្មោះរូបភាពចាស់ បើ User មិនដូរប្តូររូបថ្មី)
$u_d = $model->getUserData($user_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // កំណត់ Header ជា JSON ភ្លាមៗនៅពេលមាន POST Request
    header('Content-Type: application/json');

    $username = $_POST['username'] ?? '';
    $bio = $_POST['bio'] ?? '';
    
    // យកឈ្មោះរូបភាពចាស់ជាមុន
    $profile_pic = $u_d['ProfilePic'] ?? null; 

    // បើមានការ Upload រូបភាពថ្មី
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
        $file_name = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['profile_pic']['name']);
        $upload_path = "../../../public/img/" . $file_name;
        
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_path)) {
            $profile_pic = $file_name; 
        }
    }

    $result = $model->updateProfile($user_id, $username, $bio, $profile_pic);
    
    // បញ្ជូនចម្លើយជា JSON ស្អាត (គ្មានអក្សរ HTML លាយឡំ)
    echo json_encode(['success' => $result]);
    exit();
}

// ផ្នែកបង្ហាញទំព័រ (ដើរតែពេល Load page ដំបូងប៉ុណ្ណោះ)
$stats = $model->getProfileStats($user_id);
require_once '../../Views/Profile/editProfile.php';
?>