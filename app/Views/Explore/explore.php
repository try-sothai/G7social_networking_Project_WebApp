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
    <link rel="stylesheet" href="../../../public/css/explore/explore.css">
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
            <a href="explore.php" class="nav-item active"><i class="fa-solid fa-compass"></i> Explore</a>
            <a href="../Groups/groups.php" class="nav-item"><i class="fa-solid fa-people-group"></i> Groups</a>
            <a href="../Events/events.php" class="nav-item"><i class="fa-solid fa-calendar-days"></i> Events</a>
            <a href="../Friends/AllFriends.php" class="nav-item"><i class="fa-solid fa-user-friends"></i> Friends</a>
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

        <div class="explore-container">
            <h1 class="explore-title">Explore the World</h1>
            
            <div class="categories">
                <div class="chip active">All</div>
                <div class="chip">Photography</div>
                <div class="chip">Travel</div>
                <div class="chip">Architecture</div>
                <div class="chip">Nature</div>
                <div class="chip">Digital Art</div>
            </div>

            <div class="explore-grid">
                <div class="grid-item w-2 h-2">
                    <img src="https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=800&q=80" alt="Mountain">
                    <div class="item-overlay">
                        <h4>Mountain Adventure</h4>
                    </div>
                </div>

                <div class="grid-item h-2">
                    <img src="https://images.unsplash.com/photo-1513542789411-b6a5d4f31634?auto=format&fit=crop&w=400&q=80" alt="Art">
                    <div class="item-overlay">
                        <h4>Digital Creativity</h4>
                    </div>
                </div>

                <div class="grid-item">
                    <img src="https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?auto=format&fit=crop&w=400&q=80" alt="Night">
                    <div class="item-overlay">
                        <h4>City Night Life</h4>
                    </div>
                </div>

                <div class="grid-item">
                    <img src="https://images.unsplash.com/photo-1493246507139-91e8bef99c02?auto=format&fit=crop&w=400&q=80" alt="Nature">
                    <div class="item-overlay">
                        <h4>Pure Nature</h4>
                    </div>
                </div>

                <div class="grid-item w-2">
                    <img src="https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=800&q=80" alt="Lake">
                    <div class="item-overlay">
                        <h4>Peaceful Lake</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
   
</body>
</html>