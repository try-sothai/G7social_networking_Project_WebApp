<?php
session_start();
require_once '../../../config/db_config.php';
require_once '../../Models/Profile/ProfileModel.php';
require_once __DIR__ . '/../Component/dashboard_shell.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login_gmail.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$profileModel = new ProfileModel($conn);
$u_d = $profileModel->getUserData($user_id);

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
    <title>Socail7 Account Settings</title>
    <?php lc_shell_render_head_assets(); ?>    
    <link rel="stylesheet" href="<?php echo htmlspecialchars(lc_public_asset('css/Home/dashboard.css')); ?>">
    <style>
        .settings-container {
            max-width: 800px;
            margin: 20px auto;
            background: var(--bg-card, #fff);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .settings-header {
            margin-bottom: 25px;
            border-bottom: 1px solid var(--border-color, #e2e8f0);
            padding-bottom: 15px;
        }
        .settings-header h2 {
            margin: 0;
            font-size: 24px;
            color: var(--text-dark, #1e293b);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-dark, #334155);
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color, #cbd5e1);
            border-radius: 8px;
            outline: none;
            background: var(--bg-body, #f8fafc);
            color: var(--text-dark, #1e293b);
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            border-color: var(--primary-color, #3b82f6);
        }
        .btn-save {
            background: var(--primary-color, #3b82f6);
            color: #fff;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.3s;
        }
        .btn-save:hover {
            opacity: 0.9;
        }
        .btn-danger {
            background: #ef4444;
            color: #fff;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.3s;
            margin-top: 10px;
        }
        .btn-danger:hover {
            opacity: 0.9;
        }
        .danger-zone {
            margin-top: 40px;
            padding-top: 25px;
            border-top: 1px dashed #ef4444;
        }
        .danger-zone h3 {
            color: #ef4444;
            margin-top: 0;
        }
        .danger-zone p {
            color: var(--text-muted, #64748b);
            font-size: 14px;
        }
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
            $notificationHtml = ob_get_clean();
            lc_shell_render_header([
                'notification_html' => $notificationHtml,
                'user_name' => (string)($u_d['Username'] ?? ''),
                'profile_image' => $profilePicPath,
            ]);
        ?>

        <div class="settings-container">
            <div class="settings-header">
                <h2><i class="fa-solid fa-gear"></i> Account Settings</h2>
            </div>

            <form id="change-password-form">
                <h3>Change Password</h3>
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" id="current-password" required placeholder="Enter current password">
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" id="new-password" required placeholder="Enter new password">
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" id="confirm-password" required placeholder="Confirm new password">
                </div>
                <button type="submit" class="btn-save" id="save-pwd-btn"><i class="fa-solid fa-lock"></i> Update Password</button>
            </form>

            <div class="danger-zone">
                <h3><i class="fa-solid fa-triangle-exclamation"></i> Danger Zone</h3>
                <p>Once you delete your account, there is no going back. Please be certain.</p>
                <button class="btn-danger" id="delete-account-btn"><i class="fa-solid fa-trash"></i> Delete Account</button>
            </div>
        </div>       
    </div>

    <?php lc_shell_render_overlay(); ?>
    <?php lc_shell_render_user_bootstrap((int)($_SESSION['user_id'] ?? 0), (string)($u_d['Username'] ?? 'User'), $profilePicPath); ?>
    
    <script>
        document.getElementById('change-password-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const currentPwd = document.getElementById('current-password').value;
            const newPwd = document.getElementById('new-password').value;
            const confirmPwd = document.getElementById('confirm-password').value;
            
            if(newPwd !== confirmPwd) {
                alert("New passwords do not match!");
                return;
            }
            
            const btn = document.getElementById('save-pwd-btn');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Updating...';
            
            try {
                const formData = new FormData();
                formData.append('action', 'change_password');
                formData.append('current_password', currentPwd);
                formData.append('new_password', newPwd);
                
                const response = await fetch('<?php echo BASE_URL; ?>app/Controllers/Profile/SettingsController.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                if(result.success) {
                    alert("Password updated successfully!");
                    document.getElementById('change-password-form').reset();
                } else {
                    alert("Error: " + (result.message || "Failed to update password"));
                }
            } catch (error) {
                alert("An error occurred.");
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });

        document.getElementById('delete-account-btn').addEventListener('click', async () => {
            if(confirm("Are you sure you want to permanently delete your account? This action cannot be undone!")) {
                if(confirm("Please confirm again. Click OK to delete.")) {
                    try {
                        const formData = new FormData();
                        formData.append('action', 'delete_account');
                        
                        const response = await fetch('<?php echo BASE_URL; ?>app/Controllers/Profile/SettingsController.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const result = await response.json();
                        if(result.success) {
                            alert("Account deleted. Redirecting...");
                            window.location.href = '<?php echo BASE_URL; ?>app/Controllers/Auth/logout.php';
                        } else {
                            alert("Error: " + (result.message || "Failed to delete account"));
                        }
                    } catch (error) {
                        alert("An error occurred.");
                    }
                }
            }
        });
    </script>
    <script src="<?php echo htmlspecialchars(lc_public_asset('js/Home/dashboard.js')); ?>"></script>
    <script src="../../../public/js/common/theme-toggle.js"></script>
    <script src="<?php echo htmlspecialchars(lc_public_asset('js/Home/responsive.js')); ?>"></script>
</body>
</html>