<?php
session_start();
require_once '../../../config/db_config.php';
require_once '../../Models/Profile/editProfileModel.php';
require_once '../../Models/Friends/FriendModel.php';
require_once __DIR__ . '/../Component/dashboard_shell.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login_gmail.php");
    exit();
}

$model = new editProfileModel($conn);
$user_id = $_SESSION['user_id'];
$u_d = $model->getUserData($user_id);
$stats = $model->getProfileStats($user_id);
$friendModel = new FriendModel($conn);
$unread_notifications = $friendModel->getUnreadNotificationsCount($user_id);
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
    $candidate = __DIR__ . '/../../../public/uploads/avatars/' . $u_d['ProfilePic'];
    if (is_file($candidate)) {
        $profilePicPath = BASE_URL . 'public/uploads/avatars/' . $u_d['ProfilePic'];
    }
}
if ($profilePicPath === '') {
    $profilePicPath = BASE_URL . 'public/img/default-avatar.svg';
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Socail7 Edit Profile Page</title>
    <?php lc_shell_render_head_assets(); ?>    
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
            <?php if ($unread_notifications > 0): ?>
                <span class="notification-badge"><?php echo $unread_notifications > 99 ? '99+' : $unread_notifications; ?></span>
            <?php endif; ?>
            <div class="notifications-dropdown" id="notificationsDropdown">
                <div class="notification-header" style="padding: 12px 16px; font-weight: 600; border-bottom: 1px solid #e5e7eb; text-align: left;">Notifications</div>
                <div id="notificationsList" class="notification-list"></div>
            </div>
        <?php
            $editProfileNotificationHtml = ob_get_clean();
            lc_shell_render_header([
                'notification_html' => $editProfileNotificationHtml,
                'notification_button_id' => 'notificationBtn',
                'user_name' => (string)($u_d['Username'] ?? ''),
                'profile_image' => $profilePicPath,
            ]);
        ?>
        <div class="profile-container">
            <!-- Cover Photo Area -->
            <div id="cover-preview" style="height: 250px; border-radius: 15px; background-color: #cbd5e1; background-image: url('<?php echo !empty($u_d['CoverPic']) && file_exists('../../../public/uploads/avatars/' . $u_d['CoverPic']) ? BASE_URL . 'public/uploads/avatars/' . $u_d['CoverPic'] : BASE_URL . 'public/img/default_cover.svg'; ?>'); background-size: cover; background-position: center; position: relative; margin-bottom: 70px;">
                <label for="upload-cover" class="change-photo-btn" style="position: absolute; bottom: 15px; right: 15px; background: rgba(0,0,0,0.7); font-size: 13px; border: 1px solid rgba(255,255,255,0.3);">
                    <i class="fa-solid fa-camera"></i> Change Cover
                </label>
                <input type="file" id="upload-cover" style="display:none;" accept="image/*">
                
                <!-- Profile Photo Overlapping -->
                <div class="profile-avatar" style="position: absolute; bottom: -50px; left: 30px; display: flex; flex-direction: row; align-items: flex-end; gap: 15px;">
                    <img id="profile-preview" src="<?php 
                            if (!empty($u_d['ProfilePic']) && file_exists('../../../public/uploads/avatars/' . $u_d['ProfilePic'])) {
                                echo BASE_URL . 'public/uploads/avatars/' . $u_d['ProfilePic'];
                            } else {
                                echo BASE_URL . 'public/img/default-avatar.svg';
                            }
                        ?>" alt="Profile" style="border: 4px solid var(--bg-card, #fff); background: var(--bg-card, #fff); box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                    <label for="upload-photo" class="change-photo-btn" style="margin-bottom: 15px; font-size: 13px;">
                        <i class="fa-solid fa-camera"></i>
                        Change Photo
                    </label>
                    <input type="file" id="upload-photo" style="display:none;" accept="image/*">
                </div>
            </div>

            <div class="profile-info" style="margin-top: 10px; padding: 0 15px;">
                <div class="profile-actions" style="margin-bottom: 15px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                    <input type="text" id="username-input" value="<?php echo htmlspecialchars((string)($u_d['Username'] ?? '')); ?>" class="edit-name" style="flex: 1; min-width: 200px; max-width: 350px;">
                    <div style="margin-left: auto; display: flex; gap: 10px;">
                        <button type="button" class="btn-save" id="save-btn" style="margin: 0;"><i class="fa-solid fa-check"></i> Save Changes</button>
                    <a href="<?php echo BASE_URL; ?>app/Views/Profile/profile.php"><button type="button" class="btn-cancel" style="margin: 0;"><i class="fa-solid fa-xmark"></i> Cancel</button></a>
                    </div>
                </div>
                <p class="user-email" style="margin-bottom: 15px;"><i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars((string)($u_d['Email'] ?? '')); ?></p>
                
                <div class="profile-stats" style="margin-bottom: 25px;">
                    <span><strong><?php echo $posts; ?></strong> Posts</span>
                    <span><strong><?php echo $followers; ?></strong> Followers</span>
                    <span><strong><?php echo $following; ?></strong> Following</span>
                </div>
            </div>

            <textarea id="bio-input" class="bio-text" placeholder="Tell us about yourself..."><?php echo htmlspecialchars((string)$bio); ?></textarea>
            
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
                <a href="<?php echo BASE_URL; ?>app/Views/Profile/settings.php" style="text-decoration: none;">
                    <button class="btn_st"><i class="fa-solid fa-gear"></i> Settings</button>
                </a>
                <a href="<?php echo BASE_URL; ?>app/Views/Login/logout.php" style="text-decoration: none;">
                    <button class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Log Out</button>
                </a>
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
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const saveBtn = document.getElementById('save-btn');
            const profileInput = document.getElementById('upload-photo');
            const coverInput = document.getElementById('upload-cover');
            const profilePreview = document.getElementById('profile-preview');
            const coverPreview = document.getElementById('cover-preview');
            const usernameInput = document.getElementById('username-input');
            const bioInput = document.getElementById('bio-input');
            const locationInput = document.getElementById('location-input');
            const interestInput = document.getElementById('interest-input');
            const addInterestBtn = document.querySelector('.add-interest');
            const interestsContainer = document.querySelector('.interests');
            
            let interestsList = [];

            // Previews
            profileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    profilePreview.src = URL.createObjectURL(this.files[0]);
                }
            });

            coverInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    coverPreview.style.backgroundImage = `url('${URL.createObjectURL(this.files[0])}')`;
                }
            });

            // Load Existing Interests
            document.querySelectorAll('.interest-tag').forEach(tag => {
                const text = tag.childNodes[0].nodeValue.trim();
                if(text) interestsList.push(text);
                
                tag.querySelector('.remove-interest').addEventListener('click', function() {
                    interestsList = interestsList.filter(i => i !== text);
                    tag.remove();
                });
            });

            // Add New Interest
            addInterestBtn.addEventListener('click', () => {
                const val = interestInput.value.trim();
                if(val && !interestsList.includes(val)) {
                    interestsList.push(val);
                    
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'interest-tag';
                    btn.innerHTML = `${val} <span class="remove-interest"><i class="fa-solid fa-xmark"></i></span>`;
                    
                    btn.querySelector('.remove-interest').addEventListener('click', function() {
                        interestsList = interestsList.filter(i => i !== val);
                        btn.remove();
                    });
                    
                    interestsContainer.insertBefore(btn, document.querySelector('.add-interest-wrapper'));
                    interestInput.value = '';
                }
            });

            interestInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') { e.preventDefault(); addInterestBtn.click(); }
            });

            // Save via AJAX to Controller
            saveBtn.addEventListener('click', async () => {
                const formData = new FormData();
                formData.append('username', usernameInput.value.trim());
                formData.append('bio', bioInput.value.trim());
                formData.append('Locations', locationInput.value.trim());
                formData.append('interests', JSON.stringify(interestsList));
                
                if (profileInput.files.length > 0) formData.append('profile_pic', profileInput.files[0]);
                if (coverInput.files.length > 0) formData.append('cover_pic', coverInput.files[0]);
                
                const originalText = saveBtn.innerHTML;
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
                
                try {
                    const response = await fetch('<?php echo BASE_URL; ?>app/Controllers/Profile/editProfileController.php', { method: 'POST', body: formData });
                    const result = await response.json();
                    if (result.success) window.location.href = '<?php echo BASE_URL; ?>app/Views/Profile/profile.php';
                    else { alert('Failed: ' + (result.message || 'Unknown error')); saveBtn.disabled = false; saveBtn.innerHTML = originalText; }
                } catch (error) { alert('An error occurred.'); saveBtn.disabled = false; saveBtn.innerHTML = originalText; }
            });
        });
    </script>
    <script src="<?php echo htmlspecialchars(lc_public_asset('js/Home/dashboard.js')); ?>"></script>
    <script src="../../../public/js/common/theme-toggle.js"></script>
    <script src="<?php echo htmlspecialchars(lc_public_asset('js/Home/responsive.js')); ?>"></script>
</body>
</html>
