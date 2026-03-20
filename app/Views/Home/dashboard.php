<?php
    session_start();

    require_once '../../../config/db_config.php';

    require_once '../../Models/Profile/ProfileModel.php';
    // សុវត្ថិភាព
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../Login/login_gmail.php");
        exit();
    }
    $model = new ProfileModel($conn);
    $user_id = $_SESSION['user_id'];
    $u_d = $model->getUserData($user_id);
    $stats = $model->getProfileStats($user_id);
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LocalConnect V2 - Creative Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/css/Home/dashboard.css">
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

        <button class="btn-create">
            <i class="fa-solid fa-plus-circle"></i>
            <span>Create New Post</span>
        </button>
    </div>

    <div class="main-content">
        <header>
            <div class="search-bar">
                <i class="fa-solid fa-search text"></i>
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
                            } else {
                                
                            }
                        ?>" alt="Profile">
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
                <div class="post-card">
                    <div class="post-header">
                        <div class="post-user">
                            <img src="https://i.pravatar.cc/150?u=sarah" alt="User">
                            <div>
                                <h4 style="font-weight: 800;">Sarah Johnson</h4>
                                <span style="font-size: 13px; color: var(--text);">Traveling in Switzerland • 2h ago</span>
                            </div>
                        </div>
                        <i class="fa-solid fa-ellipsis-h text"></i>
                    </div>
                    <div class="post-content">
                        Waking up to this view is something I could never get used to. The Swiss Alps are truly magical! ✨🏔️ #travel #nature
                    </div>
                    <img src="https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=1000&q=80" class="post-image" alt="Travel">
                    
                    <div class="post-actions">
                        <div class="action-item"><i class="fa-regular fa-heart"></i> 1.2k</div>
                        <div class="action-item"><i class="fa-regular fa-comment"></i> 84</div>
                        <div class="action-item"><i class="fa-regular fa-share-from-square"></i> Share</div>
                    </div>
                </div>
            </div>

            <div class="right-section">
                <div class="section-card">
                    <h3 class="section-title">Trending Groups</h3>
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 45px; height: 45px; border-radius: 12px; background: #ffe4e6; display: flex; align-items: center; justify-content: center; font-size: 20px;">🎨</div>
                            <div>
                                <div style="font-weight: 700; font-size: 14px;">Digital Artists</div>
                                <div style="font-size: 12px; color: var(--text);">4.2k members</div>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 45px; height: 45px; border-radius: 12px; background: #fef9c3; display: flex; align-items: center; justify-content: center; font-size: 20px;">💻</div>
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

    <script>
        const CURRENT_USER_ID = <?php echo (int)($_SESSION['user_id'] ?? 0); ?>;
        const CURRENT_USER_AVATAR = <?php echo json_encode(!empty($u_d['ProfilePic']) ? $u_d['ProfilePic'] : 'https://i.pravatar.cc/150?u=' . ($u_d['UserID'] ?? 0)); ?>;
    </script>
    <script src="../../../public/js/Home/dashboard.js"></script>
</body>
</html>


