<?php
session_start();
require_once '../../../config/db_config.php';
require_once '../../Models/Profile/editProfileModel.php'; 
require_once __DIR__ . '/../Component/dashboard_shell.php';
// require_once '../../Controllers/Profile/editProfileController.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login_gmail.php");
    exit();
}

$model = new editProfileModel($conn);
$user_id = $_SESSION['user_id'];
$u_d = $model->getUserData($user_id);
$stats = $model->getProfileStats($user_id);
$username = $u_d['Username'] ?? '';
$email = $u_d['Email'] ?? '';
$bio = $u_d['Bio'] ?? '';
$profile_pic = $u_d['ProfilePic'] ?? '';
$posts = $stats['posts'];
$followers = $stats['followers'];
$following = $stats['following'];
$interests = $model->getInterests($user_id);
$profilePicPath = '';
if (!empty($u_d['ProfilePic'])) {
    $candidate = __DIR__ . '/../../../public/img/' . $u_d['ProfilePic'];
    if (is_file($candidate)) {
        $profilePicPath = '../../../public/img/' . $u_d['ProfilePic'];
    }
}
if ($profilePicPath === '') {
    $profilePicPath = 'https://via.placeholder.com/150';
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LocalConnect V2 - Creative Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(lc_public_asset('css/Home/dashboard.css')); ?>">
    <link rel="stylesheet" href="../../../public/css/Profile/editProfile.css">
</head>
<body>
    <?php lc_shell_render_sidebar(['active' => 'profile', 'show_create_post' => true]); ?>

    <div class="main-content">
        <?php
            ob_start();
        ?>
            <i class="fa-solid fa-bell"></i>
            <span class="app-shell-notification-dot"></span>
        <?php
            $editProfileNotificationHtml = ob_get_clean();
            lc_shell_render_header([
                'notification_html' => $editProfileNotificationHtml,
                'user_name' => (string)($u_d['Username'] ?? ''),
                'profile_href' => 'profile.php',
                'profile_image' => $profilePicPath,
            ]);
        ?>
        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-avatar">
                    <img src="<?php 
                            if (!empty($u_d['ProfilePic']) && file_exists('../../../public/img/' . $u_d['ProfilePic'])) {
                                echo '../../../public/img/' . $u_d['ProfilePic'];
                            } else {
                                echo 'https://via.placeholder.com/150';
                            }
                        ?>" alt="Profile">
                    <label for="upload-photo" class="change-photo-btn">
                        <i class="fa-solid fa-camera"></i>
                        Change Photo
                    </label>
                    <input type="file" id="upload-photo" style="display:none;" accept="image/*">
                </div>
                <div class="profile-info">
                    <div class="profile-actions">
                        <input type="text" value="<?php echo htmlspecialchars((string)($u_d['Username'] ?? '')); ?>" class="edit-name">
                        <button class="btn-save">Save</button>
                        <a href="profile.php"><button class="btn-cancel">Cancel</button></a>
                    </div>
                    <p class="user-email"><?php echo htmlspecialchars((string)($u_d['Email'] ?? '')); ?></p>
                    
                    <div class="profile-stats">
                        <span><strong><?php echo $posts; ?></strong> Posts</span>
                        <span><strong><?php echo $followers; ?></strong> Followers</span>
                        <span><strong><?php echo $following; ?></strong> Following</span>
                    </div>
                </div>
            </div>

            <textarea class="bio-text" placeholder="Tell us about yourself..."><?php echo htmlspecialchars((string)$bio); ?></textarea>
            
            <div class="location-edit">
                <i class="fa-solid fa-location-dot"></i>
                <input type="text" id="location-input" value="<?php echo htmlspecialchars((string)($u_d['Locations'] ?? '')); ?>" placeholder="Your location...">
            </div>

            <div class="interests-container" style="margin-top: 25px;">
                <label class="interests-label">Interests & Hobbies</label>
                <div class="interests">
                    <?php foreach ($interests as $interest): ?>
                        <button type="button" class="interest-tag">
                            <?php echo htmlspecialchars((string)$interest); ?>
                            <span class="remove-interest"><i class="fa-solid fa-xmark"></i></span>
                        </button>
                    <?php endforeach; ?>
                    <div class="add-interest-wrapper">
                        <input type="text" id="interest-input" placeholder="e.g. Photography, Coding...">
                        <button type="button" class="add-interest"><i class="fa-solid fa-plus"></i> Add</button>
                    </div>
                </div>
            </div>
            <br>
            <hr>

            <div class="profile-footer">
                <button class="btn_st"><i class="fa-solid fa-gear"></i> Settings</button>
                <button class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Log Out</button>
            </div>

            <div class="profile-tabs">
                <a href="#" class="tab active"><i class="fa-solid fa-table-cells"></i> Posts</a>
                <a href="#" class="tab"><i class="fa-solid fa-bookmark"></i> Saved</a>
            </div>
        </div>
        </div>       
    </div>

    <?php lc_shell_render_overlay(); ?>

    <?php require_once __DIR__ . '/../Component/create_post_modal.php'; ?>
    <?php lc_shell_render_user_bootstrap((int)($_SESSION['user_id'] ?? 0), (string)($u_d['Username'] ?? 'User'), $profilePicPath); ?>
    <script src="../../../public/js/Profile/editProfile.js"></script>
    <script src="<?php echo htmlspecialchars(lc_public_asset('js/Home/dashboard.js')); ?>"></script>
    <script src="../../../public/js/common/theme-toggle.js"></script>
    <script src="<?php echo htmlspecialchars(lc_public_asset('js/Home/responsive.js')); ?>"></script>
</body>
</html>
