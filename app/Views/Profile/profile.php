<?php 
session_start();

require_once '../../../config/db_config.php';

// ប្រសិនបើមិនទាន់ Login ឱ្យវាត្រឡប់ទៅទំព័រ Login វិញ
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login_gmail.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];

try {
    // ១. ទាញព័ត៌មាន User (ប្រើ Prepared Statement ដើម្បីសុវត្ថិភាព)
    $stmt = $conn->prepare("SELECT * FROM users WHERE UserID = :id");
    $stmt->execute(['id' => $current_user_id]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    // ២. រាប់ចំនួន Posts
    $stmt_posts = $conn->prepare("SELECT COUNT(*) as total FROM posts WHERE UserID = :id");
    $stmt_posts->execute(['id' => $current_user_id]);
    $posts = $stmt_posts->fetch(PDO::FETCH_ASSOC)['total'];

    // ៣. រាប់ចំនួន Followers
    $stmt_followers = $conn->prepare("SELECT COUNT(*) as total FROM friends WHERE ReceiverID = :id AND Status = 'accepted'");
    $stmt_followers->execute(['id' => $current_user_id]);
    $followers = $stmt_followers->fetch(PDO::FETCH_ASSOC)['total'];

    // ៤. រាប់ចំនួន Following
    $stmt_following = $conn->prepare("SELECT COUNT(*) as total FROM friends WHERE RequesterID = :id AND Status = 'accepted'");
    $stmt_following->execute(['id' => $current_user_id]);
    $following = $stmt_following->fetch(PDO::FETCH_ASSOC)['total'];

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
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
    <link rel="stylesheet" href="../../../public/css/home/dashboard.css">
    <link rel="stylesheet" href="../../../public/css/Profile/profile.css">
</head>
<body>

    <div class="sidebar">
        <div class="logo-section">
            <i class="fa-solid fa-cube"></i>
            <span>LocalConnect</span>
        </div>
        
        <nav class="nav-menu">
            <a href="../Home/dashboard.php" class="nav-item"><i class="fa-solid fa-home"></i> Home</a>
            <a href="../Explore/explore.php" class="nav-item"><i class="fa-solid fa-compass"></i> Explore</a>
            <a href="../Groups/groups.php" class="nav-item"><i class="fa-solid fa-people-group"></i> Groups</a>
            <a href="../Events/events.php" class="nav-item"><i class="fa-solid fa-calendar-days"></i> Events</a>
            <a href="../Friends/AllFriends.php" class="nav-item"><i class="fa-solid fa-user-friends"></i> Friends</a>
            <a href="../Messages/message.php" class="nav-item"><i class="fa-solid fa-message"></i> Messages</a>           
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
                    <span style="font-weight: 700;"><?php echo htmlspecialchars($user_data['Username']); ?></span>
                    <a href="../Profile/profile.php">
                        <img src="<?php echo !empty($user_data['ProfilePic']) ? $user_data['ProfilePic'] : 'https://i.pravatar.cc/150?u=' . $user_data['UserID']; ?>" alt="Profile">
                    </a>
                </div>
            </div>
        </header>

        <div class="profile-dashboard-wrapper">
            <div class="profile-card">
                <div class="profile-header-main">
                    <div class="avatar-container">
                        <img src="<?php echo !empty($user_data['ProfilePic']) ? $user_data['ProfilePic'] : 'https://i.pravatar.cc/150?u=' . $user_data['UserID']; ?>" alt="User Avatar" class="profile-img-large">
                    </div>
                    
                    <div class="profile-details">
                        <div class="name-row">
                            <h2><?php echo htmlspecialchars($user_data['Username']); ?></h2>
                            <button class="btn-action-light"><i class="fa-solid fa-pen"></i> Edit Profile</button>
                        </div>
                        <p class="user-email"><?php echo htmlspecialchars($user_data['Email']); ?></p>
                        
                        <div class="profile-stats">
                            <div class="stat-item"><strong><?php echo $posts; ?></strong> <span>Posts</span></div>
                            <div class="stat-item"><strong><?php echo $followers; ?></strong> <span>Followers</span></div>
                            <div class="stat-item"><strong><?php echo $following; ?></strong> <span>Following</span></div>
                        </div>

                        <p class="profile-bio"><?php echo !empty($user_data['Bio']) ? htmlspecialchars($user_data['Bio']) : 'ស្វាគមន៍មកកាន់ LocalConnect! 🎉'; ?></p>
                        <p class="profile-location"><i class="fa-solid fa-location-dot"></i> <?php echo !empty($user_data['Phone']) ? htmlspecialchars($user_data['Phone']) : 'មិនមានទីតាំង'; ?></p>

                        <div class="interest-tags">
                            <span class="interest-tag">Music</span>
                            <span class="interest-tag">Technology</span>
                            <span class="interest-tag">Travel</span>
                        </div>
                    </div>
                </div>

                <div class="profile-footer-actions">
                    <button class="btn-secondary"><i class="fa-solid fa-gear"></i> Settings</button>
                    <a href="../Login/logout.php" style="text-decoration: none;">
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