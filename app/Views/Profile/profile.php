<?php 
session_start();

require_once '../../../config/db_config.php';
require_once '../../Models/Profile/ProfileModel.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login_gmail.php");
    exit();
}

$model = new ProfileModel($conn);
$user_id = $_SESSION['user_id'];
$u_d = $model->getUserData($user_id);
$stats = $model->getProfileStats($user_id);
$userPosts = $model->getUserPosts($user_id, 80);
$posts = $stats['posts'];
$followers = $stats['followers'];
$following = $stats['following'];

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

function isVideoMedia($path) {
    $lower = strtolower((string)$path);
    return str_ends_with($lower, '.mp4') || str_ends_with($lower, '.webm') || str_ends_with($lower, '.ogg');
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

$profileAvatarPath = !empty($u_d['ProfilePic']) && file_exists('../../../public/img/' . $u_d['ProfilePic'])
    ? '../../../public/img/' . $u_d['ProfilePic']
    : 'https://i.pravatar.cc/150?u=' . ((int)($u_d['UserID'] ?? $user_id));

$profileFeedPosts = [];
foreach ($userPosts as $postRow) {
    $mediaList = parsePostMediaList($postRow['ImagePath'] ?? '');
    $mediaItems = [];
    foreach ($mediaList as $mediaPath) {
        $mediaItems[] = [
            'media' => (string)$mediaPath,
            'is_video' => isVideoMedia($mediaPath),
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
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/css/home/dashboard.css">
    <link rel="stylesheet" href="../../../public/css/Profile/profile.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo-section">
            <i class="fa-solid fa-cube"></i>
            <span>LocalConnect</span>
        </div>
        
        <nav class="menu">
            <a href="../Home/dashboard.php" class="item"><i class="fa-solid fa-home"></i> Home</a>
            <a href="../Explore/explore.php" class="item"><i class="fa-solid fa-compass"></i> Explore</a>
            <a href="../Groups/groups.php" class="item"><i class="fa-solid fa-people-group"></i> Groups</a>
            <a href="../Events/events.php" class="item"><i class="fa-solid fa-calendar-days"></i> Events</a>
            <a href="../Friends/AllFriends.php" class="item"><i class="fa-solid fa-user-friends"></i> Friends</a>
            <a href="../Messages/message.php" class="item"><i class="fa-solid fa-message"></i> Messages</a>           
        </nav>

        <button class="btn-create">
            <i class="fa-solid fa-plus-circle"></i>
            <span>Create New Post</span>
        </button>
    </div>

    <div class="main-content">
        <header>
            <div class="search-bar">
                <i class="fa-solid fa-search text-muted"></i>
                <input type="text" placeholder="Search for anything...">
            </div>
            
            <div class="header-actions">
                <div class="icon-btn"><i class="fa-solid fa-moon"></i></div>
                <div class="icon-btn" style="position: relative;">
                    <i class="fa-solid fa-bell"></i>
                    <span style="position: absolute; top: 12px; right: 12px; width: 8px; height: 8px; background: #ef4444; border-radius: 50%;"></span>
                </div>
                <div class="user-profile">
                    <span style="font-weight: 700;"><?php echo htmlspecialchars($u_d['Username']); ?></span>
                    <a href="../Profile/profile.php">
                        <img src="<?php 
                            if (!empty($u_d['ProfilePic']) && file_exists('../../../public/img/' . $u_d['ProfilePic'])) {
                                echo '../../../public/img/' . $u_d['ProfilePic'];
                            }
                        ?>" alt="Profile">
                    </a>
                </div>
            </div>
        </header>

        <div class="profile-dashboard-wrapper">
            <div class="profile-card">
                <div class="profile-header-main">
                    <div class="avatar-container">
                        <img class="profile-img-large" src="<?php 
                            if (!empty($u_d['ProfilePic']) && file_exists('../../../public/img/' . $u_d['ProfilePic'])) {
                                echo '../../../public/img/' . $u_d['ProfilePic'];
                            }
                        ?>" alt="Profile">
                    </div>
                    
                    <div class="profile-details">
                        <div class="name-row">
                            <h2><?php echo htmlspecialchars($u_d['Username']); ?></h2>
                            <a href="editProfile.php"><button class="btn-action-light"><i class="fa-solid fa-pen"></i> Edit Profile</button></a>
                        </div>
                        

                        <p><?php echo htmlspecialchars($u_d['Email']); ?></p>

                        <div class="stat-item"><strong><?php echo $stats['posts'] ?? 0; ?></strong> <span>Posts</span></div>
                        <div class="stat-item"><strong><?php echo $stats['followers'] ?? 0; ?></strong> <span>Followers</span></div>
                        <div class="stat-item"><strong><?php echo $stats['following'] ?? 0; ?></strong> <span>Following</span></div>

                        <p class="profile-bio"><?php echo !empty($u_d['Bio']) ? htmlspecialchars($u_d['Bio']) : 'Welcome to LocalConnect!'; ?></p>
                        <p class="profile-location"><i class="fa-solid fa-location-dot"></i> <?php echo !empty($u_d['Phone']) ? htmlspecialchars($u_d['Phone']) : 'No location available'; ?></p>

                        <div class="interest-tags">
                            <span class="interest-tag">Music</span>
                            <span class="interest-tag">Technology</span>
                            <span class="interest-tag">Travel</span>
                        </div>
                    </div>
                </div>

                <div class="profile-footer-actions">
                    <button class="btn-secondary"><i class="fa-solid fa-gear"></i> Settings</button>
                    <a href="../Login/login_gmail.php" style="text-decoration: none;">
                        <button class="btn-logout-alt"><i class="fa-solid fa-arrow-right-from-bracket"></i> Log Out</button>
                    </a>
                </div>
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
                        ?>
                        <article class="post-card profile-post-card">
                            <div class="post-header">
                                <div class="post-user">
                                    <img src="<?php echo htmlspecialchars($profileAvatarPath); ?>" alt="<?php echo htmlspecialchars($u_d['Username']); ?>">
                                    <div>
                                        <h4 style="font-weight: 800;"><?php echo htmlspecialchars($u_d['Username']); ?></h4>
                                        <span class="post-time"><?php echo htmlspecialchars($post['time_ago']); ?></span>
                                    </div>
                                </div>
                                <div class="post-menu-wrap">
                                    <button class="post-menu-btn" type="button" data-role="toggle-post-menu" aria-label="Post options">
                                        <i class="fa-solid fa-ellipsis-h text"></i>
                                    </button>
                                    <div class="post-menu-dropdown">
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
                                        <button
                                            type="button"
                                            class="post-menu-item danger"
                                            data-role="delete-post"
                                            data-post-id="<?php echo (int)$post['post_id']; ?>"
                                        >
                                            Delete Post
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <?php if (trim((string)$post['content']) !== ''): ?>
                                <div class="post-content"><?php echo htmlspecialchars($post['content']); ?></div>
                            <?php endif; ?>

                            <?php if (count($post['media_items']) > 1): ?>
                                <div class="post-media-grid">
                                    <?php foreach ($post['media_items'] as $mediaItem): ?>
                                        <?php if ($mediaItem['is_video']): ?>
                                            <video class="post-image post-image-grid" controls preload="metadata">
                                                <source src="<?php echo htmlspecialchars($mediaItem['media']); ?>">
                                            </video>
                                        <?php else: ?>
                                            <img class="post-image post-image-grid" src="<?php echo htmlspecialchars($mediaItem['media']); ?>" alt="Post media">
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php elseif (count($post['media_items']) === 1): ?>
                                <?php if ($post['media_items'][0]['is_video']): ?>
                                    <video class="post-image" controls preload="metadata">
                                        <source src="<?php echo htmlspecialchars($post['media_items'][0]['media']); ?>">
                                    </video>
                                <?php else: ?>
                                    <img class="post-image" src="<?php echo htmlspecialchars($post['media_items'][0]['media']); ?>" alt="Post media">
                                <?php endif; ?>
                            <?php endif; ?>

                            <div class="post-actions">
                                <div class="action-item"><i class="fa-regular fa-heart"></i> Like</div>
                                <div class="action-item"><i class="fa-regular fa-comment"></i> Comment</div>
                                <div class="action-item"><i class="fa-regular fa-share-from-square"></i> Share</div>
                            </div>
                        </article>
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
                    <i class="fa-solid fa-image"></i>
                    <span>Change / Add Images</span>
                </label>
                <input type="file" id="edit-post-media-input" name="new_media[]" accept="image/*" multiple>
                <div class="story-file-name" id="edit-post-selected-file-name">No new image selected</div>

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
    <script src="../../../public/js/Profile/profile.js"></script>
    <script src="../../../public/js/common/theme-toggle.js"></script>
</body>
</html>

