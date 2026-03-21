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
// ទាញយកទិន្នន័យ
$u_d = $model->getUserData($user_id);
$stats = $model->getProfileStats($user_id);
$posts = $stats['posts'];
$followers = $stats['followers'];
$following = $stats['following'];

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
                            } else {
                               
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
                            } else {
                               
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

                        <p class="profile-bio"><?php echo !empty($u_d['Bio']) ? htmlspecialchars($u_d['Bio']) : 'Welcome to LocalConnect! 🎉'; ?></p>
                        <p class="profile-location"><i class="fa-solid fa-location-dot"></i> <?php echo !empty($u_d['Locations']) ? htmlspecialchars($u_d['Locations']) : 'No location available'; ?></p>

                        <div class="interest-tags">
                            <?php
                            $interests = $model->getInterests($user_id);
                            foreach ($interests as $interest): ?>
                                <span class="interest-tag"><?php echo htmlspecialchars($interest); ?></span>
                            <?php endforeach; ?>
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

            <div class="post-visual-grid">
                <div class="post-placeholder">Post 1</div>
                <div class="post-placeholder">Post 2</div>
                <div class="post-placeholder">Post 3</div>
            </div>
        </div>       
    </div>
</body>
</html>
