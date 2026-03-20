<?php
    session_start();

    require_once '../../../config/db_config.php';

    require_once '../../Models/Profile/ProfileModel.php';
    // សុវត្ថិភាព
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../../Login/login_gmail.php");
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
    <link rel="stylesheet" href="../../../public/css/Groups/groups.css">
    <link rel="stylesheet" href="../../../public/css/Home/dashboard.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo-section">
            <i class="fa-solid fa-cube"></i>
            <span>LocalConnect</span>
        </div>      
        <nav class="menu">
            <a href="../Home/dashboard.php" class="item "><i class="fa-solid fa-home"></i> Home</a>
            <a href="../Explore/explore.php" class="item"><i class="fa-solid fa-compass"></i> Explore</a>
            <a href="../Groups/groups.php" class="item active"><i class="fa-solid fa-people-group"></i> Groups</a>
            <a href="../Events/events.php" class="item"><i class="fa-solid fa-calendar-days"></i> Events</a>
            <a href="../Friends/AddFriends.php" class="item"><i class="fa-solid fa-user-friends"></i> Friends</a>
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
                            $path = '../../../public/img/' . $u_d['ProfilePic'];
                            if (!empty($u_d['ProfilePic']) && file_exists($path)) {
                                echo $path;
                            } else {
                                echo 'https://via.placeholder.com/50'; // ដាក់រូប default បើអត់មានរូបពិត
                            }
                        ?>" alt="Profile">
                    </a>
                </div>
            </div>
        </header>
            <div class="content-body">
    <div class="page-header">
        <div>
            <h1 style="font-size: 28px; margin-bottom: 8px;">Community Groups</h1>
            <p style="color: var(--text-muted);">Discover and join groups that match your interests.</p>
        </div>
        <div class="group-filters">
            <button class="filter-btn active">All Groups</button>
            <button class="filter-btn">Joined</button>
            <button class="filter-btn">Popular</button>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <i class="fa-solid fa-users" style="color: #6366f1;"></i>
            <div>
                <h3>1.2k</h3>
                <span>Active Groups</span>
            </div>
        </div>
        <div class="stat-card">
            <i class="fa-solid fa-bolt" style="color: #f59e0b;"></i>
            <div>
                <h3>458</h3>
                <span>New Today</span>
            </div>
        </div>
    </div>

    <div class="groups-grid">
        <div class="group-card">
            <div class="group-banner" style="background-image: url('https://images.unsplash.com/photo-1517048676732-d65bc937f952?w=500&auto=format&fit=crop&q=60');"></div>
            <div class="group-info">
                <img src="https://i.pravatar.cc/150?u=tech" alt="Group Icon" class="group-avatar">
                <h4 class="group-name">Tech Innovators KH</h4>
                <p class="group-desc">Sharing the latest technology and coding trends in Cambodia.</p>
                <div class="group-meta">
                    <span><i class="fa-solid fa-user-group"></i> 2.5k Members</span>
                    <span><i class="fa-solid fa-earth-americas"></i> Public</span>
                </div>
                <button class="btn-join">Join Group</button>
            </div>
        </div>

        <div class="group-card">
            <div class="group-banner" style="background-image: url('https://images.unsplash.com/photo-1511739001486-6bfe10ce785f?w=500&auto=format&fit=crop&q=60');"></div>
            <div class="group-info">
                <img src="https://i.pravatar.cc/150?u=travel" alt="Group Icon" class="group-avatar">
                <h4 class="group-name">Cambodia Travelers</h4>
                <p class="group-desc">Explore hidden gems and beautiful places across the kingdom.</p>
                <div class="group-meta">
                    <span><i class="fa-solid fa-user-group"></i> 8.1k Members</span>
                    <span><i class="fa-solid fa-lock"></i> Private</span>
                </div>
                <button class="btn-join joined">Joined</button>
            </div>
        </div>

        <div class="group-card">
            <div class="group-banner" style="background-image: url('https://images.unsplash.com/photo-1552664730-d307ca884978?w=500&auto=format&fit=crop&q=60');"></div>
            <div class="group-info">
                <img src="https://i.pravatar.cc/150?u=design" alt="Group Icon" class="group-avatar">
                <h4 class="group-name">Creative Designers</h4>
                <p class="group-desc">A space for UI/UX and Graphic Designers to share their work.</p>
                <div class="group-meta">
                    <span><i class="fa-solid fa-user-group"></i> 1.2k Members</span>
                    <span><i class="fa-solid fa-earth-americas"></i> Public</span>
                </div>
                <button class="btn-join">Join Group</button>
            </div>
        </div>
    </div>
</div>
</div>
</div>
</body>
</html>