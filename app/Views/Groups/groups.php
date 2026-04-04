<?php
// app/Views/Groups/groups.php

session_start();

require_once dirname(__DIR__, 3) . '/config/db_config.php';
require_once dirname(__DIR__, 2) . '/Controllers/Groups/GroupController.php';
require_once __DIR__ . '/../Component/dashboard_shell.php';

// Initialize controller and get data
$controller = new GroupController($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $result = ['success' => false, 'message' => 'Invalid action'];

    if ($action === 'update_group') {
        $result = $controller->updateGroup();
    } elseif ($action === 'delete_group') {
        $result = $controller->deleteGroup();
    }

    $query = $result['success'] ? 'success=' . urlencode($action) : 'error=' . urlencode($result['message']);
    header('Location: groups.php?' . $query);
    exit();
}

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
} elseif (isset($_GET['success']) && $_GET['success'] === 'update_group') {
    $successMessage = 'Group updated successfully!';
} elseif (isset($_GET['success']) && $_GET['success'] === 'delete_group') {
    $successMessage = 'Group deleted successfully!';
}
$errorMessage = isset($_GET['error']) ? (string)$_GET['error'] : '';

$defaultAvatar = 'https://ui-avatars.com/api/?background=6366f1&color=fff&bold=true&size=100&length=2';
$defaultBanner = '../../../public/img/groups/banners/default_banner.jpg';
$u_d = $userData;
$profilePicPath = '';
if (!empty($u_d['ProfilePic'])) {
    $candidate = __DIR__ . '/../../../public/img/' . $u_d['ProfilePic'];
    if (is_file($candidate)) {
        $profilePicPath = '../../../public/img/' . $u_d['ProfilePic'];
    }
}
if ($profilePicPath === '') {
    $profilePicPath = 'https://via.placeholder.com/150';
}
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
    <link rel="stylesheet" href="<?php echo htmlspecialchars(lc_public_asset('css/Home/dashboard.css')); ?>">
    <link rel="stylesheet" href="../../../public/css/Home/newPost.css">
</head>
<body>
    <?php lc_shell_render_sidebar(['active' => 'groups']); ?>
    
    <div class="main-content">
        <?php
            ob_start();
        ?>
            <form method="GET" action="" id="searchForm" style="flex: 1;">
                <input type="text" name="search" placeholder="Search for groups..."
                    value="<?php echo htmlspecialchars($searchKeyword); ?>"
                    style="width: 100%; border: none; outline: none; background: transparent;">
            </form>
        <?php
            $groupsSearchHtml = ob_get_clean();
            ob_start();
        ?>
            <i class="fa-solid fa-bell"></i>
            <span class="app-shell-notification-dot"></span>
        <?php
            $groupsNotificationHtml = ob_get_clean();
            lc_shell_render_header([
                'search_html' => $groupsSearchHtml,
                'notification_html' => $groupsNotificationHtml,
                'user_name' => (string)$userData['Username'],
                'profile_image' => $profilePicPath,
            ]);
        ?>
        
        <div class="page-header">
            <div>
                <h1>Community Groups</h1>
                <p>Discover and join groups that match your interests.</p>
            </div>
        </div>
        <?php if ($successMessage !== ''): ?>
            <div style="margin: 0 0 12px; padding: 10px 12px; border-radius: 10px; background: #065f46; color: #fff;">
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>
        <?php if ($errorMessage !== ''): ?>
            <div style="margin: 0 0 12px; padding: 10px 12px; border-radius: 10px; background: #991b1b; color: #fff;">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <div class="content-body">
            <div class="groups-layout">
                <div class="groups-main-content">
                    <div class="create-post-card js-open-create-post" data-open-create-post>
                        <div class="create-post-header">
                            <img src="<?php 
                                $path = '../../../public/img/' . ($userData['ProfilePic'] ?? '');
                                if (!empty($userData['ProfilePic']) && file_exists($path)) {
                                    echo $path;
                                } else {
                                    echo 'https://ui-avatars.com/api/?background=6366f1&color=fff&name=' . urlencode($userData['Username']);
                                }
                            ?>" alt="Profile">
                            <div class="fake-input">What's on your mind?</div>
                        </div>
                        <div class="create-post-actions">
                            <div class="action-btn photo"><i class="fa-solid fa-photo-film"></i> Photo</div>
                            <div class="action-btn video"><i class="fa-solid fa-video"></i> Video</div>
                            <div class="action-btn event"><i class="fa-solid fa-calendar-days"></i> Event</div>
                        </div>
                    </div>
                    
                    <div class="groups-grid" id="groupsGrid">
                        <?php if (empty($groups)): ?>
                            <div class="no-groups" style="grid-column: 1/-1; text-align: center; padding: 60px;">
                                <i class="fa-solid fa-users-slash" style="font-size: 64px; color: var(--text-muted); margin-bottom: 20px;"></i>
                                <h3>No groups found</h3>
                                <p style="color: var(--text-muted);">Try adjusting your search or filter</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($groups as $group): 
                                $memberCount = $group['member_count'];
                                $isMember = $group['is_member'] > 0;
                                $isOwner = ((int)($group['CreatedBy'] ?? 0) === (int)($_SESSION['user_id'] ?? 0));
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
                                        <?php if ($isOwner): ?>
                                            <div style="display:flex; gap:8px; margin-top:10px;">
                                                <button type="button"
                                                        class="btn-join"
                                                        style="background:#1d4ed8;color:#fff;"
                                                        onclick='openGroupEdit(<?php echo (int)$group['GroupID']; ?>, <?php echo json_encode((string)$group['GroupName']); ?>, <?php echo json_encode((string)($group['GroupDescription'] ?? '')); ?>, <?php echo json_encode((string)$group['Privacy']); ?>, <?php echo json_encode((string)($group['Category'] ?? 'Other')); ?>)'>
                                                    Edit
                                                </button>
                                                <form action="groups.php" method="POST" onsubmit="return confirm('Delete this group?');">
                                                    <input type="hidden" name="action" value="delete_group">
                                                    <input type="hidden" name="group_id" value="<?php echo (int)$group['GroupID']; ?>">
                                                    <button type="submit" class="btn-join" style="background:#ef4444;color:#fff;">Delete</button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="groups-sidebar">
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
                    
                    <div class="group-filters-container">
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
            </div>
        </div>
    </div>
    
    <?php lc_shell_render_overlay(); ?>

    <?php require_once __DIR__ . '/../Component/create_post_modal.php'; ?>
    <form id="group-edit-form" action="groups.php" method="POST" style="display:none;">
        <input type="hidden" name="action" value="update_group">
        <input type="hidden" name="group_id" id="edit-group-id">
        <input type="hidden" name="group_name" id="edit-group-name">
        <input type="hidden" name="description" id="edit-group-description">
        <input type="hidden" name="privacy" id="edit-group-privacy">
        <input type="hidden" name="category" id="edit-group-category">
    </form>

    <script src="../../../public/js/Groups/groups.js"></script>
    <script src="../../../public/js/common/theme-toggle.js"></script>
    <script>
        function openGroupEdit(groupId, groupName, description, privacy, category) {
            const nameVal = prompt('Group name:', groupName || '');
            if (nameVal === null || nameVal.trim() === '') return;
            const descVal = prompt('Description:', description || '') ?? '';
            const privacyVal = confirm('Set group as Private?\\nPress OK = Private, Cancel = Public') ? 'Private' : 'Public';
            const categoryVal = prompt('Category:', category || 'Other') || 'Other';

            document.getElementById('edit-group-id').value = String(groupId || 0);
            document.getElementById('edit-group-name').value = nameVal.trim();
            document.getElementById('edit-group-description').value = descVal.trim();
            document.getElementById('edit-group-privacy').value = privacyVal;
            document.getElementById('edit-group-category').value = categoryVal.trim();
            document.getElementById('group-edit-form').submit();
        }
    </script>
    <?php lc_shell_render_user_bootstrap((int)($_SESSION['user_id'] ?? 0), (string)($u_d['Username'] ?? 'User'), $profilePicPath); ?>
    <script src="<?php echo htmlspecialchars(lc_public_asset('js/Home/dashboard.js')); ?>"></script>
    <script src="<?php echo htmlspecialchars(lc_public_asset('js/Home/responsive.js')); ?>"></script>
</body>
</html>
