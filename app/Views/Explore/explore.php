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
    $candidate = __DIR__ . '/../../../public/uploads/avatars/' . basename($u_d['ProfilePic']);
    if (is_file($candidate)) {
        $profilePicPath = '../../../public/uploads/avatars/' . basename($u_d['ProfilePic']);
    }
}
if ($profilePicPath === '') {
    $profilePicPath = '../../../public/img/default-avatar.svg';
}
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Socail7 Explore Page</title>
    <?php lc_shell_render_head_assets(); ?>
    
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

        /* 🔥 Image-Centric Explore Grid (Thumbnails) */
        .posts-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)) !important;
            gap: 25px !important;
            margin-top: 20px !important;
            align-items: start;
        }

        .post-card {
            background: #ffffff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            padding: 0 !important; /* Override standard padding if any */
        }

        .post-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .explore-thumbnail-wrapper {
            width: 100%;
            aspect-ratio: 4 / 3;
            background: #f8fafc;
            overflow: hidden;
            position: relative;
        }

        .explore-post-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .post-card:hover .explore-post-image {
            transform: scale(1.05);
        }

        .explore-text-thumbnail {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            text-align: center;
            color: white;
        }

        .explore-text-thumbnail h3 {
            font-size: 1.3rem;
            font-weight: 700;
            line-height: 1.5;
            text-shadow: 0 2px 5px rgba(0,0,0,0.2);
            margin: 0;
            display: -webkit-box;
            -webkit-line-clamp: 4;
            line-clamp: 4;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .explore-post-header {
            padding: 15px 15px 5px 15px !important;
            border-bottom: none !important;
        }

        .explore-post-content {
            padding: 5px 15px 15px 15px !important;
        }

        .explore-post-content p {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin: 0;
            font-size: 14px;
            color: #475569;
        }

        .explore-post-actions {
            padding: 12px 15px !important;
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 15px;
        }

        body.dark-mode .post-card {
            background: #1e293b;
            border-color: rgba(148, 163, 184, 0.2);
        }
        body.dark-mode .explore-post-actions {
            border-top-color: rgba(148, 163, 184, 0.2);
        }
        body.dark-mode .explore-post-content p {
            color: #cbd5e1;
        }

        /* 🖼️ Lightbox Styles */
        .explore-lightbox {
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
        .explore-lightbox.active {
            opacity: 1;
            visibility: visible;
        }
        .explore-lightbox-img {
            max-width: 90%;
            max-height: 90vh;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            object-fit: contain;
            transform: scale(0.7);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .explore-lightbox.active .explore-lightbox-img {
            transform: scale(1);
        }
        .explore-lightbox-close {
            position: absolute;
            top: 20px;
            right: 30px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s ease;
        }
        .explore-lightbox-close:hover {
            color: #ef4444;
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
            <span class="notification-badge"><?php echo $unread_notifications > 99 ? '99+' : $unread_notifications; ?></span>
        <?php endif; ?>
        <div class="notifications-dropdown" id="notificationsDropdown">
            <div class="notification-header" style="padding: 12px 16px; font-weight: 600; border-bottom: 1px solid #e5e7eb; text-align: left;">
                Notifications
            </div>
            <div id="notificationsList" class="notification-list"></div>
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

<!-- Edit Post Modal (reused by explore page) -->
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

<!-- Lightbox Modal -->
<div id="exploreLightbox" class="explore-lightbox">
    <span class="explore-lightbox-close">&times;</span>
    <img class="explore-lightbox-img" id="exploreLightboxImg" src="" alt="Full Screen Image">
</div>

<script>
// Pass PHP variables to JavaScript
window.currentUser = <?php echo json_encode([
    'id' => $user_id, 
    'username' => $u_d['Username'], 
    'profilePic' => !empty($u_d['ProfilePic']) ? getImageUrl($u_d['ProfilePic']) : '../../../public/img/default-avatar.svg'
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
