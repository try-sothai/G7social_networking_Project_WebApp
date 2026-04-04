<?php
session_start();
require_once '../../../config/db_config.php';
require_once '../../Helpers/functions.php';
require_once '../../Models/Explore/ProfileModel.php';
require_once __DIR__ . '/../Component/dashboard_shell.php';

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
    <link rel="stylesheet" href="<?php echo htmlspecialchars(lc_public_asset('css/Home/dashboard.css')); ?>">
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
<?php lc_shell_render_sidebar(['active' => 'explore']); ?>

<div class="main-content">
    <?php
        ob_start();
    ?>
        <i class="fa-solid fa-bell"></i>
        <?php if ($unread_notifications > 0): ?>
            <span class="app-shell-notification-dot"></span>
        <?php endif; ?>
        <div class="notifications-dropdown" id="notificationsDropdown">
            <div class="notification-header" style="padding: 12px 16px; font-weight: 600; border-bottom: 1px solid #e5e7eb;">
                Notifications
            </div>
            <div id="notificationsList"></div>
        </div>
    <?php
        $exploreNotificationHtml = ob_get_clean();
        lc_shell_render_header([
            'search_input_id' => 'searchInput',
            'notification_html' => $exploreNotificationHtml,
            'notification_button_id' => 'notificationBtn',
            'user_name' => (string)$u_d['Username'],
            'profile_image' => $profilePicPath,
        ]);
    ?>

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

<?php lc_shell_render_overlay(); ?>

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
<?php lc_shell_render_user_bootstrap((int)($_SESSION['user_id'] ?? 0), (string)($u_d['Username'] ?? 'User'), $profilePicPath); ?>
<script src="<?php echo htmlspecialchars(lc_public_asset('js/Home/dashboard.js')); ?>"></script>
<script src="<?php echo htmlspecialchars(lc_public_asset('js/Home/responsive.js')); ?>"></script>


</body>
</html>
