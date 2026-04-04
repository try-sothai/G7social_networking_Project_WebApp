<?php
// app/Views/Groups/create_group.php

session_start();
require_once dirname(__DIR__, 3) . '/config/db_config.php';
require_once __DIR__ . '/../Component/dashboard_shell.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "app/Views/Login/login_gmail.php");
    exit();
}

require_once dirname(__DIR__, 2) . '/Controllers/Groups/GroupController.php';

$controller = new GroupController($conn);
$result = $controller->createGroup();
$errors = $result['errors'] ?? [];
$formData = $result['form_data'] ?? [];

require_once dirname(__DIR__, 2) . '/Models/Profile/ProfileModel.php';
$profileModel = new ProfileModel($conn);
$user_id = $_SESSION['user_id'];
$u_d = $profileModel->getUserData($user_id);
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
    <title>Create New Group - LocalConnect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(lc_public_asset('css/Home/dashboard.css')); ?>">
    <link rel="stylesheet" href="../../../public/css/Groups/create_group.css">
</head>
<body>
    <?php lc_shell_render_sidebar(['active' => 'groups']); ?>

    <div class="main-content">
        <?php
            ob_start();
        ?>
            <i class="fa-solid fa-bell"></i>
            <span class="app-shell-notification-dot"></span>
        <?php
            $createGroupNotificationHtml = ob_get_clean();
            lc_shell_render_header([
                'search_placeholder' => 'Search for groups...',
                'notification_html' => $createGroupNotificationHtml,
                'user_name' => (string)$u_d['Username'],
                'profile_image' => $profilePicPath,
            ]);
        ?>

        <div class="container">
            <a href="groups.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Groups</a>
            
            <div class="form-container">
                <h1>Create New Group</h1>
                <p class="subtitle">Build a community around your interests</p>
                
                <?php if (!empty($errors)): ?>
                    <div class="error-messages">
                        <?php foreach ($errors as $error): ?>
                            <div class="error"><?php echo htmlspecialchars($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form action="create_group.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Group Name <span class="required">*</span></label>
                        <input type="text" name="group_name" required 
                               value="<?php echo htmlspecialchars($formData['group_name'] ?? ''); ?>"
                               placeholder="e.g., Tech Enthusiasts Cambodia">
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" placeholder="Tell us what this group is about..."><?php echo htmlspecialchars($formData['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category">
                                <option value="Technology" <?php echo ($formData['category'] ?? '') == 'Technology' ? 'selected' : ''; ?>>Technology</option>
                                <option value="Travel" <?php echo ($formData['category'] ?? '') == 'Travel' ? 'selected' : ''; ?>>Travel</option>
                                <option value="Design" <?php echo ($formData['category'] ?? '') == 'Design' ? 'selected' : ''; ?>>Design</option>
                                <option value="Food" <?php echo ($formData['category'] ?? '') == 'Food' ? 'selected' : ''; ?>>Food</option>
                                <option value="Marketing" <?php echo ($formData['category'] ?? '') == 'Marketing' ? 'selected' : ''; ?>>Marketing</option>
                                <option value="Education" <?php echo ($formData['category'] ?? '') == 'Education' ? 'selected' : ''; ?>>Education</option>
                                <option value="Entertainment" <?php echo ($formData['category'] ?? '') == 'Entertainment' ? 'selected' : ''; ?>>Entertainment</option>
                                <option value="Other" <?php echo ($formData['category'] ?? 'Other') == 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Privacy</label>
                            <select name="privacy">
                                <option value="Public" <?php echo ($formData['privacy'] ?? 'Public') == 'Public' ? 'selected' : ''; ?>>Public - Anyone can join</option>
                                <option value="Private" <?php echo ($formData['privacy'] ?? '') == 'Private' ? 'selected' : ''; ?>>Private - Members must be approved</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Group Avatar</label>
                            <div class="file-upload">
                                <input type="file" name="avatar" accept="image/*" id="avatarInput">
                                <div class="avatar-preview" id="avatarPreview">
                                    <i class="fa-solid fa-cloud-upload-alt"></i>
                                    <span>Click to upload avatar</span>
                                </div>
                            </div>
                            <small>Recommended: Square image, at least 200x200px</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Group Banner</label>
                            <div class="file-upload">
                                <input type="file" name="banner" accept="image/*" id="bannerInput">
                                <div class="banner-preview" id="bannerPreview">
                                    <i class="fa-solid fa-cloud-upload-alt"></i>
                                    <span>Click to upload banner</span>
                                </div>
                            </div>
                            <small>Recommended: 1200x300px</small>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-submit">Create Group</button>
                </form>
            </div>
        </div>
    </div>
    
    <?php lc_shell_render_overlay(); ?>
    <?php require_once __DIR__ . '/../Component/create_post_modal.php'; ?>
    <?php lc_shell_render_user_bootstrap((int)($_SESSION['user_id'] ?? 0), (string)($u_d['Username'] ?? 'User'), $profilePicPath); ?>
    <script src="<?php echo htmlspecialchars(lc_public_asset('js/Home/dashboard.js')); ?>"></script>
    <script src="../../../public/js/common/theme-toggle.js"></script>
    <script src="<?php echo htmlspecialchars(lc_public_asset('js/Home/responsive.js')); ?>"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- ១. កូដសម្រាប់ Preview រូបភាព (កូដចាស់របស់អ្នក) ---
        const avatarInput = document.getElementById('avatarInput');
        const avatarPreview = document.getElementById('avatarPreview');
        
        if (avatarInput) {
            avatarInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        avatarPreview.style.backgroundImage = `url(${event.target.result})`;
                        avatarPreview.style.backgroundSize = 'cover';
                        avatarPreview.style.backgroundPosition = 'center';
                        avatarPreview.innerHTML = '';
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        const bannerInput = document.getElementById('bannerInput');
        const bannerPreview = document.getElementById('bannerPreview');
        
        if (bannerInput) {
            bannerInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        bannerPreview.style.backgroundImage = `url(${event.target.result})`;
                        bannerPreview.style.backgroundSize = 'cover';
                        bannerPreview.style.backgroundPosition = 'center';
                        bannerPreview.innerHTML = '';
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });
</script>
    
</body>
</html>
