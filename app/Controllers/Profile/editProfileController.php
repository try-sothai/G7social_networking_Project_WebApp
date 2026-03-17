<?php
// @session_start(); // ប្រើ @ ដើម្បីការពារ Error ជាន់គ្នា
require_once '../../../config/db_config.php';
require_once '../../Models/Profile/editProfileModel.php';

$model = new editProfileModel($conn);
$user_id = $_SESSION['user_id'];

// ១. បង្កើត variable ទុកឈ្មោះរូបភាព (យកឈ្មោះចាស់មកប្រើសិន បើគ្មានការ Upload ថ្មី)
$profile_pic = $u_d['ProfilePic'] ?? null; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $bio = $_POST['bio'] ?? '';

    // ២. បើមានការ Upload រូបភាពថ្មី ត្រូវចាប់យកឈ្មោះរូបភាពថ្មីនោះ
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
        $file_name = time() . '_' . $_FILES['profile_pic']['name'];
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], "../../../public/img/" . $file_name)) {
            $profile_pic = $file_name; // ប្តូរមកប្រើឈ្មោះរូបភាពថ្មី
        }
    }

    // ៣. ហៅ Model ដោយផ្ញើទៅ ៤ argument (UserID, Username, Bio, ProfilePic)
    // ធ្វើបែបនេះនឹងបាត់ពណ៌ក្រហមក្នុង VS Code ភ្លាម!
    $result = $model->updateProfile($user_id, $username, $bio, $profile_pic);

    echo json_encode(['success' => $result]);
    exit();
}

// ផ្នែកបង្ហាញទំព័រធម្មតា
$u_d = $model->getUserData($user_id);
$stats = $model->getProfileStats($user_id);
require_once '../../Views/Profile/editProfile.php';
?>
