<?php 
session_start();

require_once '../../../config/db_config.php';
require_once '../../Models/Profile/ProfileModel.php';
require_once __DIR__ . '/../Component/dashboard_shell.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login_gmail.php");
    exit();
}

$success_msg = $_SESSION['settings_success'] ?? '';
$error_msg = $_SESSION['settings_error'] ?? '';

unset($_SESSION['settings_success']);
unset($_SESSION['settings_error']);

$user_id = $_SESSION['user_id'];
$model = new ProfileModel($conn);
$u_d = $model->getUserData($user_id);

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings | LocalConnect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(lc_public_asset('css/Home/dashboard.css')) ?>">
    <style>
        /* ⚙️ Settings Specific Styles */
        .settings-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px 0 50px;
        }
        .settings-header {
            margin-bottom: 30px;
        }
        .settings-header h1 {
            font-size: 28px;
            color: #0f172a;
            margin-bottom: 8px;
            font-weight: 800;
        }
        .settings-header p {
            color: #64748b;
            font-size: 15px;
        }
        .settings-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            margin-bottom: 24px;
            border: 1px solid #e2e8f0;
        }
        .settings-section-title {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 12px;
        }
        .settings-section-title i {
            color: #6366f1;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #334155;
            font-size: 14px;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-family: inherit;
            font-size: 14px;
            background: #f8fafc;
            transition: 0.2s;
        }
        .form-group input:focus,
        .form-group select:focus {
            border-color: #6366f1;
            outline: none;
            background: #ffffff;
        }
        .password-wrapper {
            position: relative;
            display: block;
        }
        .password-wrapper input {
            padding-right: 40px; /* ទុកចន្លោះកុំឱ្យអក្សររត់ទៅជាន់ Icon ភ្នែក */
        }
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #64748b;
        }
        .toggle-password:hover {
            color: #6366f1;
        }
        .btn-save-settings {
            background: #2563eb;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn-save-settings:hover {
            background: #1d4ed8;
        }
        
        /* ⚠️ Danger Zone */
        .danger-zone {
            border-color: #fecaca;
            background: #fff5f5;
        }
        .danger-zone .settings-section-title {
            color: #dc2626;
            border-bottom-color: #fecaca;
        }
        .danger-zone .settings-section-title i {
            color: #dc2626;
        }
        .btn-danger {
            background: #ef4444;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-danger:hover {
            background: #dc2626;
        }
        .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        /* 🌙 Dark Mode Support for Settings */
        body.dark-mode .settings-header h1 { color: #e2e8f0; }
        body.dark-mode .settings-card { background: #1e293b; border-color: rgba(148, 163, 184, 0.2); }
        body.dark-mode .settings-section-title { color: #f8fafc; border-bottom-color: rgba(148, 163, 184, 0.2); }
        body.dark-mode .form-group label { color: #cbd5e1; }
        body.dark-mode .form-group input, body.dark-mode .form-group select { 
            background: #0f172a; color: #e2e8f0; border-color: rgba(148, 163, 184, 0.3); 
        }
        body.dark-mode .form-group input:focus, body.dark-mode .form-group select:focus { border-color: #6366f1; background: #1e293b; }
        body.dark-mode .danger-zone { background: rgba(220, 38, 38, 0.05); border-color: rgba(220, 38, 38, 0.2); }
        body.dark-mode .danger-zone .settings-section-title { border-bottom-color: rgba(220, 38, 38, 0.2); }
        body.dark-mode .alert-success { background: rgba(22, 101, 52, 0.2); color: #86efac; border-color: rgba(34, 197, 94, 0.3); }
        body.dark-mode .alert-error { background: rgba(153, 27, 27, 0.2); color: #fca5a5; border-color: rgba(239, 68, 68, 0.3); }
    </style>
</head>
<body>
    <?php lc_shell_render_sidebar(['active' => 'profile']); ?>

    <div class="main-content">
        <?php
            ob_start();
        ?>
            <i class="fa-solid fa-bell"></i>
            <span class="app-shell-notification-dot"></span>
        <?php
            $settingsNotificationHtml = ob_get_clean();
            lc_shell_render_header([
                'notification_html' => $settingsNotificationHtml,
                'user_name' => (string)($u_d['Username'] ?? 'User'),
                'profile_image' => $profilePicPath,
            ]);
        ?>
        
        <div class="settings-container">
            <div class="settings-header">
                <a href="profile.php" style="color: #64748b; text-decoration: none; font-size: 14px; margin-bottom: 10px; display: inline-block;"><i class="fa-solid fa-arrow-left"></i> Back to Profile</a>
                <h1>Account Settings</h1>
                <p>Manage your account preferences, security, and privacy.</p>
            </div>

            <?php if ($success_msg): ?>
                <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success_msg) ?></div>
            <?php endif; ?>
            
            <?php if ($error_msg): ?>
                <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error_msg) ?></div>
            <?php endif; ?>

            <!-- Security & Password -->
            <div class="settings-card">
                <h2 class="settings-section-title"><i class="fa-solid fa-shield-halved"></i> Security & Password</h2>
                <form action="../../Controllers/Profile/SettingsController.php" method="POST" id="passwordForm">
                    <input type="hidden" name="action" value="update_password">
                    <div class="form-group">
                        <label>Current Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="current_password" id="current_password" placeholder="Enter current password" required>
                            <i class="fa-regular fa-eye-slash toggle-password" onclick="togglePasswordVisibility('current_password', this)"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="new_password" id="new_password" placeholder="Enter new password" required>
                            <i class="fa-regular fa-eye-slash toggle-password" onclick="togglePasswordVisibility('new_password', this)"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm new password" required>
                            <i class="fa-regular fa-eye-slash toggle-password" onclick="togglePasswordVisibility('confirm_password', this)"></i>
                        </div>
                    </div>
                    <button type="submit" class="btn-save-settings">Update Password</button>
                </form>
            </div>

            <!-- Privacy Preferences -->
            <div class="settings-card">
                <h2 class="settings-section-title"><i class="fa-solid fa-eye"></i> Privacy Preferences</h2>
                <form action="#" method="POST">
                    <div class="form-group">
                        <label>Who can see your posts?</label>
                        <select name="profile_visibility">
                            <option value="public">Public (Everyone)</option>
                            <option value="friends">Friends Only</option>
                            <option value="private">Only me</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Who can send you direct messages?</label>
                        <select name="message_permission">
                            <option value="everyone">Everyone</option>
                            <option value="friends">Friends Only</option>
                            <option value="nobody">No one</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-save-settings">Save Privacy</button>
                </form>
            </div>

            <!-- Danger Zone -->
            <div class="settings-card danger-zone">
                <h2 class="settings-section-title"><i class="fa-solid fa-triangle-exclamation"></i> Danger Zone</h2>
                <p style="margin-bottom: 15px; font-size: 14px; color: #64748b; line-height: 1.5;">
                    Once you delete your account, there is no going back. All your posts, photos, and messages will be permanently deleted. Please be certain.
                </p>
                <form id="deleteAccountForm" action="../../Controllers/Profile/SettingsController.php" method="POST">
                    <input type="hidden" name="action" value="delete_account">
                    <button type="button" class="btn-danger" onclick="confirmDelete()">Delete Account</button>
                </form>
            </div>
        </div>
    </div>

    <?php lc_shell_render_overlay(); ?>
    <?php lc_shell_render_user_bootstrap((int)($_SESSION['user_id'] ?? 0), (string)($u_d['Username'] ?? 'User'), $profilePicPath); ?>
    
    <script src="../../../public/js/common/theme-toggle.js"></script>
    <script src="<?= htmlspecialchars(lc_public_asset('js/Home/responsive.js')) ?>"></script>
    <script src="<?= htmlspecialchars(lc_public_asset('js/Home/dashboard.js')) ?>"></script>
    <script>
        function togglePasswordVisibility(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            }
        }

        function confirmDelete() {
            if(confirm("Are you absolutely sure you want to delete your account? This action cannot be undone!")) {
                document.getElementById('deleteAccountForm').submit();
            }
        }
    </script>
</body>
</html>