<?php
    session_start();

    require_once '../../../config/db_config.php';

    require_once '../../Models/Profile/ProfileModel.php'; // For user data
    require_once '../../Models/Friends/FriendModel.php'; // For notifications
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../Login/login_gmail.php");
        exit();
    }
    $model = new ProfileModel($conn);
    $user_id = $_SESSION['user_id'];
    $u_d = $model->getUserData($user_id);
    $stats = $model->getProfileStats($user_id);
    $friendModel = new FriendModel($conn); // Create an instance of FriendModel
    $unread_notifications = $friendModel->getUnreadNotificationsCount($user_id); // Use FriendModel
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
    <link rel="stylesheet" href="../../../public/css/Home/dashboard.css?v=<?php echo time(); ?>">
    <script>
        (function () {
            const key = 'localconnect_theme';
            if (!localStorage.getItem(key)) {
                localStorage.setItem(key, 'dark');
            }
        })();
    </script>
</head>
<body>

    <div class="sidebar">
        <div class="logo-section">
            <i class="fa-solid fa-cube"></i>
            <span>LocalConnect</span>
        </div>

        <nav class="menu">
            <a href="dashboard.php" class="item active"><i class="fa-solid fa-home"></i> Home</a>
            <a href="../Explore/explore.php" class="item"><i class="fa-solid fa-compass"></i> Explore</a>
            <a href="../Groups/groups.php" class="item"><i class="fa-solid fa-people-group"></i> Groups</a>
            <a href="../Events/events.php" class="item"><i class="fa-solid fa-calendar-days"></i> Events</a>
            <a href="../Friends/AllFriends.php" class="item"><i class="fa-solid fa-user-friends"></i> Friends</a>
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
                <input type="text" id="header-search-input" placeholder="Search for anything...">
            </div>

            <div class="header-actions">
                <div class="icon-btn search-toggle-btn"><i class="fa-solid fa-search"></i></div>
                <div class="icon-btn theme-toggle"><i class="fa-solid fa-moon"></i></div>
                <div class="icon-btn" style="position: relative;" id="notificationBtn">
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
                </div>
                <div class="user-profile">
                    <span style="font-weight: 700;"><?php echo htmlspecialchars($u_d['Username']); ?></span>
                    <a href="../Profile/profile.php">
                        <img src="<?php echo htmlspecialchars($profilePicPath); ?>" alt="Profile">
                    </a>
                </div>
            </div>
        </header>

        <div class="stories-container" id="stories-container">
            <div class="story-card add-story" id="open-add-story">
                <div class="add-story-icon-wrap">
                    <i class="fa-solid fa-circle-plus" style="color: rgb(119, 72, 255);"></i>
                </div>
                <span class="story-name">Add Story</span>
            </div>
        </div>

        <div class="feed-grid">
            <div class="feed-left">
                <form id="quick-status-form" class="create-post-card">
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
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 45px; height: 45px; border-radius: 12px; background: #ffe4e6; display: flex; align-items: center; justify-content: center; font-size: 20px;">Art</div>
                            <div>
                                <div style="font-weight: 700; font-size: 14px;">Digital Artists</div>
                                <div style="font-size: 12px; color: var(--text);">4.2k members</div>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 45px; height: 45px; border-radius: 12px; background: #fef9c3; display: flex; align-items: center; justify-content: center; font-size: 20px;">Dev</div>
                            <div>
                                <div style="font-weight: 700; font-size: 14px;">Web Developers</div>
                                <div style="font-size: 12px; color: var(--text);">12.8k members</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="overlay"></div>

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

    <script>
        const CURRENT_USER_ID = <?php echo (int)($_SESSION['user_id'] ?? 0); ?>;
        const CURRENT_USER_NAME = <?php echo json_encode((string)($u_d['Username'] ?? 'User')); ?>;
        const CURRENT_USER_AVATAR = <?php echo json_encode($profilePicPath); ?>;
    </script>
    <script src="../../../public/js/Home/dashboard.js"></script>
    <script src="../../../public/js/common/theme-toggle.js"></script>
    <script src="../../../public/js/Home/responsive.js"></script>
</body>
</html>
