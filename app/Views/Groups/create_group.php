<?php
// app/Views/Groups/create_group.php

session_start();
require_once dirname(__DIR__, 3) . '/config/db_config.php';

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
    <link rel="stylesheet" href="../../../public/css/Home/dashboard.css">
    <link rel="stylesheet" href="../../../public/css/Groups/create_group.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo-section">
            <i class="fa-solid fa-cube"></i>
            <span>LocalConnect</span>
        </div>      
        <nav class="menu">
            <a href="../Home/dashboard.php" class="item"><i class="fa-solid fa-home"></i> Home</a>
            <a href="../Explore/explore.php" class="item"><i class="fa-solid fa-compass"></i> Explore</a>
            <a href="../Groups/groups.php" class="item active"><i class="fa-solid fa-people-group"></i> Groups</a>
            <a href="../Events/events.php" class="item"><i class="fa-solid fa-calendar-days"></i> Events</a>
            <a href="../Friends/AddFriends.php" class="item"><i class="fa-solid fa-user-friends"></i> Friends</a>
            <a href="../Messages/message.php" class="item"><i class="fa-solid fa-message"></i> Messages</a> 
            <a href="../Report/userReport.php" class="item"><i class="fa-solid fa-chart-simple"></i> Report</a>          
        </nav>
        <button class="btn-create" id="open-create-post" type="button">
            <i class="fa-solid fa-plus-circle"></i>
            <span>Create New Post</span>
        </button>
    </div>

    <div class="main-content">
        <header>
            <button class="icon-btn menu-toggle"><i class="fa-solid fa-bars"></i></button>
            <div class="search-bar">
                <i class="fa-solid fa-search text"></i>
                <input type="text" placeholder="Search for groups...">
            </div>
            
            <div class="header-actions">
                <div class="icon-btn search-toggle-btn"><i class="fa-solid fa-search"></i></div>
                <div class="icon-btn theme-toggle"><i class="fa-solid fa-moon"></i></div>
                <div class="icon-btn" style="position: relative;">
                    <i class="fa-solid fa-bell"></i>
                    <span style="position: absolute; top: 12px; right: 12px; width: 8px; height: 8px; background: #ef4444; border-radius: 50%;"></span>
                </div>
                <div class="user-profile">
                    <span style="font-weight: 700;"><?php echo htmlspecialchars($u_d['Username']); ?></span>
                    <a href="../Profile/profile.php">
                        <img src="<?php 
                            $path = '../../../public/img/' . ($u_d['ProfilePic'] ?? '');
                            if (!empty($u_d['ProfilePic']) && file_exists($path)) {
                                echo $path;
                            } else {
                                echo 'https://ui-avatars.com/api/?background=6366f1&color=fff&name=' . urlencode($u_d['Username']);
                            }
                        ?>" alt="Profile">
                    </a>
                </div>
            </div>
        </header>

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
    
    <div class="overlay"></div>
    <?php require_once __DIR__ . '/../Component/create_post_modal.php'; ?>
    <script src="../../../public/js/common/theme-toggle.js"></script>
    <script src="../../../public/js/Home/responsive.js"></script>
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
