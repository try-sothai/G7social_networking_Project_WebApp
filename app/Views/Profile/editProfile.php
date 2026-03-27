<?php
session_start();
require_once '../../../config/db_config.php';
require_once '../../Models/Profile/editProfileModel.php'; 
// require_once '../../Controllers/Profile/editProfileController.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login_gmail.php");
    exit();
}

$model = new editProfileModel($conn);
$user_id = $_SESSION['user_id'];
$u_d = $model->getUserData($user_id);
$stats = $model->getProfileStats($user_id);
$username = $u_d['Username'];
$email = $u_d['Email'];
$bio = $u_d['Bio'];
$profile_pic = $u_d['ProfilePic'];
$posts = $stats['posts'];
$followers = $stats['followers'];
$following = $stats['following'];
$interests = $model->getInterests($user_id);
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
                    <a href="profile.php">
                        <img src="<?php 
                            if (!empty($u_d['ProfilePic']) && file_exists('../../../public/img/' . $u_d['ProfilePic'])) {
                                echo '../../../public/img/' . $u_d['ProfilePic'];
                            } else {
                               echo 'https://via.placeholder.com/150';
                            }
                        ?>" alt="Profile">
                    </a>
                </div>
            </div>
        </header>
        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-avatar">
                    <img src="<?php 
                            if (!empty($u_d['ProfilePic']) && file_exists('../../../public/img/' . $u_d['ProfilePic'])) {
                                echo '../../../public/img/' . $u_d['ProfilePic'];
                            } else {
                                echo 'https://via.placeholder.com/150';
                            }
                        ?>" alt="Profile">
                    <label for="upload-photo" class="change-photo" style="cursor:pointer;">Change Photo</label>
                    <input type="file" id="upload-photo" style="display:none;" accept="image/*">
                </div>
                <div class="profile-info">
                    <div class="profile-actions">
                        <input type="text" value="<?php echo htmlspecialchars($u_d['Username']); ?>" class="edit-name">
                        <button class="btn-save">Save</button>
                        <a href="profile.php"><button class="btn-cancel">Cancel</button></a>
                    </div>
                    <p class="user-email"><?php echo htmlspecialchars($u_d['Email']); ?></p>
                    
                    <div class="profile-stats">
                        <span><strong><?php echo $posts; ?></strong> Posts</span>
                        <span><strong><?php echo $followers; ?></strong> Followers</span>
                        <span><strong><?php echo $following; ?></strong> Following</span>
                    </div>
                </div>
            </div>

            <textarea class="bio-text" placeholder="Tell us about yourself..."><?php echo htmlspecialchars($bio); ?></textarea>
            
            <div class="location-edit">
                <i class="fa-solid fa-location-dot"></i>
                <input type="text" id="location-input" value="<?php echo htmlspecialchars($u_d['Locations'] ?? ''); ?>" placeholder="Your location...">
            </div>

            <div class="interests">
                <?php foreach ($interests as $interest): ?>
                    <button class="interest-tag">
                        <?php echo htmlspecialchars($interest); ?>
                        <span class="remove-interest">x</span>
                    </button>
                <?php endforeach; ?>
                <input type="text" id="interest-input" value="<?php echo htmlspecialchars($u_d['InterestName'] ?? ''); ?>" placeholder="Add an interest" >
                <button class="add-interest">+ Add</button>
            </div>
            <br>
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
    <script src="../../../public/js/common/theme-toggle.js"></script>
</body>
</html>

