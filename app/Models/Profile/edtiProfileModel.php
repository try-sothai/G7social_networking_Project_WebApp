<?php
session_start();
require_once '../../../config/db_config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_pic'])) {
    $user_id = $_SESSION['user_id'];
    $upload_dir = '../../../img/'; // ត្រូវបង្កើត Folder នេះទុក
    $file_name = $user_id . '_' . time() . '.jpg';
    $target_file = $upload_dir . $file_name;

    if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
        // Update ទៅក្នុង Database
        $sql = "UPDATE users SET ProfilePic = ? WHERE UserID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$target_file, $user_id]);

        echo json_encode(['success' => true, 'new_image_url' => $target_file]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Upload failed']);
    }
}
?>