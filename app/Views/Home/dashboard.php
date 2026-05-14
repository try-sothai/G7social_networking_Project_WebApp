<?php
    session_start();

    require_once '../../../config/db_config.php';
    require_once '../../Helpers/functions.php';

    require_once '../../Models/Profile/ProfileModel.php';
    require_once '../../Models/Friends/FriendModel.php';
    require_once '../../Models/Groups/GroupModel.php';
    require_once __DIR__ . '/../Component/dashboard_shell.php';
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../Login/login_gmail.php");
        exit();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    $model = new ProfileModel($conn);
    $groupModel = new GroupModel($conn);
    $user_id = $_SESSION['user_id'];
    $u_d = $model->getUserData($user_id);
    $stats = $model->getProfileStats($user_id);
    $friendModel = new FriendModel($conn); // Create an instance of FriendModel
    $unread_notifications = $friendModel->getUnreadNotificationsCount($user_id); // Use FriendModel
    $profilePicPath = '';
    $trendingGroups = $groupModel->getPopularGroups($user_id, 3);
    if (!empty($u_d['ProfilePic'])) {
        $candidate = __DIR__ . '/../../../public/uploads/avatars/' . $u_d['ProfilePic'];
        if (is_file($candidate)) {
            $profilePicPath = '../../../public/uploads/avatars/' . $u_d['ProfilePic'];
        }
    }
    if ($profilePicPath === '') {
        $profilePicPath = '../../../public/img/default-avatar.svg';
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social7 Home Page</title>
    <?php lc_shell_render_head_assets(); ?>    
    <link rel="stylesheet" href="<?php echo htmlspecialchars(lc_public_asset('css/Home/dashboard.css')); ?>">
    <script>
        window.APP_CONFIG = <?php echo getJsConfig(); ?>;
        (function () {
            const key = 'localconnect_theme';
            if (!localStorage.getItem(key)) {
                localStorage.setItem(key, 'dark');
            }
        })();
    </script>
    <style>
        .stories-wrapper {
            width: 100%;
            overflow-x: auto !important;
            overflow-y: hidden;
            padding-bottom: 10px;
            margin-bottom: 20px;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: var(--text-secondary, #9ca3af) var(--background-secondary, #f1f5f9);
        }
        .stories-wrapper::-webkit-scrollbar {
            height: 8px;
        }
        .stories-wrapper::-webkit-scrollbar-track {
            background: var(--background-secondary, #f1f5f9);
            border-radius: 4px;
        }
        .stories-wrapper::-webkit-scrollbar-thumb {
            background-color: var(--text-secondary, #9ca3af);
            border-radius: 4px;
        }
        .stories-container {
            display: flex;
            flex-wrap: nowrap !important;
            gap: 16px;
            width: max-content;
            padding: 4px;
        }
        .stories-container .story-card {
            flex-shrink: 0;
        }

        /* 🖼️ Lightbox Styles */
        .post-lightbox {
            display: flex;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.85);
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(8px);
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        .post-lightbox.active {
            opacity: 1;
            visibility: visible;
        }
        .post-lightbox-img {
            max-width: 90%;
            max-height: 90vh;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            object-fit: contain;
            transform: scale(0.7);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .post-lightbox.active .post-lightbox-img {
            transform: scale(1);
        }
        .post-lightbox-close {
            position: absolute;
            top: 20px;
            right: 30px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s ease;
        }
        .post-lightbox-close:hover {
            color: #ef4444;
        }

        /* ✨ Modern Post Card Design - App Inspired ✨ */
        .post-card {
            background: #ffffff;
            border-radius: 24px !important;
            border: none !important;
            box-shadow: 0 10px 30px rgba(226, 232, 240, 0.6) !important;
            padding: 0 !important; /* Remove padding so images extend to the full edge */
            margin-bottom: 25px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .post-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(226, 232, 240, 0.9) !important;
        }
        .post-header {
            padding: 20px 20px 10px 20px !important;
            border-bottom: none !important;
        }
        .post-content {
            padding: 0 20px 15px 20px !important;
            font-size: 1.05rem !important;
            line-height: 1.6 !important;
        }
        .post-media-grid, .post-image {
            border-radius: 0 !important; /* Stretch to fill both sides */
            margin: 0 !important;
            width: 100% !important;
        }
        .modern-post-actions {
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            background: #ffffff;
        }
        .action-pill {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 18px; border-radius: 30px;
            background: #f8fafc; border: none; color: #475569;
            font-weight: 600; font-size: 14px; cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .action-pill:hover { background: #e2e8f0; transform: scale(1.05); }
        .action-pill.liked { background: #fee2e2; color: #ef4444; }
        .action-pill i { font-size: 1.1rem; }
        .post-stats-pill { margin-left: auto; font-size: 13px; color: #94a3b8; }

        /* Dark mode support for Modern Cards */
        body.dark-mode .post-card { background: #1e293b !important; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important; }
        body.dark-mode .modern-post-actions { background: #1e293b; }
        body.dark-mode .action-pill { background: #0f172a; color: #cbd5e1; }
        body.dark-mode .action-pill:hover { background: #334155; }
        body.dark-mode .action-pill.liked { background: rgba(239, 68, 68, 0.2); color: #f87171; }
    </style>
</head>
<body>
    <?php lc_shell_render_sidebar(['active' => 'home']); ?>

    <div class="main-content">
        <?php
            ob_start();
        ?>
            <i class="fa-solid fa-bell"></i>
            <?php if ($unread_notifications > 0): ?>
                <span class="notification-badge"><?php echo $unread_notifications > 99 ? '99+' : $unread_notifications; ?></span>
            <?php endif; ?>
            <div class="notifications-dropdown" id="notificationsDropdown">
                <div class="notification-header" style="padding: 12px 16px; font-weight: 600; border-bottom: 1px solid #e5e7eb; text-align: left;">
                    Notifications
                </div>
                <div id="notificationsList" class="notification-list"></div>
            </div>
        <?php
            $homeNotificationHtml = ob_get_clean();
            lc_shell_render_header([
                'search_input_id' => 'header-search-input',
                'notification_html' => $homeNotificationHtml,
                'notification_button_id' => 'notificationBtn',
                'user_name' => (string)$u_d['Username'],
                'profile_image' => $profilePicPath,
            ]);
        ?>

        <div class="stories-wrapper">
            <div class="stories-container" id="stories-container">
                <div class="story-card add-story" id="open-add-story">
                    <div class="add-story-icon-wrap">
                        <i class="fa-solid fa-circle-plus" style="color: rgb(119, 72, 255);"></i>
                    </div>
                    <span class="story-name">Add Story</span>
                </div>
            </div>
        </div>

        <div class="feed-grid">
            <div class="feed-left">
                <form id="quick-status-form" class="create-post-card" enctype="multipart/form-data">
                    <div class="quick-status-top">
                        <a href="../Profile/profile.php" class="quick-status-avatar-link">
                            <img src="<?php echo htmlspecialchars($profilePicPath); ?>" alt="Profile" class="quick-status-avatar">
                        </a>
                        <textarea
                            id="quick-status-input"
                            name="content"
                            maxlength="800"
                            rows="2"
                            placeholder="What's on your mind, <?php echo htmlspecialchars($u_d['Username']); ?>?"
                        ></textarea>
                    </div>

                    <!-- Media Preview Container -->
                    <div id="quick-status-preview" style="display: flex; gap: 10px; flex-wrap: wrap; margin: 0 15px 10px 15px;"></div>
                    <input type="file" id="quick-status-media-input" name="post_media[]" accept="image/*,video/*" multiple style="display: none;">
                    
                    <div class="quick-status-actions">
                        <button type="button" class="quick-status-secondary" id="quick-status-add-photo">
                            <i class="fa-solid fa-photo-film"></i> Photo/Video Post
                        </button>
                        <button type="submit" class="quick-status-submit">
                            <i class="fa-solid fa-paper-plane"></i> Post Status
                        </button>
                    </div>
                    <p id="quick-status-message" class="quick-status-message"></p>
                </form>

                <div id="post-feed-list"></div>
            </div>

            <div class="right-section">
                <div class="section-card">
                    <h3 class="section-title">Trending Groups</h3>
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <?php if (!empty($trendingGroups)): ?>
                            <?php foreach ($trendingGroups as $tGroup): 
                                $groupAvatar = !empty($tGroup['GroupAvatar']) && file_exists('../../../public/uploads/groups/' . $tGroup['GroupAvatar']) 
                                    ? '../../../public/uploads/groups/' . $tGroup['GroupAvatar'] 
                                    : '../../../public/img/default-avatar.svg';
                            ?>
                            <div style="display: flex; align-items: center; gap: 12px; cursor: pointer;" onclick="window.location.href='../Groups/group_details.php?id=<?php echo $tGroup['GroupID']; ?>'">
                                <img src="<?php echo $groupAvatar; ?>" style="width: 45px; height: 45px; border-radius: 12px; object-fit: cover;" alt="Group Avatar">
                                <div>
                                    <div style="font-weight: 700; font-size: 14px;"><?php echo htmlspecialchars($tGroup['GroupName']); ?></div>
                                    <div style="font-size: 12px; color: var(--text);"><?php echo number_format($tGroup['member_count']); ?> members</div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="font-size: 13px; color: var(--text-muted); text-align: center; padding: 10px 0;">No trending groups yet</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php lc_shell_render_overlay(); ?>

    <div class="story-modal-overlay" id="add-story-modal">
        <div class="story-modal-card">
            <div class="story-modal-header">
                <h3>Add New Story</h3>
                <button class="story-modal-close" id="close-add-story" type="button">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="add-story-form" class="story-upload-form" action="story_editor.php" method="post" enctype="multipart/form-data">
                <label for="story-media-input" class="story-file-label">
                    <i class="fa-solid fa-image"></i>
                    <span>Choose Image or Video</span>
                </label>
                <input type="file" id="story-media-input" name="story_media" accept="image/*,video/*">
                <div class="story-file-name" id="selected-file-name">No file chosen</div>
                <button type="submit" class="story-upload-btn">Continue to Edit</button>
                <p class="story-modal-message" id="story-modal-message"></p>
            </form>
        </div>
    </div>

    <div class="story-modal-overlay" id="story-viewer-modal">
        <div class="story-viewer-card">
            <div class="story-viewer-top">
                <div class="story-viewer-user">
                    <img id="story-viewer-avatar" src="" alt="User">
                    <div>
                        <div id="story-viewer-username">User</div>
                        <div id="story-viewer-time">Just now</div>
                    </div>
                </div>
                <button class="story-modal-close" id="close-story-viewer" type="button">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div id="story-viewer-media" class="story-viewer-media"></div>
        </div>
    </div>

    <?php require_once __DIR__ . '/../Component/create_post_modal.php'; ?>

    <!-- Lightbox Modal -->
    <div id="postLightbox" class="post-lightbox">
        <span class="post-lightbox-close">&times;</span>
        <img class="post-lightbox-img" id="postLightboxImg" src="" alt="Full Screen Image">
    </div>

    <div class="story-modal-overlay" id="edit-post-modal">
        <div class="story-modal-card post-modal-card">
            <div class="story-modal-header">
                <h3>Edit Post</h3>
                <button class="story-modal-close" id="close-edit-post" type="button">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form id="edit-post-form" class="story-upload-form" enctype="multipart/form-data">
                <input type="hidden" id="edit-post-id" name="post_id">

                <label for="edit-post-media-input" class="story-file-label">
                    <i class="fa-solid fa-photo-film"></i>
                    <span>Change / Add Media</span>
                </label>
                <input type="file" id="edit-post-media-input" name="new_media[]" accept="image/*,video/*" multiple>
                <div class="story-file-name" id="edit-post-selected-file-name">No new media selected</div>

                <div class="post-editor-area active">
                    <div class="post-editor-preview" id="edit-post-preview"></div>
                    <label for="edit-post-content-input">Caption</label>
                    <textarea id="edit-post-content-input" name="content" maxlength="800" placeholder="Edit your caption..."></textarea>
                </div>

                <button type="submit" class="story-upload-btn">Save Changes</button>
                <p class="story-modal-message" id="edit-post-modal-message"></p>
            </form>
        </div>
    </div>

    <?php lc_shell_render_user_bootstrap((int)($_SESSION['user_id'] ?? 0), (string)($u_d['Username'] ?? 'User'), $profilePicPath); ?>
    <script src="<?php echo htmlspecialchars(lc_public_asset('js/Home/dashboard.js')); ?>"></script>
    <script src="../../../public/js/common/theme-toggle.js"></script>
    <script src="<?php echo htmlspecialchars(lc_public_asset('js/Home/responsive.js')); ?>"></script>

    <!-- Script for Quick Status Media Preview -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const photoBtn = document.getElementById('quick-status-add-photo');
            const fileInput = document.getElementById('quick-status-media-input');
            const previewContainer = document.getElementById('quick-status-preview');
            const form = document.getElementById('quick-status-form');

            if (photoBtn && fileInput) {
                // Click on button opens file dialog
                photoBtn.addEventListener('click', () => fileInput.click());

                fileInput.addEventListener('change', function() {
                    previewContainer.innerHTML = ''; 
                    const files = Array.from(this.files);
                    
                    if (files.length > 4) {
                        alert('You can only upload up to 4 media files per post.');
                        this.value = '';
                        return;
                    }

                    files.forEach((file) => {
                        const fileReader = new FileReader();
                        const previewWrapper = document.createElement('div');
                        previewWrapper.style.position = 'relative';
                        previewWrapper.style.width = '80px';
                        previewWrapper.style.height = '80px';
                        previewWrapper.style.borderRadius = '8px';
                        previewWrapper.style.overflow = 'hidden';
                        previewWrapper.style.border = '1px solid var(--border-color, #e5e7eb)';

                        const removeBtn = document.createElement('button');
                        removeBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
                        removeBtn.style.position = 'absolute';
                        removeBtn.style.top = '4px';
                        removeBtn.style.right = '4px';
                        removeBtn.style.background = 'rgba(0,0,0,0.6)';
                        removeBtn.style.color = 'white';
                        removeBtn.style.border = 'none';
                        removeBtn.style.borderRadius = '50%';
                        removeBtn.style.width = '20px';
                        removeBtn.style.height = '20px';
                        removeBtn.style.cursor = 'pointer';
                        removeBtn.style.display = 'flex';
                        removeBtn.style.alignItems = 'center';
                        removeBtn.style.justifyContent = 'center';
                        removeBtn.style.fontSize = '12px';
                        
                        removeBtn.onclick = function(e) {
                            e.preventDefault();
                            previewWrapper.remove();
                            // Remove file from input
                            const newDt = new DataTransfer();
                            Array.from(fileInput.files).forEach((f) => {
                                if (f.name !== file.name || f.size !== file.size) {
                                    newDt.items.add(f);
                                }
                            });
                            fileInput.files = newDt.files;
                        };

                        fileReader.onload = function(e) {
                            if (file.type.startsWith('image/')) {
                                const img = document.createElement('img');
                                img.src = e.target.result;
                                img.style.width = '100%';
                                img.style.height = '100%';
                                img.style.objectFit = 'cover';
                                previewWrapper.appendChild(img);
                            } else if (file.type.startsWith('video/')) {
                                const video = document.createElement('video');
                                video.src = e.target.result;
                                video.style.width = '100%';
                                video.style.height = '100%';
                                video.style.objectFit = 'cover';
                                previewWrapper.appendChild(video);
                            }
                            previewWrapper.appendChild(removeBtn);
                            previewContainer.appendChild(previewWrapper);
                        };
                        fileReader.readAsDataURL(file);
                    });
                });
            }

            // Clear preview when form is successfully submitted/reset
            if (form) {
                form.addEventListener('reset', () => {
                    previewContainer.innerHTML = '';
                });
            }
        });
    </script>
</body>
</html>
