<?php
session_start();
require_once '../../../config/db_config.php';
require_once '../../Helpers/functions.php';
require_once '../../Models/Explore/ProfileModel.php';

// Security check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login_gmail.php");
    exit();
}

$model = new ProfileModel($conn);
$user_id = $_SESSION['user_id'];
$u_d = $model->getUserData($user_id);
$stats = $model->getProfileStats($user_id);
$notifications = $model->getNotifications($user_id);
$unread_notifications = $model->getUnreadNotificationCount($user_id);
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
    <title>LocalConnect V2 - Explore</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/css/Home/dashboard.css">
    <link rel="stylesheet" href="../../../public/css/Explore/explore.css">
    <style>
        /* Add fallback media queries to ensure Explore page is 100% responsive exactly like Dashboard */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
            }
            .explore-container {
                padding: 15px !important;
            }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="logo-section">
        <i class="fa-solid fa-cube"></i>
        <span>LocalConnect</span>
    </div>
    
    <nav class="menu">
        <a href="../Home/dashboard.php" class="item"><i class="fa-solid fa-home"></i> Home</a>
        <a href="explore.php" class="item active"><i class="fa-solid fa-compass"></i> Explore</a>
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
            <input type="text" id="searchInput" placeholder="Search for anything...">
        </div>
        
        <div class="header-actions">
            <div class="icon-btn search-toggle-btn"><i class="fa-solid fa-search"></i></div>
            <div class="icon-btn theme-toggle"><i class="fa-solid fa-moon"></i></div>
            <div class="icon-btn" style="position: relative;" id="notificationBtn">
                <i class="fa-solid fa-bell"></i>
                <?php if($unread_notifications > 0): ?>
                <span style="position: absolute; top: 12px; right: 12px; width: 8px; height: 8px; background: #ef4444; border-radius: 50%;"></span>
                <?php endif; ?>
                <div class="notifications-dropdown" id="notificationsDropdown">
                    <div class="notification-header" style="padding: 12px 16px; font-weight: 600; border-bottom: 1px solid #e5e7eb;">
                        Notifications
                    </div>
                    <div id="notificationsList"></div>
                </div>
            </div>
            <div class="user-profile">
                <span style="font-weight: 700;"><?php echo htmlspecialchars($u_d['Username']); ?></span>
                <a href="../Profile/profile.php">
                    <img src="<?php 
                        $path = '../../../public/img/' . $u_d['ProfilePic'];
                        if (!empty($u_d['ProfilePic']) && file_exists($path)) {
                            echo $path;
                        } else {
                            echo 'https://via.placeholder.com/50';
                        }
                    ?>" alt="Profile">
                </a>
            </div>
        </div>
    </header>

    <div class="explore-container">
        <h1 class="explore-title">Explore the World</h1>
        
        <div class="categories" id="categories">
            <div class="chip active" data-category="All">All</div>
            <div class="chip" data-category="Photography">Photography</div>
            <div class="chip" data-category="Travel">Travel</div>
            <div class="chip" data-category="Architecture">Architecture</div>
            <div class="chip" data-category="Nature">Nature</div>
            <div class="chip" data-category="Digital Art">Digital Art</div>
        </div>

        <div id="postsContainer">
            <div class="loading">
                <i class="fa-solid fa-spinner fa-spin"></i> Loading posts...
            </div>
        </div>
    </div>
</div>

<div class="overlay"></div>

<?php require_once __DIR__ . '/../Component/create_post_modal.php'; ?>

<script>
// Pass PHP variables to JavaScript
window.currentUser = <?php echo json_encode([
    'id' => $user_id, 
    'username' => $u_d['Username'], 
    'profilePic' => !empty($u_d['ProfilePic']) ? getImageUrl($u_d['ProfilePic']) : 'https://via.placeholder.com/50'
]); ?>;
window.APP_CONFIG = <?php echo getJsConfig(); ?>;
</script>
<script src="../../../public/js/config.js"></script>
<script src="../../../public/js/Explore/explore.js"></script>
<script src="../../../public/js/common/theme-toggle.js"></script>
<script>
        const CURRENT_USER_ID = <?php echo (int)($_SESSION['user_id'] ?? 0); ?>;
        const CURRENT_USER_NAME = <?php echo json_encode((string)($u_d['Username'] ?? 'User')); ?>;
        const CURRENT_USER_AVATAR = <?php echo json_encode($profilePicPath); ?>;
    </script>
<script src="../../../public/js/Home/dashboard.js"></script>
<script src="../../../public/js/Home/responsive.js"></script>


</body>
</html>
