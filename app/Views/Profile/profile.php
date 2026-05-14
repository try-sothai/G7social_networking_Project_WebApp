<?php 
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once '../../../config/db_config.php';
require_once '../../Helpers/functions.php';
require_once '../../Models/Profile/ProfileModel.php';
require_once '../../Models/Groups/GroupModel.php';
require_once '../../Models/Friends/FriendModel.php';
require_once __DIR__ . '/../Component/dashboard_shell.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "app/Views/Login/login_gmail.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];
$profile_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : $current_user_id;
$is_own_profile = ($current_user_id === $profile_user_id);

$model = new ProfileModel($conn);
$groupModel = new GroupModel($conn);
$friendModel = new FriendModel($conn);
$unread_notifications = $friendModel->getUnreadNotificationsCount($current_user_id);
// Current user data (for header/sidebar)
$current_u_d = $model->getUserData($current_user_id);

// Profile user data (for content)
$u_d = $model->getUserData($profile_user_id);
if (!$u_d) {
    $u_d = $current_u_d;
    $profile_user_id = $current_user_id;
    $is_own_profile = true;
}

$stats = $model->getProfileStats($profile_user_id);
$userPosts = $model->getUserPosts($profile_user_id, 80, $current_user_id);
$posts = $stats['posts'];
$followers = $stats['followers'];
$following = $stats['following'];
$trendingGroups = $groupModel->getPopularGroups($current_user_id, 3);

// --- Fetch Friends List (both sides) ---
$stmtFriends = $conn->prepare("
    SELECT DISTINCT u.UserID, u.Username, u.ProfilePic
    FROM friends f
    JOIN users u ON (
        CASE
            WHEN f.RequesterID = ? THEN f.ReceiverID
            ELSE f.RequesterID
        END = u.UserID
    )
    WHERE (f.RequesterID = ? OR f.ReceiverID = ?) AND f.Status = 'Accepted'
");
$stmtFriends->execute([$profile_user_id, $profile_user_id, $profile_user_id]);
$friendsList = $stmtFriends->fetchAll(PDO::FETCH_ASSOC);

// Keep followersList/followingList aliases for modal compatibility
$followersList = $friendsList;
$followingList = [];


function parsePostMediaList($imagePathValue) {
    $raw = trim((string)$imagePathValue);
    if ($raw === '') {
        return [];
    }

    if (str_starts_with($raw, '[') && str_ends_with($raw, ']')) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return array_values(array_filter(array_map('strval', $decoded), fn($v) => trim($v) !== ''));
        }
    }

    return [$raw];
}

function buildAppUrl(string $path): string {
    $clean = trim(str_replace('\\', '/', $path));
    if ($clean === '') {
        return '';
    }

    if (preg_match('#^(https?:)?//#i', $clean) || str_starts_with($clean, 'data:')) {
        return $clean;
    }

    if (!str_starts_with($clean, '/')) {
        $clean = '/' . ltrim($clean, '/');
    }

    if (str_starts_with($clean, BASE_URL)) {
        return $clean;
    }

    return rtrim(BASE_URL, '/') . $clean;
}

function normalizePostMediaPath($path) {
    $raw = trim(str_replace('\\', '/', (string)$path));
    if ($raw === '') {
        return '';
    }

    if (str_starts_with($raw, 'data:')) {
        return $raw;
    }

    // Strip hostname from absolute URLs (old ngrok / Cloudflare tunnel URLs)
    if (preg_match('#^(https?:)?//#i', $raw)) {
        $localFile = basename(parse_url($raw, PHP_URL_PATH) ?? '');
        if ($localFile !== '') {
            return buildAppUrl('/public/uploads/posts/' . $localFile);
        }
        return '';
    }

    $base = basename($raw);
    if ($base === '') {
        return '';
    }

    return buildAppUrl('/public/uploads/posts/' . $base);
}

function normalizeProfileImagePath($path) {
    $raw = trim(str_replace('\\', '/', (string)$path));
    if ($raw === '') {
        return '../../../public/img/default-avatar.svg';
    }

    if (str_starts_with($raw, 'data:')) {
        return $raw;
    }

    if (preg_match('#^https?://#i', $raw)) {
        $localFile = basename(parse_url($raw, PHP_URL_PATH) ?? '');
        $localPath = dirname(__DIR__, 3) . '/public/uploads/avatars/' . $localFile;
        if ($localFile !== '' && is_file($localPath)) {
            return buildAppUrl('/public/uploads/avatars/' . $localFile);
        }
        return '../../../public/img/default-avatar.svg';
    }

    if (str_contains($raw, '/public/uploads/avatars/')) {
        $parts = explode('/public/uploads/avatars/', $raw, 2);
        $raw = $parts[1] ?? $raw;
    }

    return buildAppUrl('/public/uploads/avatars/' . ltrim($raw, '/'));
}

function isVideoMedia($path) {
    $lower = strtolower((string)$path);
    return str_ends_with($lower, '.mp4')
        || str_ends_with($lower, '.webm')
        || str_ends_with($lower, '.ogg')
        || str_ends_with($lower, '.mov')
        || str_ends_with($lower, '.m4v');
}

function formatTimeAgo($dateTime) {
    $ts = strtotime((string)$dateTime);
    if (!$ts) {
        return 'Just now';
    }

    $diff = time() - $ts;
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', $ts);
}

$profileAvatarPath = '../../../public/img/default-avatar.svg';
if (!empty($u_d['ProfilePic'])) {
    $profileAvatarCandidate = __DIR__ . '/../../../public/uploads/avatars/' . basename($u_d['ProfilePic']);
    if (is_file($profileAvatarCandidate)) {
        $profileAvatarPath = normalizeProfileImagePath((string)$u_d['ProfilePic']);
    }
}

$profileFeedPosts = [];
foreach ($userPosts as $postRow) {
    $mediaList = parsePostMediaList($postRow['ImagePath'] ?? '');
    $mediaItems = [];
    foreach ($mediaList as $mediaPath) {
        $normalizedMediaPath = normalizePostMediaPath($mediaPath);
        if ($normalizedMediaPath === '') {
            continue;
        }

        $mediaItems[] = [
            'media' => $normalizedMediaPath,
            'is_video' => isVideoMedia($normalizedMediaPath),
        ];
    }

    $profileFeedPosts[] = [
        'post_id' => (int)($postRow['PostID'] ?? 0),
        'content' => (string)($postRow['Content'] ?? ''),
        'created_at' => (string)($postRow['CreatedAt'] ?? ''),
        'time_ago' => formatTimeAgo($postRow['CreatedAt'] ?? ''),
        'media_items' => $mediaItems,
        'likes_count' => (int)($postRow['likes_count'] ?? 0),
        'is_liked' => (bool)($postRow['is_liked'] ?? false),
        'view_count' => (int)($postRow['view_count'] ?? 0),
    ];
}
$headerPicPath = '';
if (!empty($current_u_d['ProfilePic'])) {
    $candidate = __DIR__ . '/../../../public/uploads/avatars/' . $current_u_d['ProfilePic'];
    if (is_file($candidate)) {
        $headerPicPath = normalizeProfileImagePath((string)$current_u_d['ProfilePic']);
    }
}
if ($headerPicPath === '') {
    $headerPicPath = BASE_URL . 'public/img/default-avatar.svg';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Socail7 Profile Page</title>
    <?php lc_shell_render_head_assets(); ?>    
    <link rel="stylesheet" href="<?php echo htmlspecialchars(lc_public_asset('css/Home/dashboard.css')); ?>">
    <link rel="stylesheet" href="../../../public/css/Profile/profile.css">
    <script>
        window.APP_CONFIG = <?php echo getJsConfig(); ?>;
    </script>
    <style>
        /* Lightbox */
        .profile-lightbox {
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
        .profile-lightbox.active {
            opacity: 1;
            visibility: visible;
        }
        .profile-lightbox-img {
            max-width: 90%;
            max-height: 90vh;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            object-fit: contain;
            transform: scale(0.7);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .profile-lightbox.active .profile-lightbox-img {
            transform: scale(1);
        }
        .profile-lightbox-close {
            position: absolute;
            top: 20px;
            right: 30px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s ease;
        }
        .profile-lightbox-close:hover {
            color: #ef4444;
        }

        /* Follow Modal */
        .follow-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }
        .follow-modal.active {
            display: flex;
        }
        .follow-modal-content {
            background: var(--bg-card, #fff);
            width: 90%;
            max-width: 400px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            animation: slideDown 0.3s ease;
        }
        @keyframes slideDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .follow-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color, #e2e8f0);
        }
        .follow-modal-header h3 {
            margin: 0;
            font-size: 18px;
            color: var(--text-dark, #1e293b);
        }
        .follow-modal-close {
            cursor: pointer;
            font-size: 20px;
            color: var(--text-muted, #64748b);
            transition: color 0.2s;
        }
        .follow-modal-close:hover {
            color: #ef4444;
        }
        .follow-list {
            max-height: 350px;
            overflow-y: auto;
            padding: 10px 0;
        }
        .follow-item {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            gap: 15px;
            text-decoration: none;
            color: var(--text-dark, #1e293b);
            transition: background 0.2s;
        }
        .follow-item:hover {
            background: var(--bg-body, #f8fafc);
        }
        .follow-item img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--border-color, #e2e8f0);
        }
        .follow-item-name {
            font-weight: 600;
            font-size: 15px;
        }
        .stat-item {
            transition: transform 0.2s;
        }
        .stat-item:hover {
            transform: scale(1.05);
        }
        
        /* Dark Mode Support for Modal */
        body.dark-mode .follow-modal-content { background: #1e293b; border: 1px solid rgba(148, 163, 184, 0.2); }
        body.dark-mode .follow-modal-header { border-bottom-color: rgba(148, 163, 184, 0.2); }
        body.dark-mode .follow-item { color: #e2e8f0; }
        body.dark-mode .follow-item:hover { background: #0f172a; }
        body.dark-mode .follow-modal-header h3 { color: #f1f5f9; }
    </style>
</head>
<body>
    <?php lc_shell_render_sidebar(['active' => 'profile']); ?>

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
            $profileNotificationHtml = ob_get_clean();
            lc_shell_render_header([
                'notification_html' => $profileNotificationHtml,
                'notification_button_id' => 'notificationBtn',
                'user_name' => (string)$current_u_d['Username'],
                'profile_image' => $headerPicPath,
            ]);
        ?>

        <div class="profile-dashboard-wrapper">
            <div class="profile-card" style="padding: 0; overflow: hidden;">
                <!-- Cover Photo Area -->
                <?php $coverPhotoUrl = !empty($u_d['CoverPic']) && file_exists('../../../public/uploads/avatars/' . $u_d['CoverPic']) ? BASE_URL . 'public/uploads/avatars/' . $u_d['CoverPic'] : BASE_URL . 'public/img/default_cover.svg'; ?>
                <div onclick="openLightbox('<?php echo $coverPhotoUrl; ?>')" style="cursor: pointer; height: 250px; background-color: #cbd5e1; background-image: url('<?php echo $coverPhotoUrl; ?>'); background-size: cover; background-position: center; position: relative;">
                </div>
                
                <div style="padding: 20px 30px 30px; position: relative;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: -80px; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
                        <div class="avatar-container" style="position: relative; z-index: 2;">
                            <?php $avatarPhotoUrl = !empty($u_d['ProfilePic']) && file_exists('../../../public/uploads/avatars/' . $u_d['ProfilePic']) ? '../../../public/uploads/avatars/' . $u_d['ProfilePic'] : BASE_URL . 'public/img/default-avatar.svg'; ?>
                            <img onclick="openLightbox('<?php echo $avatarPhotoUrl; ?>')" class="profile-img-large" src="<?php echo $avatarPhotoUrl; ?>" alt="Profile" style="cursor: pointer; width: 140px; height: 140px; border: 4px solid var(--bg-card, #ffffff); box-shadow: 0 4px 10px rgba(0,0,0,0.1); background: var(--bg-card, #ffffff);">
                        </div>
                        <div style="position: relative; z-index: 2; margin-bottom: 10px;">
                            <?php if ($is_own_profile): ?>
                                <a href="<?php echo BASE_URL; ?>app/Controllers/Profile/editProfileController.php">
                                    <button class="btn-action-light" style="background: linear-gradient(135deg, #2563eb, #3b82f6); color: white; border: none; padding: 10px 24px; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3); border-radius: 12px; font-weight: 600;"><i class="fa-solid fa-pen" style="margin-right: 5px;"></i> Edit Profile</button>
                                </a>
                            <?php else: ?>
                                <div style="display: flex; gap: 10px;">
                                    <a href="<?php echo BASE_URL; ?>app/Views/Messages/message.php?user_id=<?php echo $profile_user_id; ?>"><button class="btn-action-light" style="border-radius: 12px; padding: 10px 20px;"><i class="fa-solid fa-message" style="margin-right: 5px;"></i> Message</button></a>
                                    <button class="btn-action-light" onclick="sendFriendRequest(<?php echo $profile_user_id; ?>, this)" style="border-radius: 12px; padding: 10px 20px; background: #2563eb; color: white; border: none;"><i class="fa-solid fa-user-plus" style="margin-right: 5px;"></i> Add Friend</button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                        
                    <div class="profile-details">
                        <h2 style="font-size: 26px; font-weight: 800; margin-bottom: 5px;"><?php echo htmlspecialchars($u_d['Username']); ?></h2>
                        
                        <?php if ($is_own_profile): ?>
                            <p class="user-email" style="margin-bottom: 15px;"><i class="fa-solid fa-envelope" style="margin-right: 5px;"></i> <?php echo htmlspecialchars($u_d['Email']); ?></p>
                        <?php endif; ?>

                        <div class="profile-stats" style="margin-bottom: 25px;">
                            <div class="stat-item"><strong><?php echo $stats['posts'] ?? 0; ?></strong> <span>Posts</span></div>
                            <div class="stat-item" style="cursor: pointer;" onclick="openFollowModal('followersModal')"><strong><?php echo $stats['friends'] ?? 0; ?></strong> <span>Friends</span></div>
                        </div>

                        <p class="profile-bio"><?php echo !empty($u_d['Bio']) ? htmlspecialchars($u_d['Bio']) : 'Welcome to LocalConnect! 🎉'; ?></p>
                        <p class="profile-location"><i class="fa-solid fa-location-dot"></i> <?php echo !empty($u_d['Locations']) ? htmlspecialchars($u_d['Locations']) : 'No location available'; ?></p>

                        <div class="interest-tags">
                            <?php
                        $interests = $model->getInterests($profile_user_id);
                            foreach ($interests as $interest): ?>
                                <span class="interest-tag"><?php echo htmlspecialchars($interest); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <?php if ($is_own_profile): ?>
                    <div class="profile-footer-actions" style="margin: 0 30px; padding: 25px 0 30px;">
                        <a href="<?php echo BASE_URL; ?>app/Views/Profile/settings.php" style="text-decoration: none;">
                            <button class="btn-secondary"><i class="fa-solid fa-gear"></i> Settings</button>
                        </a>
                        <a href="<?php echo BASE_URL; ?>app/Views/Login/logout.php" style="text-decoration: none;">
                            <button class="btn-logout-alt"><i class="fa-solid fa-arrow-right-from-bracket"></i> Log Out</button>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="content-tabs">
                <div class="tab-item active"><i class="fa-solid fa-grid-2"></i> Posts</div>
                <div class="tab-item"><i class="fa-regular fa-bookmark"></i> Saved</div>
            </div>

            <div class="feed-grid profile-feed-grid">
                <div class="feed-left">
                    <div class="profile-post-feed">
                        <?php if (count($profileFeedPosts) > 0): ?>
                            <?php foreach ($profileFeedPosts as $post): ?>
                        <?php
                            $postMediaPaths = array_map(
                                fn($mediaItem) => (string)($mediaItem['media'] ?? ''),
                                $post['media_items'] ?? []
                            );
                            $postMediaJson = htmlspecialchars(
                                json_encode($postMediaPaths, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            
                            $content = (string)$post['content'];
                            $isShare = false;
                            $shareData = null;
                            if (str_starts_with($content, '[SHARED_POST]')) {
                                $isShare = true;
                                $shareData = json_decode(substr($content, 13), true);
                                $content = '';
                            }

                            $sharedOriginalUser = is_array($shareData)
                                ? trim((string)($shareData['original_user'] ?? ''))
                                : '';
                            if ($sharedOriginalUser === '') {
                                $sharedOriginalUser = 'Original user';
                            }
                            
                            // Convert newlines to clean HTML line breaks
                            $displayContent = nl2br(htmlspecialchars($content));
                        ?>
                        <div class="post-card" data-post-id="<?php echo (int)$post['post_id']; ?>">
                            <div class="post-header">
                                <div class="post-user">
                                    <a href="#" class="post-user-link" style="text-decoration: none; color: inherit;">
                                        <img src="<?php echo htmlspecialchars($profileAvatarPath); ?>" alt="<?php echo htmlspecialchars($u_d['Username']); ?>" onerror="this.src='../../../public/img/default-avatar.svg'">
                                    </a>
                                    <div>
                                        <a href="#" class="post-user-link" style="display: flex; align-items: center; gap: 5px; text-decoration: none; color: inherit;">
                                            <h4 style="font-weight: 800; margin: 0; font-size: 15px;"><?php echo htmlspecialchars($u_d['Username']); ?></h4>
                                            <?php if ($isShare): ?>
                                                <span style="color: #64748b; font-weight: 500; font-size: 13px;"><i class="fa-solid fa-share" style="font-size: 11px; margin-right: 4px;"></i>shared a post</span>
                                            <?php endif; ?>
                                        </a>
                                        <span class="post-time"><?php echo htmlspecialchars($post['time_ago']); ?></span>
                                    </div>
                                </div>
                                <div class="post-menu-wrap">
                                    <button class="post-menu-btn" type="button" data-role="toggle-post-menu" aria-label="Post options">
                                        <i class="fa-solid fa-ellipsis-h text"></i>
                                    </button>
                                    <div class="post-menu-dropdown">
                                        <?php if ($is_own_profile && !$isShare): ?>
                                        <button
                                            type="button"
                                            class="post-menu-item"
                                            data-role="edit-post"
                                            data-post-id="<?php echo (int)$post['post_id']; ?>"
                                            data-content="<?php echo htmlspecialchars((string)$post['content'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-media="<?php echo $postMediaJson; ?>"
                                        >
                                            Edit Post
                                        </button>
                                        <?php endif; ?>
                                        <?php if (!$is_own_profile): ?>
                                        <button
                                            type="button"
                                            class="post-menu-item"
                                            data-role="report-post"
                                            data-post-id="<?php echo (int)$post['post_id']; ?>"
                                        >
                                            Report Post
                                        </button>
                                        <?php endif; ?>
                                        <?php if ($is_own_profile): ?>
                                        <button
                                            type="button"
                                            class="post-menu-item danger"
                                            data-role="delete-post"
                                            data-post-id="<?php echo (int)$post['post_id']; ?>"
                                        >
                                            Delete Post
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <?php if (trim($content) !== ''): ?>
                                <div class="post-content"><?php echo $displayContent; ?></div>
                            <?php endif; ?>

                            <?php if ($isShare && is_array($shareData)): ?>
                                <div class="shared-post-card" style="border: 1px solid var(--border-color, #e2e8f0); border-radius: 16px; overflow: hidden; margin: 12px 0 15px;">
                                    <div class="shared-post-header" style="padding: 12px 15px; background: rgba(148, 163, 184, 0.05); border-bottom: 1px solid var(--border-color, #e2e8f0); display: flex; align-items: center; gap: 10px;">
                                        <img src="<?php echo htmlspecialchars(normalizeProfileImagePath($shareData['original_avatar'] ?? '')); ?>" style="width: 36px; height: 36px; border-radius: 12px; object-fit: cover;">
                                        <div>
                                            <h4 style="margin: 0; font-weight: 700; font-size: 14px;"><?php echo htmlspecialchars($sharedOriginalUser); ?></h4>
                                            <span style="font-size: 11px; color: #64748b;">Original Post</span>
                                        </div>
                                    </div>
                                    <?php if (!empty($shareData['original_content'])): ?>
                                        <div class="post-content" style="padding: 12px 15px; margin-bottom: 0;"><?php echo nl2br(htmlspecialchars($shareData['original_content'])); ?></div>
                                    <?php endif; ?>
                            <?php endif; ?>

                            <?php if (count($post['media_items']) > 1): ?>
                                <div class="post-media-grid">
                                    <?php foreach ($post['media_items'] as $mediaItem): ?>
                                        <?php if ($mediaItem['is_video']): ?>
                                            <video class="post-image post-image-grid" muted playsinline loop preload="metadata" style="max-height: 320px; object-fit: cover;">
                                                <source src="<?php echo htmlspecialchars($mediaItem['media']); ?>">
                                            </video>
                                        <?php else: ?>
                                            <img class="post-image post-image-grid" src="<?php echo htmlspecialchars($mediaItem['media']); ?>" alt="Post media" style="max-height: 320px; object-fit: cover;">
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php elseif (count($post['media_items']) === 1): ?>
                                <?php if ($post['media_items'][0]['is_video']): ?>
                                    <video class="post-image" controls preload="metadata" style="border-radius: 12px;">
                                        <source src="<?php echo htmlspecialchars($post['media_items'][0]['media']); ?>">
                                    </video>
                                <?php else: ?>
                                    <img class="post-image" src="<?php echo htmlspecialchars($post['media_items'][0]['media']); ?>" alt="Post media" style="border-radius: 12px;">
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php if ($isShare && is_array($shareData)): ?>
                                </div>
                            <?php endif; ?>

                            <div class="post-actions-stats" style="display:flex; justify-content:space-between; color:var(--text-muted, #65676b); font-size:13px; margin-bottom:10px; padding: 0 5px;">
                                <div><i class="fa-solid fa-heart" style="color: #ef4444;"></i> <span class="post-like-count"><?php echo $post['likes_count']; ?></span> Likes</div>
                                <div>Interactions</div>
                            </div>

                            <div class="post-actions">
                                <div class="action-item <?php echo $post['is_liked'] ? 'liked' : ''; ?>" data-role="like-post" data-post-id="<?php echo (int)$post['post_id']; ?>"><i class="<?php echo $post['is_liked'] ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i> Like</div>
                                <div class="action-item" data-role="comment-post" data-post-id="<?php echo (int)$post['post_id']; ?>"><i class="fa-regular fa-comment"></i> Comment</div>
                                <div class="action-item" data-role="share-post" data-post-id="<?php echo (int)$post['post_id']; ?>"><i class="fa-regular fa-share-from-square"></i> Share</div>
                                <div style="margin-left: auto; font-size: 13px; color: #94a3b8; display: flex; align-items: center; gap: 5px; pointer-events: none;">
                                    <i class="fa-regular fa-eye"></i>
                                    <span class="profile-view-count"><?php echo $post['view_count']; ?></span>
                                </div>
                            </div>

                            <!-- Comment Input Area -->
                            <div class="post-comments-area" id="comments-area-<?php echo (int)$post['post_id']; ?>" style="display:none; margin-top: 15px; border-top: 1px solid #f0f2f5; padding-top: 15px;">
                                <div style="display:flex; gap: 10px;">
                                    <img src="<?php echo htmlspecialchars($headerPicPath); ?>" style="width: 32px; height: 32px; border-radius: 50%;">
                                    <input type="text" id="comment-input-<?php echo (int)$post['post_id']; ?>" placeholder="Write a comment..." style="flex:1; border-radius: 20px; border: 1px solid #ccd0d5; padding: 8px 15px; outline: none; background: #f0f2f5;">
                                    <button type="button" data-role="submit-comment" data-post-id="<?php echo (int)$post['post_id']; ?>" style="background: transparent; border: none; color: #1877f2; font-weight: bold; cursor: pointer;">Post</button>
                                </div>
                            </div>
                        </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="post-placeholder">No posted media yet</div>
                        <?php endif; ?>
                    </div>
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
                        <div style="display: flex; align-items: center; gap: 12px; cursor: pointer;" onclick="window.location.href='<?php echo BASE_URL; ?>app/Views/Groups/group_details.php?id=<?php echo $tGroup['GroupID']; ?>'">
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
    </div>

    <?php lc_shell_render_overlay(); ?>
    
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
                    <i class="fa-solid fa-image"></i>
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

    <!-- Toast Notification for Add Friend -->
    <div id="toast" style="position: fixed; bottom: 20px; right: 20px; padding: 12px 24px; border-radius: 8px; color: white; display: none; z-index: 1000; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: 0.3s;"></div>

    <!-- Lightbox -->
    <div id="profileLightbox" class="profile-lightbox" onclick="this.classList.remove('active')">
        <span class="profile-lightbox-close">&times;</span>
        <img class="profile-lightbox-img" id="profileLightboxImg" src="" alt="View Photo">
    </div>

    <!-- Friends Modal -->
    <div id="followersModal" class="follow-modal" onclick="if(event.target === this) closeFollowModal('followersModal')">
        <div class="follow-modal-content">
            <div class="follow-modal-header">
                <h3><i class="fa-solid fa-user-group" style="margin-right:8px;color:#6366f1;"></i>Friends</h3>
                <i class="fa-solid fa-xmark follow-modal-close" onclick="closeFollowModal('followersModal')"></i>
            </div>
            <div class="follow-list">
                <?php if(empty($friendsList)): ?>
                    <div style="padding: 20px; text-align: center; color: #64748b;">No friends yet.</div>
                <?php else: ?>
                    <?php foreach($friendsList as $f): ?>
                        <a href="profile.php?user_id=<?php echo $f['UserID']; ?>" class="follow-item">
                            <img src="<?php echo normalizeProfileImagePath($f['ProfilePic']); ?>" alt="">
                            <span class="follow-item-name"><?php echo htmlspecialchars($f['Username']); ?></span>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Following Modal (hidden — not used) -->
    <div id="followingModal" class="follow-modal" onclick="if(event.target === this) closeFollowModal('followingModal')">
        <div class="follow-modal-content">
            <div class="follow-modal-header">
                <h3>Following</h3>
                <i class="fa-solid fa-xmark follow-modal-close" onclick="closeFollowModal('followingModal')"></i>
            </div>
            <div class="follow-list">
                <?php if(empty($followingList)): ?>
                    <div style="padding: 20px; text-align: center; color: #64748b;">Not following anyone yet.</div>
                <?php else: ?>
                    <?php foreach($followingList as $f): ?>
                        <a href="profile.php?user_id=<?php echo $f['UserID']; ?>" class="follow-item">
                            <img src="<?php echo normalizeProfileImagePath($f['ProfilePic']); ?>" alt="">
                            <span class="follow-item-name"><?php echo htmlspecialchars($f['Username']); ?></span>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function sendFriendRequest(userId, button) {
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';
            button.disabled = true;
            
            fetch('../../Controllers/Friends/friend_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=send_request&friend_id=${userId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, '#10b981'); // Green color on success
                    button.innerHTML = '<i class="fa-solid fa-clock"></i> Request Sent';
                } else {
                    showToast(data.message, '#ef4444'); // Red color on error
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            })
            .catch(error => {
                showToast('An error occurred', '#ef4444');
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }
        
        function showToast(message, bgColor) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.style.backgroundColor = bgColor;
            toast.style.display = 'block';
            setTimeout(() => { toast.style.display = 'none'; }, 3000);
        }

        function openLightbox(src) {
            document.getElementById('profileLightboxImg').src = src;
            document.getElementById('profileLightbox').classList.add('active');
        }

        function openFollowModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }
        function closeFollowModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }
    </script>
    <script src="../../../public/js/Profile/profile.js"></script>
    <script src="../../../public/js/common/theme-toggle.js"></script>
    <script src="<?php echo htmlspecialchars(lc_public_asset('js/Home/responsive.js')); ?>"></script>
    <?php require_once __DIR__ . '/../Component/create_post_modal.php'; ?>
    <?php lc_shell_render_user_bootstrap((int)($current_user_id ?? 0), (string)($current_u_d['Username'] ?? 'User'), $headerPicPath); ?>
    <script src="<?php echo htmlspecialchars(lc_public_asset('js/Home/dashboard.js')); ?>"></script>
</body>
</html>
