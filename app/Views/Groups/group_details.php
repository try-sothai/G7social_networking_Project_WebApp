<?php
session_start();
require_once dirname(__DIR__, 3) . '/config/db_config.php';
require_once dirname(__DIR__, 2) . '/Controllers/Groups/GroupController.php';
require_once __DIR__ . '/../Component/dashboard_shell.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login_gmail.php");
    exit();
}

$controller = new GroupController($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'remove_member') {
        $controller->removeMember();
        exit();
    }
    if ($action === 'leave_group') {
        $controller->leaveGroup();
        exit();
    }
}

$user_id = $_SESSION['user_id'];
$group_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($group_id <= 0) {
    header("Location: groups.php");
    exit();
}

$group = $controller->getGroupById($group_id, $user_id);

if (!$group) {
    header("Location: groups.php?error=GroupNotFound");
    exit();
}

// ទាញយកសមាជិកទាំងអស់របស់គ្រុប
$members = $controller->getGroupMembers($group_id);
$isMember = $group['is_member'] > 0;
$isOwner = ((int)($group['CreatedBy'] ?? 0) === $user_id);

// ទាញយករូប Profile របស់ User បច្ចុប្បន្ន
require_once dirname(__DIR__, 2) . '/Models/Profile/ProfileModel.php';
$profileModel = new ProfileModel($conn);
$u_d = $profileModel->getUserData($user_id);
$profilePicPath = !empty($u_d['ProfilePic']) && file_exists(__DIR__ . '/../../../public/img/' . $u_d['ProfilePic']) 
    ? '../../../public/img/' . $u_d['ProfilePic'] 
    : 'https://via.placeholder.com/150';

// កំណត់រូប Group
$groupAvatar = !empty($group['GroupAvatar']) && file_exists('../../../public/img/groups/' . $group['GroupAvatar']) 
    ? '../../../public/img/groups/' . $group['GroupAvatar'] 
    : 'https://ui-avatars.com/api/?background=6366f1&color=fff&bold=true&size=100&name=' . urlencode(substr($group['GroupName'], 0, 2));
$groupBanner = !empty($group['GroupBanner']) && file_exists('../../../public/img/groups/banners/' . $group['GroupBanner'])
    ? '../../../public/img/groups/banners/' . $group['GroupBanner']
    : '../../../public/img/groups/banners/default_banner.jpg';
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($group['GroupName']); ?> - LocalConnect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(lc_public_asset('css/Home/dashboard.css')); ?>">
    <link rel="stylesheet" href="../../../public/css/Home/newPost.css">
    <style>
        .group-header-wrap { background: var(--bg-card, #fff); border-radius: 0 0 16px 16px; overflow: hidden; box-shadow: var(--card-shadow); margin-bottom: 20px; }
        .group-cover { height: 300px; background-size: cover; background-position: center; position: relative; }
        .group-header-info { padding: 20px 30px; display: flex; align-items: flex-end; gap: 20px; margin-top: -60px; }
        .group-dp { width: 120px; height: 120px; border-radius: 50%; border: 4px solid var(--bg-card, #fff); object-fit: cover; background: #fff; z-index: 2; }
        .group-text-info { flex: 1; z-index: 2; padding-bottom: 10px; }
        .group-text-info h1 { font-size: 28px; margin-bottom: 5px; color: var(--text-primary); }
        .group-meta-tags { font-size: 14px; color: var(--text-muted); display: flex; gap: 15px; align-items: center; }
        .group-actions-top { z-index: 2; padding-bottom: 10px; }
        .group-nav-tabs { display: flex; padding: 0 30px; border-top: 1px solid var(--border-color, #e5e7eb); }
        .g-tab { padding: 15px 20px; font-weight: 600; color: var(--text-muted); cursor: pointer; border-bottom: 3px solid transparent; transition: 0.3s; }
        .g-tab.active { color: var(--primary-color, #6366f1); border-bottom-color: var(--primary-color, #6366f1); }
        .g-tab:hover { background: rgba(0,0,0,0.02); }
        
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        .members-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-top: 20px; }
        .member-card { background: var(--bg-card, #fff); padding: 15px; border-radius: 12px; text-align: center; border: 1px solid var(--border-color, #e5e7eb); box-shadow: 0 2px 5px rgba(0,0,0,0.02); position: relative; }
        .member-card img { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin-bottom: 10px; }
        .member-role { font-size: 12px; background: #eef2ff; color: #4f46e5; padding: 4px 10px; border-radius: 12px; display: inline-block; margin-top: 5px; font-weight: bold; }
        .btn-remove-member { position: absolute; top: 10px; right: 10px; background: rgba(239, 68, 68, 0.1); color: #ef4444; border: none; width: 28px; height: 28px; border-radius: 50%; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; }
        .btn-remove-member:hover { background: #ef4444; color: #fff; }
        
        body.dark-mode .group-header-wrap { background: #1e293b; }
        body.dark-mode .group-dp { border-color: #1e293b; background: #1e293b; }
        body.dark-mode .member-card { background: #1e293b; border-color: rgba(148, 163, 184, 0.2); }
        body.dark-mode .group-nav-tabs { border-color: rgba(148, 163, 184, 0.2); }
        
        /* Responsive សម្រាប់ទូរស័ព្ទ */
        @media (max-width: 768px) {
            .group-header-info { flex-direction: column; align-items: center; text-align: center; margin-top: -60px; padding: 15px; }
            .group-meta-tags { justify-content: center; flex-wrap: wrap; margin-bottom: 10px; }
            .group-nav-tabs { justify-content: flex-start; overflow-x: auto; white-space: nowrap; padding: 0 10px; }
            .g-tab { padding: 12px 15px; font-size: 14px; }
        }
    </style>
</head>
<body>
    <?php lc_shell_render_sidebar(['active' => 'groups']); ?>
    <div class="main-content">
        <?php lc_shell_render_header(['user_name' => (string)$u_d['Username'], 'profile_image' => $profilePicPath]); ?>
        
        <div class="group-header-wrap">
            <div class="group-cover" style="background-image: url('<?php echo $groupBanner; ?>');"></div>
            <div class="group-header-info">
                <img src="<?php echo $groupAvatar; ?>" class="group-dp" alt="Group Avatar">
                <div class="group-text-info">
                    <h1><?php echo htmlspecialchars($group['GroupName']); ?></h1>
                    <div class="group-meta-tags">
                        <span><i class="fa-solid <?php echo $group['Privacy'] == 'Public' ? 'fa-earth-americas' : 'fa-lock'; ?>"></i> <?php echo $group['Privacy']; ?> group</span>
                        <span><i class="fa-solid fa-user-group"></i> <?php echo number_format($group['member_count']); ?> members</span>
                        <?php if ($isMember && !$isOwner): ?>
                            <button type="button" onclick="leaveGroup(<?php echo $group_id; ?>)" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); padding: 5px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; cursor: pointer; transition: 0.2s; margin-left: 10px;"><i class="fa-solid fa-arrow-right-from-bracket"></i> Leave Group</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="group-nav-tabs">
                <div class="g-tab active" onclick="switchTab('discussion')">Discussion</div>
                <div class="g-tab" onclick="switchTab('members')">Members</div>
                <div class="g-tab" onclick="switchTab('about')">About</div>
            </div>
        </div>

        <div class="group-body-container">
            <!-- Discussion / Feed Tab -->
            <div id="tab-discussion" class="tab-content active">
                <?php if ($group['Privacy'] == 'Private' && !$isMember && !$isOwner): ?>
                    <div class="post-card" style="text-align: center; padding: 40px;">
                        <i class="fa-solid fa-lock" style="font-size: 40px; color: #94a3b8; margin-bottom: 15px;"></i>
                        <h2>This group is Private</h2>
                        <p style="color: #64748b;">Join this group to view or participate in discussions.</p>
                    </div>
                <?php else: ?>
                    <div class="feed-grid" style="grid-template-columns: 1fr; max-width: 700px; margin: 0 auto;">
                        <div class="create-post-card js-open-create-post" data-open-create-post>
                            <div class="create-post-header">
                                <img src="<?php echo $profilePicPath; ?>" alt="Profile">
                                <div class="fake-input">Write something in this group...</div>
                            </div>
                        </div>
                        
                        <div id="post-feed-list">
                            <div style="text-align:center; padding: 20px; color: #64748b;"><i class="fa-solid fa-spinner fa-spin"></i> Loading posts...</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Members Tab -->
            <div id="tab-members" class="tab-content">
                <div class="post-card">
                    <h3 style="margin-bottom: 15px;">Members (<?php echo count($members); ?>)</h3>
                    <div class="members-grid">
                        <?php foreach($members as $member): 
                            $memPic = !empty($member['ProfilePic']) && file_exists('../../../public/img/' . $member['ProfilePic']) 
                                ? '../../../public/img/' . $member['ProfilePic'] 
                                : 'https://ui-avatars.com/api/?name=' . urlencode($member['Username']) . '&background=random';
                        ?>
                        <div class="member-card" id="member-<?php echo $member['UserID']; ?>">
                            <?php if ($isOwner && $member['UserID'] != $user_id): ?>
                            <button type="button" class="btn-remove-member" onclick="removeMember(<?php echo $group_id; ?>, <?php echo $member['UserID']; ?>)" title="Remove Member">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                            <?php endif; ?>
                            <img src="<?php echo $memPic; ?>" alt="<?php echo htmlspecialchars($member['Username']); ?>">
                            <h4><?php echo htmlspecialchars($member['Username']); ?></h4>
                            <span class="member-role"><i class="fa-solid <?php echo $member['Role'] == 'Admin' ? 'fa-crown' : 'fa-user'; ?>"></i> <?php echo $member['Role']; ?></span>
                            <div style="font-size: 11px; color: #94a3b8; margin-top: 8px;">Joined <?php echo date('M j, Y', strtotime($member['JoinedAt'])); ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- About Tab -->
            <div id="tab-about" class="tab-content">
                <div class="post-card">
                    <h3 style="margin-bottom: 15px;">About this group</h3>
                    <p style="line-height: 1.6; color: var(--text-secondary);"><?php echo nl2br(htmlspecialchars($group['GroupDescription'] ?? 'No description provided.')); ?></p>
                    
                    <div style="margin-top: 25px; display: flex; gap: 15px; flex-direction: column;">
                        <div><i class="fa-solid <?php echo $group['Privacy'] == 'Public' ? 'fa-earth-americas' : 'fa-lock'; ?>" style="width: 25px; color: #64748b;"></i> <strong><?php echo $group['Privacy']; ?></strong><br><small style="color: #64748b; margin-left: 25px;"><?php echo $group['Privacy'] == 'Public' ? 'Anyone can see who\'s in the group and what they post.' : 'Only members can see who\'s in the group and what they post.'; ?></small></div>
                        <div><i class="fa-solid fa-tag" style="width: 25px; color: #64748b;"></i> <strong>Category:</strong> <?php echo htmlspecialchars($group['Category'] ?? 'General'); ?></div>
                        <div><i class="fa-solid fa-clock" style="width: 25px; color: #64748b;"></i> <strong>Created on:</strong> <?php echo date('F j, Y', strtotime($group['CreatedAt'])); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php lc_shell_render_overlay(); ?>
    <?php require_once __DIR__ . '/../Component/create_post_modal.php'; ?>
    
    <script>
        // កូដសម្រាប់ចុចប្តូរ Tab ទៅមក (Feed, Members, About)
        function switchTab(tabName) {
            document.querySelectorAll('.g-tab').forEach(el => el.classList.remove('active'));
            event.currentTarget.classList.add('active');
            
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.getElementById('tab-' + tabName).classList.add('active');
        }

        // កូដសម្រាប់ Owner ដកសមាជិកចេញពីគ្រុប
        function removeMember(groupId, memberId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to remove this member from the group?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, remove!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', 'remove_member');
                    formData.append('group_id', groupId);
                    formData.append('member_id', memberId);

                    fetch('group_details.php?id=' + groupId, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({ icon: 'success', title: 'Removed!', text: data.message, showConfirmButton: false, timer: 1500 });
                            const memberCard = document.getElementById('member-' + memberId);
                            if (memberCard) {
                                memberCard.style.display = 'none';
                            }
                        } else {
                            Swal.fire('Error', data.message || 'Failed to remove member', 'error');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire('Error', 'Something went wrong', 'error');
                    });
                }
            });
        }

        // កូដសម្រាប់ Leave Group (ចាកចេញដោយខ្លួនឯង)
        function leaveGroup(groupId) {
            Swal.fire({
                title: 'Leave Group?',
                text: "Are you sure you want to leave this group?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, leave!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', 'leave_group');
                    formData.append('group_id', groupId);

                    fetch('group_details.php?id=' + groupId, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({ icon: 'success', title: 'Left!', text: data.message, showConfirmButton: false, timer: 1500 });
                            setTimeout(() => window.location.href = 'groups.php', 1500); // ត្រលប់ទៅទំព័រ Groups វិញ
                        } else {
                            Swal.fire('Error', data.message || 'Failed to leave group', 'error');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Error', 'Something went wrong', 'error');
                    });
                }
            });
        }
        
        window.CURRENT_USER_ID = <?php echo $user_id; ?>;
        window.CURRENT_USER_AVATAR = '<?php echo $profilePicPath; ?>';
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../../public/js/common/theme-toggle.js"></script>
    <script src="<?php echo htmlspecialchars(lc_public_asset('js/Home/dashboard.js')); ?>"></script>
    <script src="<?php echo htmlspecialchars(lc_public_asset('js/Home/responsive.js')); ?>"></script>
</body>
</html>