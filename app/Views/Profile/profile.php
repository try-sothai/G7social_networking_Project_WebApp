<?php 
session_start();

require_once '../../../config/db_config.php';
require_once '../../Models/Profile/ProfileModel.php';
require_once '../../Models/Groups/GroupModel.php';
require_once __DIR__ . '/../Component/dashboard_shell.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login_gmail.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];
$profile_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : $current_user_id;
$is_own_profile = ($current_user_id === $profile_user_id);

$model = new ProfileModel($conn);
$groupModel = new GroupModel($conn);
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
$userPosts = $model->getUserPosts($profile_user_id, 80);
$posts = $stats['posts'];
$followers = $stats['followers'];
$following = $stats['following'];
$trendingGroups = $groupModel->getPopularGroups($current_user_id, 3);


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

    if (preg_match('#^(https?:)?//#i', $raw) || str_starts_with($raw, 'data:')) {
        return $raw;
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
        return 'https://via.placeholder.com/150';
    }

    if (preg_match('#^(https?:)?//#i', $raw) || str_starts_with($raw, 'data:')) {
        return $raw;
    }

    if (str_contains($raw, '/public/img/')) {
        $parts = explode('/public/img/', $raw, 2);
        $raw = $parts[1] ?? $raw;
    }

    return buildAppUrl('/public/img/' . ltrim($raw, '/'));
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

$profileAvatarPath = 'https://i.pravatar.cc/150?u=' . ((int)($u_d['UserID'] ?? $profile_user_id));
if (!empty($u_d['ProfilePic'])) {
    $profileAvatarCandidate = __DIR__ . '/../../../public/img/' . $u_d['ProfilePic'];
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
    ];
}
$headerPicPath = '';
if (!empty($current_u_d['ProfilePic'])) {
    $candidate = __DIR__ . '/../../../public/img/' . $current_u_d['ProfilePic'];
    if (is_file($candidate)) {
        $headerPicPath = normalizeProfileImagePath((string)$current_u_d['ProfilePic']);
    }
}
if ($headerPicPath === '') {
    $headerPicPath = 'https://via.placeholder.com/150';
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(lc_public_asset('css/Home/dashboard.css')); ?>">
    <link rel="stylesheet" href="../../../public/css/Profile/profile.css">
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
            $profileNotificationHtml = ob_get_clean();
            lc_shell_render_header([
                'notification_html' => $profileNotificationHtml,
            'user_name' => (string)$current_u_d['Username'],
            'profile_image' => $headerPicPath,
            ]);
        ?>

        <div class="profile-dashboard-wrapper">
            <div class="profile-card">
                <div class="profile-header-main">
                    <div class="avatar-container">
                        <img class="profile-img-large" src="<?php 
                            if (!empty($u_d['ProfilePic']) && file_exists('../../../public/img/' . $u_d['ProfilePic'])) {
                                echo '../../../public/img/' . $u_d['ProfilePic'];
                            } else {
                                echo 'https://via.placeholder.com/150';
                            }
                        ?>" alt="Profile">
                    </div>
                    
                    <div class="profile-details">
                        <div class="name-row">
                            <h2><?php echo htmlspecialchars($u_d['Username']); ?></h2>
                            <?php if ($is_own_profile): ?>
                                <a href="editProfile.php"><button class="btn-action-light"><i class="fa-solid fa-pen"></i> Edit Profile</button></a>
                            <?php else: ?>
                                <div style="display: flex; gap: 10px;">
                                    <a href="../Messages/message.php?user_id=<?php echo $profile_user_id; ?>"><button class="btn-action-light"><i class="fa-solid fa-message"></i> Message</button></a>
                                    <button class="btn-action-light" onclick="sendFriendRequest(<?php echo $profile_user_id; ?>, this)"><i class="fa-solid fa-user-plus"></i> Add Friend</button>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($is_own_profile): ?>
                            <p><?php echo htmlspecialchars($u_d['Email']); ?></p>
                        <?php endif; ?>

                        <div class="stat-item"><strong><?php echo $stats['posts'] ?? 0; ?></strong> <span>Posts</span></div>
                        <div class="stat-item"><strong><?php echo $stats['followers'] ?? 0; ?></strong> <span>Followers</span></div>
                        <div class="stat-item"><strong><?php echo $stats['following'] ?? 0; ?></strong> <span>Following</span></div>

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
                <div class="profile-footer-actions">
                    <button class="btn-secondary"><i class="fa-solid fa-gear"></i> Settings</button>
                    <a href="../Login/login_gmail.php" style="text-decoration: none;">
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
                            
                            // បំប្លែងការចុះបន្ទាត់ឲ្យបានស្អាត
                            $displayContent = nl2br(htmlspecialchars($content));
                        ?>
                        <div class="post-card" data-post-id="<?php echo (int)$post['post_id']; ?>">
                            <div class="post-header">
                                <div class="post-user">
                                    <a href="#" class="post-user-link" style="text-decoration: none; color: inherit;">
                                        <img src="<?php echo htmlspecialchars($profileAvatarPath); ?>" alt="<?php echo htmlspecialchars($u_d['Username']); ?>" onerror="this.src='https://via.placeholder.com/150'">
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
                                <div><i class="fa-solid fa-thumbs-up" style="color: var(--primary-color);"></i> Likes</div>
                                <div>Interactions</div>
                            </div>

                            <div class="post-actions">
                                <div class="action-item" data-role="like-post" data-post-id="<?php echo (int)$post['post_id']; ?>"><i class="fa-regular fa-thumbs-up"></i> Like</div>
                                <div class="action-item" data-role="comment-post" data-post-id="<?php echo (int)$post['post_id']; ?>"><i class="fa-regular fa-comment"></i> Comment</div>
                                <div class="action-item" data-role="share-post" data-post-id="<?php echo (int)$post['post_id']; ?>"><i class="fa-regular fa-share-from-square"></i> Share</div>
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
                                    $groupAvatar = !empty($tGroup['GroupAvatar']) && file_exists('../../../public/img/groups/' . $tGroup['GroupAvatar']) 
                                        ? '../../../public/img/groups/' . $tGroup['GroupAvatar'] 
                                        : 'https://ui-avatars.com/api/?background=random&color=fff&bold=true&name=' . urlencode(substr($tGroup['GroupName'], 0, 2));
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

    <!-- Toast Notification សម្រាប់ Add Friend -->
    <div id="toast" style="position: fixed; bottom: 20px; right: 20px; padding: 12px 24px; border-radius: 8px; color: white; display: none; z-index: 1000; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: 0.3s;"></div>

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
                    showToast(data.message, '#10b981'); // ពណ៌បៃតងពេលជោគជ័យ
                    button.innerHTML = '<i class="fa-solid fa-clock"></i> Request Sent';
                } else {
                    showToast(data.message, '#ef4444'); // ពណ៌ក្រហមពេលមានបញ្ហា
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
    </script>
    <script src="../../../public/js/Profile/profile.js"></script>
    <script src="../../../public/js/common/theme-toggle.js"></script>
    <script src="<?php echo htmlspecialchars(lc_public_asset('js/Home/responsive.js')); ?>"></script>
    <?php require_once __DIR__ . '/../Component/create_post_modal.php'; ?>
    <?php lc_shell_render_user_bootstrap((int)($current_user_id ?? 0), (string)($current_u_d['Username'] ?? 'User'), $headerPicPath); ?>
    <script src="<?php echo htmlspecialchars(lc_public_asset('js/Home/dashboard.js')); ?>"></script>
</body>
</html>
