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
    <title>LocalConnect V2 - Friends Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/css/Friends/AddFriends.css">
    <link rel="stylesheet" href="../../../public/css/Home/dashboard.css">
    
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
            <a href="../Friends/AllFriends.php" class="nav-item active"><i class="fa-solid fa-user-friends"></i> Friends</a>
            <a href="../Messages/message.php" class="nav-item"><i class="fa-solid fa-message"></i> Messages</a>
            <a href="../Report/userReport.php" class="nav-item"><i class="fa-solid fa-chart-simple"></i> Report</a>     
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

        <div class="friends-container">
            <div class="friends-tabs">
                <a href="AllFriends.php"><button class="tab-btn">All Friends</button></a>
                <a href="AddFriends.php"><button class="tab-btn active">Add Friends</button></a>
                <a href="FriendsRequest.php">
                <button class="tab-btn ">Friend Request</button></a>
            </div>

            <div class="friends-card">
                <div class="friends-list">
                    <div class="friend-item">
                        <div class="friend-info">
                            <img src="https://images.unsplash.com/photo-1543946207-39bd91e70ca7?w=100" alt="profile">
                            <span class="friend-name">Titha</span>
                        </div>
                        <div class="friend-actions">
                            <button class="btn-add">AddFriend</button>
                            <button class="btn-remove">Remove</button>
                        </div>
                    </div>

                    <div class="friend-item">
                        <div class="friend-info">
                            <img src="https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=100" alt="profile">
                            <span class="friend-name">Yuri</span>
                        </div>
                        <div class="friend-actions">
                            <button class="btn-add">AddFriend</button>
                            <button class="btn-remove">Remove</button>
                        </div>
                    </div>

                    <div class="friend-item">
                        <div class="friend-info">
                            <img src="https://images.unsplash.com/photo-1519378018457-4c29a3a2ecdf?w=100" alt="profile">
                            <span class="friend-name">Hana</span>
                        </div>
                        <div class="friend-actions">
                            <button class="btn-add">AddFriend</button>
                            <button class="btn-remove">Remove</button>
                        </div>
                    </div>

                    <div class="friend-item">
                        <div class="friend-info">
                            <img src="https://images.unsplash.com/photo-1450778869180-41d0601e046e?w=100" alt="profile">
                            <span class="friend-name">Mara</span>
                        </div>
                        <div class="friend-actions">
                            <button class="btn-add">AddFriend</button>
                            <button class="btn-remove">Remove</button>
                        </div>
                    </div>
                </div>
            </div>
        </div> </div> </body>
</html>