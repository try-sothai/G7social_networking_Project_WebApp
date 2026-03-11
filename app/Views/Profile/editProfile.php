<?php 
session_start();

require_once '../../../config/db_config.php';
require_once '../../Models/Profile/editProfileModel.php';
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
    <title>LocalConnect V2 - Creative Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/css/home/dashboard.css">
    <link rel="stylesheet" href="../../../public/css/Profile/editProfile.css">
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
                    <span style="font-weight: 700;"><?php echo htmlspecialchars($u_d['Username']); ?></span>
                    <a href="../Profile/profile.php">
                        <img src="<?php echo !empty($u_d['ProfilePic']) ? $u_d['ProfilePic'] : 'https://i.pravatar.cc/150?u=' . $u_d['UserID']; ?>" alt="Profile">
                    </a>
                </div>
            </div>
        </header>
        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-avatar">
                    <img id="profile-img" src="<?php echo !empty($u_d['ProfilePic']) ? $u_d['ProfilePic'] : 'https://i.pravatar.cc/150?u=' . $u_d['UserID']; ?>" alt="Profile">
                    <label for="upload-photo" class="change-photo" style="cursor:pointer;">Change Photo</label>
                    <input type="file" id="upload-photo" style="display:none;" accept="image/*">
                </div>
                <div class="profile-info">
                    <div class="profile-actions">
                        <input type="text" value="<?php echo htmlspecialchars($u_d['Username']); ?>" class="edit-name">
                        <button class="btn-save">Save</button>
                        <button class="btn-cancel">Cancel</button>
                    </div>
                    <p class="user-email"><?php echo htmlspecialchars($u_d['Email']); ?></p>
                    
                    <div class="profile-stats">
                        <span><strong><?php echo $posts; ?></strong> Posts</span>
                        <span><strong><?php echo $followers; ?></strong> Followers</span>
                        <span><strong><?php echo $following; ?></strong> Following</span>
                    </div>
                </div>
            </div>

            <textarea class="bio-text" placeholder="Tell us about yourself...">Hey there! I am using LocalConnect 🎉</textarea>
            
            <div class="location-edit">
                <i class="fa-solid fa-location-dot"></i>
                <input type="text" value="Downtown">
                <input type="text" value="San Francisco">
            </div>

            <div class="interests">
                <button class="interest-tag">Music</button>
                <button class="interest-tag">Technology</button>
                <button class="interest-tag">Travel</button>
                <button class="add-interest">+ Add Interest</button>
            </div>

            <hr>

            <div class="profile-footer">
                <button class="btn_st"><i class="fa-solid fa-gear"></i> Settings</button>
                <button class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Log Out</button>
            </div>

            <div class="profile-tabs">
                <a href="#" class="tab active"><i class="fa-solid fa-table-cells"></i> Posts</a>
                <a href="#" class="tab"><i class="fa-solid fa-bookmark"></i> Saved</a>
            </div>
        </div>
        </div>       
    </div>

    <script src="../../../public/js/Profile/editProfile.js"></script>
</body>
</html>