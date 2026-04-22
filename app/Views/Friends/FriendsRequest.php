<?php
session_start();
require_once '../../../config/db_config.php';
require_once '../../Models/Profile/ProfileModel.php';
require_once '../../Models/Friends/FriendModel.php';
require_once __DIR__ . '/../Component/dashboard_shell.php';

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
    $diff = max(0, $now - $time); // Prevent negative time difference
    
    if ($diff < 60) {
        return $diff == 0 ? 'Just now' : $diff . ' seconds ago';
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Socail7 Friend Requests</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(lc_public_asset('css/Home/dashboard.css')); ?>">
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

        .request-avatar {
            flex-shrink: 0; /* Prevent the avatar box from growing or shrinking */
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #e2e8f0;
            border: 2px solid #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden; /* Lock to circular shape regardless of content overflow */
        }

        .request-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .request-info {
            flex: 1;
            min-width: 0; /* Prevent long text from overflowing the container */
            overflow: hidden;
        }

        .request-name {
            font-weight: 700;
            font-size: 1rem;
            color: #1e293b;
            margin-bottom: 5px;
            overflow-wrap: anywhere; /* Allow clean line breaks for long names or emails */
            line-height: 1.3;
        }

        .request-bio {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 8px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
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

        @media (max-width: 768px) {
            .friends-tabs {
                display: flex;
                flex-wrap: wrap;
                width: 100%;
            }
            .tab-btn {
                flex: 1;
                text-align: center;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <?php lc_shell_render_sidebar(['active' => 'friends']); ?>

    <div class="main-content">
        <?php
            ob_start();
        ?>
            <i class="fa-solid fa-bell"></i>
            <?php if ($unread_notifications > 0): ?>
                <span class="notification-badge"><?php echo $unread_notifications > 99 ? '99+' : $unread_notifications; ?></span>
            <?php endif; ?>
            <div class="notifications-dropdown" id="notificationsDropdown">
                <div class="notification-header" style="padding: 12px 16px; font-weight: 600; border-bottom: 1px solid #e5e7eb; text-align: left;">Notifications</div>
                <div id="notificationsList" class="notification-list"></div>
            </div>
        <?php
            $friendRequestNotificationHtml = ob_get_clean();
            lc_shell_render_header([
                'search_input_id' => 'searchRequests',
                'search_placeholder' => 'Search requests...',
                'notification_html' => $friendRequestNotificationHtml,
                'notification_button_id' => 'notificationBtn',
                'user_name' => (string)$user_data['Username'],
                'user_name_class' => 'user-name',
                'profile_image' => $profilePicPath,
            ]);
        ?>

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
                                <div class="request-card" data-request-id="<?php echo $request['UserID']; ?>" data-name="<?php echo strtolower(htmlspecialchars($request['Username'])); ?>">
                                    <div class="request-avatar">
                                        <img src="<?php 
                                            $path = '../../../public/img/' . $request['ProfilePic'];
                                            echo (!empty($request['ProfilePic']) && file_exists($path)) ? $path : 'https://ui-avatars.com/api/?name=' . urlencode($request['Username']) . '&background=cbd5e1&color=1e293b';
                                        ?>" alt="<?php echo htmlspecialchars($request['Username']); ?>" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($request['Username']); ?>&background=cbd5e1&color=1e293b';">
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
                                <div class="request-card sent" data-name="<?php echo strtolower(htmlspecialchars($request['Username'])); ?>">
                                    <div class="request-avatar">
                                        <img src="<?php 
                                            $path = '../../../public/img/' . $request['ProfilePic'];
                                            echo (!empty($request['ProfilePic']) && file_exists($path)) ? $path : 'https://ui-avatars.com/api/?name=' . urlencode($request['Username']) . '&background=cbd5e1&color=1e293b';
                                        ?>" alt="<?php echo htmlspecialchars($request['Username']); ?>" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($request['Username']); ?>&background=cbd5e1&color=1e293b';">
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

    <?php lc_shell_render_overlay(); ?>
    <div id="toast" class="toast"></div>
    <?php require_once __DIR__ . '/../Component/create_post_modal.php'; ?>
    <script src="../../../public/js/common/theme-toggle.js"></script>
    <script>
        // Search requests functionality
        const searchInput = document.getElementById('searchRequests');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                const requestCards = document.querySelectorAll('.request-card');
                
                requestCards.forEach(card => {
                    const name = card.getAttribute('data-name') || '';
                    if (name.includes(searchTerm)) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') e.preventDefault();
            });
        }

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
    <?php lc_shell_render_user_bootstrap((int)($_SESSION['user_id'] ?? 0), (string)($user_data['Username'] ?? 'User'), $profilePicPath ?? 'https://via.placeholder.com/150'); ?>
    <script src="<?php echo htmlspecialchars(lc_public_asset('js/Home/dashboard.js')); ?>"></script>
    <script src="<?php echo htmlspecialchars(lc_public_asset('js/Home/responsive.js')); ?>"></script>
</body>
</html>
