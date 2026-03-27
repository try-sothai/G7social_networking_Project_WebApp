<?php
// app/Views/Groups/groups.php

session_start();

require_once dirname(__DIR__, 3) . '/config/db_config.php';
require_once dirname(__DIR__, 2) . '/Controllers/Groups/GroupController.php';

// Initialize controller and get data
$controller = new GroupController($conn);
$data = $controller->index();

// Extract data for view
$groups = $data['groups'];
$stats = $data['stats'];
$userData = $data['userData'];
$currentFilter = $data['currentFilter'];
$searchKeyword = $data['searchKeyword'];

// Show flash feedback from create_group redirect
$successMessage = '';
if (isset($_GET['success']) && $_GET['success'] === 'created') {
    $successMessage = 'Group created successfully!';
}

$defaultAvatar = 'https://ui-avatars.com/api/?background=6366f1&color=fff&bold=true&size=100&length=2';
$defaultBanner = '../../../public/img/groups/banners/default_banner.jpg';
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Groups - LocalConnect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
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
            <a href="../Home/dashboard.php" class="item"><i class="fa-solid fa-home"></i> Home</a>
            <a href="../Explore/explore.php" class="item"><i class="fa-solid fa-compass"></i> Explore</a>
            <a href="../Groups/groups.php" class="item active"><i class="fa-solid fa-people-group"></i> Groups</a>
            <a href="../Events/events.php" class="item"><i class="fa-solid fa-calendar-days"></i> Events</a>
            <a href="../Friends/AddFriends.php" class="item"><i class="fa-solid fa-user-friends"></i> Friends</a>
            <a href="../Messages/message.php" class="item"><i class="fa-solid fa-message"></i> Messages</a> 
            <a href="../Report/userReport.php" class="item"><i class="fa-solid fa-chart-simple"></i> Report</a>          
        </nav>
        <button class="btn-create" onclick="window.location.href='create_group.php'">
            <i class="fa-solid fa-plus-circle"></i>
            <span>Create New Group</span>
        </button>
    </div>
    
    <div class="main-content">
        <header>
            <div class="search-bar">
                <i class="fa-solid fa-search text-muted"></i>
                <form method="GET" action="" id="searchForm" style="flex: 1;">
                    <input type="text" name="search" placeholder="Search for groups..." 
                           value="<?php echo htmlspecialchars($searchKeyword); ?>"
                           style="width: 100%; border: none; outline: none; background: transparent;">
                </form>
            </div>
            
            <div class="header-actions">
                <div class="icon-btn theme-toggle"><i class="fa-solid fa-moon"></i></div>
                <div class="icon-btn" style="position: relative;">
                    <i class="fa-solid fa-bell"></i>
                    <span style="position: absolute; top: 12px; right: 12px; width: 8px; height: 8px; background: #ef4444; border-radius: 50%;"></span>
                </div>
                <div class="user-profile">
                    <span style="font-weight: 700;"><?php echo htmlspecialchars($userData['Username']); ?></span>
                    <a href="../Profile/profile.php">
                        <img src="<?php 
                            $path = '../../../public/img/' . ($userData['ProfilePic'] ?? '');
                            if (!empty($userData['ProfilePic']) && file_exists($path)) {
                                echo $path;
                            } else {
                                echo 'https://ui-avatars.com/api/?background=6366f1&color=fff&name=' . urlencode($userData['Username']);
                            }
                        ?>" alt="Profile">
                    </a>
                </div>
            </div>
        </header>
        
        <div class="content-body">                <?php if (!empty($successMessage)): ?>
                    <div class="success-alert" style="padding: 12px 16px; margin-bottom: 16px; border: 1px solid #10b981; color: #065f46; background: #dcfce7; border-radius: 8px;">
                        <?php echo htmlspecialchars($successMessage); ?>
                    </div>
                <?php endif; ?>
                        <div class="page-header">
                <div>
                    <h1>Community Groups</h1>
                    <p>Discover and join groups that match your interests.</p>
                </div>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <div class="group-filters">
                        <a href="?filter=all" class="filter-btn <?php echo $currentFilter == 'all' ? 'active' : ''; ?>">All Groups</a>
                        <a href="?filter=joined" class="filter-btn <?php echo $currentFilter == 'joined' ? 'active' : ''; ?>">Joined</a>
                        <a href="?filter=popular" class="filter-btn <?php echo $currentFilter == 'popular' ? 'active' : ''; ?>">Popular</a>
                    </div>
                    <button class="btn-create-group" onclick="window.location.href='create_group.php'">
                        <i class="fa-solid fa-plus"></i> Create Group
                    </button>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fa-solid fa-users" style="color: #6366f1;"></i>
                    <div>
                        <h3><?php echo number_format($stats['total_groups']); ?></h3>
                        <span>Active Groups</span>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fa-solid fa-bolt" style="color: #f59e0b;"></i>
                    <div>
                        <h3><?php echo number_format($stats['new_today']); ?></h3>
                        <span>New Today</span>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fa-solid fa-user-plus" style="color: #10b981;"></i>
                    <div>
                        <h3><?php echo number_format($stats['joined_groups']); ?></h3>
                        <span>Groups Joined</span>
                    </div>
                </div>
            </div>

            <div class="groups-grid" id="groupsGrid">
                <?php if (empty($groups)): ?>
                    <div class="no-groups" style="grid-column: 1/-1; text-align: center; padding: 60px;">
                        <i class="fa-solid fa-users-slash" style="font-size: 64px; color: var(--text-muted); margin-bottom: 20px;"></i>
                        <h3>No groups found</h3>
                        <p style="color: var(--text-muted);">Try adjusting your search or filter</p>
                        <?php if ($currentFilter == 'joined'): ?>
                            <button class="btn-create-group" onclick="window.location.href='?filter=all'">Browse All Groups</button>
                        <?php else: ?>
                            <button class="btn-create-group" onclick="window.location.href='create_group.php'">Create First Group</button>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($groups as $group): 
                        $memberCount = $group['member_count'];
                        $isMember = $group['is_member'] > 0;
                        $groupAvatar = !empty($group['GroupAvatar']) && file_exists('../../../public/img/groups/' . $group['GroupAvatar']) 
                            ? '../../../public/img/groups/' . $group['GroupAvatar'] 
                            : $defaultAvatar . '&name=' . urlencode(substr($group['GroupName'], 0, 2));
                        $groupBanner = !empty($group['GroupBanner']) && file_exists('../../../public/img/groups/banners/' . $group['GroupBanner'])
                            ? '../../../public/img/groups/banners/' . $group['GroupBanner']
                            : $defaultBanner;
                    ?>
                        <div class="group-card" data-group-id="<?php echo $group['GroupID']; ?>" data-is-member="<?php echo $isMember ? '1' : '0'; ?>">
                            <div class="group-banner" style="background-image: url('<?php echo $groupBanner; ?>');"></div>
                            <div class="group-info">
                                <img src="<?php echo $groupAvatar; ?>" alt="<?php echo htmlspecialchars($group['GroupName']); ?>" class="group-avatar">
                                <h4 class="group-name"><?php echo htmlspecialchars($group['GroupName']); ?></h4>
                                <p class="group-desc"><?php echo htmlspecialchars($group['GroupDescription'] ?? 'No description available.'); ?></p>
                                <div class="group-meta">
                                    <span><i class="fa-solid fa-user-group"></i> <?php echo number_format($memberCount); ?> Members</span>
                                    <span><i class="fa-solid <?php echo $group['Privacy'] == 'Public' ? 'fa-earth-americas' : 'fa-lock'; ?>"></i> <?php echo $group['Privacy']; ?></span>
                                </div>
                                <button class="btn-join <?php echo $isMember ? 'joined' : ''; ?>" 
                                        data-group-id="<?php echo $group['GroupID']; ?>"
                                        <?php echo $isMember ? 'disabled' : ''; ?>>
                                    <?php if ($isMember): ?>
                                        <i class="fa-solid fa-check"></i> Joined
                                    <?php else: ?>
                                        Join Group
                                    <?php endif; ?>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        src="../../../public/js/Groups/groups.js";
    </script>
</body>
</html>