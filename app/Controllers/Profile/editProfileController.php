<?php
require_once '../../../config/db_config.php';
require_once '../../Models/Profile/editProfileModel.php';
require_once __DIR__ . '/../../Helpers/cloudinary.php';

$model = new editProfileModel($conn);
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Session expired']);
    exit();
}

$u_d = $model->getUserData($user_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // CSRF validation
    $csrf = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
        echo json_encode(['success' => false, 'message' => 'Invalid request token. Please reload the page and try again.']);
        exit();
    }

    try {
        $username  = trim($_POST['username']  ?? '');
        $bio       = trim($_POST['bio']       ?? '');
        $Locations = trim($_POST['Locations'] ?? '');
        $interests = isset($_POST['interests']) ? (json_decode($_POST['interests'], true) ?? []) : [];

        $profile_pic = $u_d['ProfilePic'] ?? null;
        $cover_pic   = $u_d['CoverPic']   ?? null;

        $uploadDirAbs = __DIR__ . '/../../../public/uploads/avatars/';
        if (!is_dir($uploadDirAbs)) {
            mkdir($uploadDirAbs, 0775, true);
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $maxSize      = 5 * 1024 * 1024; // 5 MB

        // Profile picture upload
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
            $tmp      = $_FILES['profile_pic']['tmp_name'];
            $origName = $_FILES['profile_pic']['name'];
            $finfo    = new finfo(FILEINFO_MIME_TYPE);
            $mime     = $finfo->file($tmp);

            if (!in_array($mime, $allowedMimes, true)) {
                echo json_encode(['success' => false, 'message' => 'Profile picture must be JPEG, PNG, WebP, or GIF.']);
                exit();
            }
            if ($_FILES['profile_pic']['size'] > $maxSize) {
                echo json_encode(['success' => false, 'message' => 'Profile picture must be under 5 MB.']);
                exit();
            }

            $ext       = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            $file_name = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

            if (move_uploaded_file($tmp, $uploadDirAbs . $file_name)) {
                if (lc_cloudinary_enabled()) {
                    $cloudUrl = lc_cloudinary_upload($uploadDirAbs . $file_name, 'social7/avatars');
                    if ($cloudUrl) {
                        @unlink($uploadDirAbs . $file_name);
                        $profile_pic = $cloudUrl;
                    } else {
                        $profile_pic = $file_name;
                    }
                } else {
                    if ($profile_pic && !str_starts_with($profile_pic, 'http') && is_file($uploadDirAbs . $profile_pic)) {
                        @unlink($uploadDirAbs . $profile_pic);
                    }
                    $profile_pic = $file_name;
                }
            }
        }

        // Cover photo upload
        if (isset($_FILES['cover_pic']) && $_FILES['cover_pic']['error'] === 0) {
            $tmp      = $_FILES['cover_pic']['tmp_name'];
            $origName = $_FILES['cover_pic']['name'];
            $finfo    = new finfo(FILEINFO_MIME_TYPE);
            $mime     = $finfo->file($tmp);

            if (!in_array($mime, $allowedMimes, true)) {
                echo json_encode(['success' => false, 'message' => 'Cover photo must be JPEG, PNG, WebP, or GIF.']);
                exit();
            }
            if ($_FILES['cover_pic']['size'] > $maxSize) {
                echo json_encode(['success' => false, 'message' => 'Cover photo must be under 5 MB.']);
                exit();
            }

            $ext             = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            $cover_file_name = time() . '_cover_' . bin2hex(random_bytes(4)) . '.' . $ext;

            if (move_uploaded_file($tmp, $uploadDirAbs . $cover_file_name)) {
                if (lc_cloudinary_enabled()) {
                    $cloudUrl = lc_cloudinary_upload($uploadDirAbs . $cover_file_name, 'social7/covers');
                    if ($cloudUrl) {
                        @unlink($uploadDirAbs . $cover_file_name);
                        $cover_pic = $cloudUrl;
                    } else {
                        $cover_pic = $cover_file_name;
                    }
                } else {
                    if ($cover_pic && !str_starts_with($cover_pic, 'http') && is_file($uploadDirAbs . $cover_pic)) {
                        @unlink($uploadDirAbs . $cover_pic);
                    }
                    $cover_pic = $cover_file_name;
                }
            }
        }

        if (empty($username)) {
            echo json_encode(['success' => false, 'message' => 'Username cannot be empty.']);
            exit();
        }

        $result = $model->updateProfile($user_id, $username, $bio, $profile_pic, $Locations, $cover_pic);
        $model->updateInterests($user_id, $interests);

        echo json_encode(['success' => (bool)$result]);
    } catch (Throwable $e) {
        error_log('editProfileController error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }
    exit();
}

$stats = $model->getProfileStats($user_id);
require_once '../../Views/Profile/editProfile.php';
