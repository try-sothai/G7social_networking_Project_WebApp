<?php
session_start();
require_once '../../../config/db_config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_pic'])) {
    $user_id = $_SESSION['user_id'];
    $upload_dir = '../../../public/img/';
    
    // ពិនិត្យប្រភេទឯកសារ (សុវត្ថិភាព)
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
    $file_type = mime_content_type($_FILES['profile_pic']['tmp_name']);
    
    if (!in_array($file_type, $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type.']);
        exit;
    }

    $file_extension = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
    $file_name = $user_id . '_' . time() . '.' . $file_extension;
    $target_file = $upload_dir . $file_name;

    // ទាញយកផ្លូវរូបភាពចាស់ដើម្បីលុបចេញ
    $stmt = $conn->prepare("SELECT ProfilePic FROM users WHERE UserID = ?");
    $stmt->execute([$user_id]);
    $old_pic = $stmt->fetchColumn();

    if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
        // លុបរូបភាពចាស់ចេញ (ប្រសិនបើមាន)
        if ($old_pic && file_exists($old_pic)) {
            unlink($old_pic);
        }

        // Update ក្នុង Database
        $sql = "UPDATE users SET ProfilePic = ? WHERE UserID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$target_file, $user_id]);

        echo json_encode(['success' => true, 'new_image_url' => $target_file]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Upload failed']);
    }
}
?>