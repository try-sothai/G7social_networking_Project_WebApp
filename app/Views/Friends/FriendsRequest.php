<?php
session_start();
require_once '../../../config/db_config.php';
require_once '../../Models/Profile/ProfileModel.php';
require_once '../../Models/Friends/FriendModel.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login_gmail.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$profileModel = new ProfileModel($conn);
$friendModel = new FriendModel($conn);

$user_data = $profileModel->getUserData($user_id);
$u_d = $user_data;
$received_requests = $friendModel->getReceivedRequests($user_id);
$sent_requests = $friendModel->getSentRequests($user_id);
$unread_notifications = $friendModel->getUnreadNotificationsCount($user_id);

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

function time_ago($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return $diff . ' seconds ago';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 2592000) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $time);
    }
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LocalConnect - Friend Requests</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/css/Home/dashboard.css">
    <style>
        /* Friends Section */
        .friends-container {
            margin-top: 20px;
        }

        .friends-tabs {
            background: #e2e8f0;
            padding: 8px;
            border-radius: 16px;
            display: inline-flex;
            gap: 10px;
            width: fit-content;
            margin-bottom: 20px;
        }

        .tab-btn {
            padding: 12px 25px;
            border: none;
            border-radius: 12px;
            background: transparent;
            font-weight: 600;
            color: #1e293b;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .tab-btn.active {
            background: #2563eb;
            color: white;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .tab-btn:not(.active):hover {
            background: rgba(37, 99, 235, 0.1);
        }

        .friends-card {
            background: white;
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        /* Request specific styles */
        .requests-section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .badge {
            background: #ef4444;
            color: white;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .requests-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .request-card {
            background: #f8fafc;
            border-radius: 16px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: 0.3s;
            border: 1px solid #e2e8f0;
        }

        .request-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .request-avatar img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .request-info {
            flex: 1;
        }

        .request-name {
            font-weight: 700;
            font-size: 1rem;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .request-bio {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .request-time {
            font-size: 0.75rem;
            color: #64748b;
        }

        .request-actions {
            display: flex;
            flex-direction: column;
            gap: 8px;
            width: 120px;
        }

        .btn-accept, .btn-reject {
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            justify-content: center;
            width: 100%;
        }

        .btn-accept {
            background: #10b981;
            color: white;
        }

        .btn-accept:hover {
            background: #059669;
        }

        .btn-reject {
            background: #ef4444;
            color: white;
        }

        .btn-reject:hover {
            background: #dc2626;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.2rem;
            margin-bottom: 8px;
            color: #1e293b;
        }

        .empty-state p {
            font-size: 0.9rem;
        }

        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 12px 24px;
            border-radius: 8px;
            color: white;
            display: none;
            z-index: 1000;
            animation: slideIn 0.3s ease;
        }

        .toast.success {
            background: #10b981;
        }

        .toast.error {
            background: #ef4444;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    
        /* Dark mode */
        body.dark-mode .friends-tabs {
            background: #1e293b;
        }

        body.dark-mode .friends-card,
        body.dark-mode .left-content,
        body.dark-mode .right-content,
        body.dark-mode .friend-card,
        body.dark-mode .friend-profile,
        body.dark-mode .search-results,
        body.dark-mode .suggestions-section,
        body.dark-mode .requests-section,
        body.dark-mode .empty-state,
        body.dark-mode .request-card {
            background: #111827 !important;
            color: #e2e8f0 !important;
            border: 1px solid rgba(148, 163, 184, 0.22);
        }

        body.dark-mode .tab-btn {
            color: #cbd5e1;
        }

        body.dark-mode .tab-btn:not(.active):hover {
            background: #334155;
        }

        body.dark-mode .search-box input,
        body.dark-mode #searchInput {
            background: #0f172a;
            color: #e2e8f0;
            border-color: rgba(148, 163, 184, 0.3);
        }

        body.dark-mode .section-title,
        body.dark-mode .friend-name,
        body.dark-mode h2,
        body.dark-mode h3,
        body.dark-mode h4 {
            color: #e2e8f0 !important;
        }

        body.dark-mode .friend-location,
        body.dark-mode .friend-bio,
        body.dark-mode .empty-state p,
        body.dark-mode p,
        body.dark-mode small {
            color: #94a3b8 !important;
        }
    </style>
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
            <a href="../Friends/AllFriends.php" class="item active"><i class="fa-solid fa-user-friends"></i> Friends</a>
            <a href="../Messages/message.php" class="item"><i class="fa-solid fa-message"></i> Messages</a>
            <a href="../Report/userReport.php" class="item"><i class="fa-solid fa-chart-simple"></i> Report</a>     
        </nav>
        <button class="btn-create" id="open-create-post" type="button">
            <i class="fa-solid fa-plus-circle"></i>
            <span>Create New Post</span>
        </button>
    </div>

    <div class="main-content">
        <header>
            <div class="search-bar">
                <i class="fa-solid fa-search"></i>
                <input type="text" placeholder="Search for anything...">
            </div>
            <div class="header-actions">
                <div class="icon-btn theme-toggle"><i class="fa-solid fa-moon"></i></div>
                <div class="icon-btn notifications-btn" style="position: relative;">
                    <i class="fa-solid fa-bell"></i>
                    <?php if ($unread_notifications > 0): ?>
                        <span style="position: absolute; top: -5px; right: -5px; width: 8px; height: 8px; background: #ef4444; border-radius: 50%;"></span>
                    <?php endif; ?>
                </div>
                <div class="user-profile">
                    <strong class="user-name"><?php echo htmlspecialchars($user_data['Username']); ?></strong>
                    <a href="../Profile/profile.php">
                        <img src="<?php 
                            $path = '../../../public/img/' . $user_data['ProfilePic'];
                            echo (!empty($user_data['ProfilePic']) && file_exists($path)) ? $path : 'https://via.placeholder.com/50';
                        ?>" alt="Profile">
                    </a>
                </div>
            </div>
        </header>

        <div class="friends-container">
            <div class="friends-tabs">
                <a href="AllFriends.php" class="tab-btn">All Friends</a>
                <a href="AddFriends.php" class="tab-btn">Add Friends</a>
                <a href="FriendsRequest.php" class="tab-btn active">Friend Request</a>
            </div>

            <div class="friends-card">
                <div class="friends-content">
                <!-- Received Requests -->
                <div class="requests-section">
                    <h2 class="section-title">
                        <i class="fa-solid fa-inbox"></i>
                        Received Requests 
                        <?php if (count($received_requests) > 0): ?>
                            <span class="badge"><?php echo count($received_requests); ?></span>
                        <?php endif; ?>
                    </h2>
                    
                    <?php if (empty($received_requests)): ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-envelope-open-text"></i>
                            <h3>No pending requests</h3>
                            <p>When someone sends you a friend request, it will appear here.</p>
                        </div>
                    <?php else: ?>
                        <div class="requests-grid">
                            <?php foreach ($received_requests as $request): ?>
                                <div class="request-card" data-request-id="<?php echo $request['UserID']; ?>">
                                    <div class="request-avatar">
                                        <img src="<?php 
                                            $path = '../../../public/img/' . $request['ProfilePic'];
                                            echo (!empty($request['ProfilePic']) && file_exists($path)) ? $path : 'https://via.placeholder.com/80';
                                        ?>" alt="<?php echo htmlspecialchars($request['Username']); ?>">
                                    </div>
                                    <div class="request-info">
                                        <h3 class="request-name"><?php echo htmlspecialchars($request['Username']); ?></h3>
                                        <p class="request-bio"><?php echo htmlspecialchars($request['Bio'] ?? 'No bio yet'); ?></p>
                                        <p class="request-time">
                                            <i class="fa-regular fa-clock"></i> 
                                            Sent <?php echo time_ago($request['request_sent_at']); ?>
                                        </p>
                                    </div>
                                    <div class="request-actions">
                                        <button class="btn-accept" onclick="handleRequest(<?php echo $request['UserID']; ?>, 'accept')">
                                            <i class="fa-solid fa-check"></i> Accept
                                        </button>
                                        <button class="btn-reject" onclick="handleRequest(<?php echo $request['UserID']; ?>, 'reject')">
                                            <i class="fa-solid fa-times"></i> Reject
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Sent Requests -->
                <div class="requests-section sent-requests">
                    <h2 class="section-title">
                        <i class="fa-solid fa-paper-plane"></i>
                        Sent Requests
                    </h2>
                    
                    <?php if (empty($sent_requests)): ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-user-plus"></i>
                            <h3>No sent requests</h3>
                            <p>Friend requests you've sent will appear here.</p>
                        </div>
                    <?php else: ?>
                        <div class="requests-grid">
                            <?php foreach ($sent_requests as $request): ?>
                                <div class="request-card sent">
                                    <div class="request-avatar">
                                        <img src="<?php 
                                            $path = '../../../public/img/' . $request['ProfilePic'];
                                            echo (!empty($request['ProfilePic']) && file_exists($path)) ? $path : 'https://via.placeholder.com/80';
                                        ?>" alt="<?php echo htmlspecialchars($request['Username']); ?>">
                                    </div>
                                    <div class="request-info">
                                        <h3 class="request-name"><?php echo htmlspecialchars($request['Username']); ?></h3>
                                        <p class="request-bio"><?php echo htmlspecialchars($request['Bio'] ?? 'No bio yet'); ?></p>
                                        <p class="request-time">
                                            <i class="fa-regular fa-clock"></i> 
                                            Sent <?php echo time_ago($request['request_sent_at']); ?>
                                        </p>
                                    </div>
                                    <div class="request-actions">
                                        <button class="btn-reject" onclick="cancelRequest(<?php echo $request['UserID']; ?>)">
                                            <i class="fa-solid fa-ban"></i> Cancel Request
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    </div>

    <div id="toast" class="toast"></div>
    <?php require_once __DIR__ . '/../Component/create_post_modal.php'; ?>
    <script src="../../../public/js/common/theme-toggle.js"></script>
    <script>
        function handleRequest(userId, action) {
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
            button.disabled = true;
            
            fetch('../../Controllers/Friends/friend_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=${action}_request&friend_id=${userId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showToast(data.message, 'error');
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            })
            .catch(error => {
                showToast('An error occurred', 'error');
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }
        
        function cancelRequest(userId) {
            if (confirm('Are you sure you want to cancel this friend request?')) {
                handleRequest(userId, 'reject');
            }
        }
        
        function showToast(message, type) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = `toast ${type}`;
            toast.style.display = 'block';
            
            setTimeout(() => {
                toast.style.display = 'none';
            }, 3000);
        }
    </script>
        <script>
        const CURRENT_USER_ID = <?php echo (int)($_SESSION['user_id'] ?? 0); ?>;
        const CURRENT_USER_NAME = <?php echo json_encode((string)($user_data['Username'] ?? 'User')); ?>;
        const CURRENT_USER_AVATAR = <?php echo json_encode($profilePicPath ?? 'https://via.placeholder.com/150'); ?>;
    </script>
    <script src="../../../public/js/Home/dashboard.js"></script>
</body>
</html>

