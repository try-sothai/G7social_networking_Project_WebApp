<?php
require_once '../../../config/db_config.php';
require_once '../../Helpers/functions.php';
require_once '../../Models/Profile/ProfileModel.php';
require_once '../../Models/Friends/FriendModel.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../Login/login_gmail.php');
    exit();
}

$model = new ProfileModel($conn);
$friendModel = new FriendModel($conn);
$user_id = $_SESSION['user_id'];
$u_d = $model->getUserData($user_id);
$unread_notifications = $friendModel->getUnreadNotificationsCount($user_id);
$story_id = isset($_GET['story_id']) ? (int)$_GET['story_id'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Story View</title>
    <link rel="stylesheet" href="../../../public/css/fontawesome/all.min.css">
    <link rel="stylesheet" href="../../../public/css/fonts/fonts.css">
    
    <link rel="stylesheet" href="../../../public/css/Home/story_dashboard.css?v=<?php echo filemtime('../../../public/css/Home/story_dashboard.css'); ?>">
    <script>
        // Match dashboard default: treat null/missing as 'dark'
        var _theme = localStorage.getItem('localconnect_theme') || 'dark';
        if (_theme === 'dark') {
            document.documentElement.classList.add('dark-mode');
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    </script>
</head>
<body>
    <div class="story-layout">
        <aside class="story-left">
            <a href="dashboard.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
            <div class="story-notif-wrap" id="notificationBtn" style="position:relative;display:inline-flex;align-items:center;cursor:pointer;padding:6px 10px;border-radius:8px;background:var(--fb-btn-bg);margin-bottom:10px;color:var(--fb-btn-color);">
                <i class="fa-solid fa-bell" style="font-size:18px;"></i>
                <?php if ($unread_notifications > 0): ?>
                    <span class="notification-badge" style="position:absolute;top:-4px;right:-4px;"><?php echo $unread_notifications > 99 ? '99+' : $unread_notifications; ?></span>
                <?php endif; ?>
                <div class="notifications-dropdown" id="notificationsDropdown" style="left:0;right:auto;">
                    <div class="notification-header" style="text-align: left;">Notifications</div>
                    <div id="notificationsList" class="notification-list"></div>
                </div>
            </div>
            <div class="profile-chip">
                <img src="<?php echo htmlspecialchars(getAvatarUrl($u_d['ProfilePic'] ?? null)); ?>" alt="Profile">
                <span><?php echo htmlspecialchars($u_d['Username'] ?? 'User'); ?></span>
            </div>
        </aside>

        <main class="story-stage">
            <div class="story-viewer-wrap">
                <button id="story-prev-btn" class="story-nav-btn story-nav-prev" type="button" aria-label="Previous story">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>

                <div class="story-frame">
                <div class="story-top">
                    <div class="story-progress"><span id="story-progress-fill"></span></div>
                    <div class="story-user-row">
                        <a id="story-user-link" href="<?php echo htmlspecialchars(getProfileUrl($user_id)); ?>" class="story-user" style="text-decoration:none; color:inherit;">
                            <img id="story-avatar" src="" alt="User">
                            <div>
                                <div id="story-username">User</div>
                                <div id="story-time">Just now</div>
                            </div>
                        </a>

                        <div class="story-actions">
                            <button id="story-play-pause-btn" class="story-menu-btn" type="button" aria-label="Pause story">
                                <i class="fa-solid fa-pause"></i>
                            </button>
                            <button id="story-menu-btn" class="story-menu-btn" type="button" aria-label="Story menu">
                                <i class="fa-solid fa-ellipsis"></i>
                            </button>
                            <a href="dashboard.php" class="story-close"><i class="fa-solid fa-xmark"></i></a>
                        </div>
                    </div>

                    <div id="story-menu" class="story-menu">
                        <button type="button" id="copy-story-link"><i class="fa-solid fa-link"></i> Copy Link</button>
                        <button type="button" id="delete-story" class="danger"><i class="fa-solid fa-trash"></i> Delete Story</button>
                    </div>
                </div>
                <div id="story-media" class="story-media"></div>
                </div>

                <button id="story-next-btn" class="story-nav-btn story-nav-next" type="button" aria-label="Next story">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
        </main>
    </div>

    <script>
        const STORY_ID = <?php echo $story_id; ?>;
        const CURRENT_USER_ID = <?php echo (int)$user_id; ?>;
    </script>
    <script src="../../../public/js/Home/story_dashboard.js"></script>
    <script src="../../../public/js/common/notifications.js"></script>
    <script>
        // Same logic as dashboard: null/missing → dark
        if ((localStorage.getItem('localconnect_theme') || 'dark') === 'dark') {
            document.body.classList.add('dark-mode');
        }
    </script>
</body>
</html>
