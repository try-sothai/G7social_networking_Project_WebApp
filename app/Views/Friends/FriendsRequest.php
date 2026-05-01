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
    $candidate = __DIR__ . '/../../../public/uploads/avatars/' . $u_d['ProfilePic'];
    if (is_file($candidate)) {
        $profilePicPath = '../../../public/uploads/avatars/' . $u_d['ProfilePic'];
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
    <link rel="stylesheet" href="<?php echo htmlspecialchars(lc_public_asset('css/Friends/friends.css')); ?>">
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

        <div class="friends-page">
            <div class="friends-tabs">
                <a href="AllFriends.php" class="tab-btn"><i class="fa-solid fa-user-group"></i> All Friends</a>
                <a href="AddFriends.php" class="tab-btn"><i class="fa-solid fa-user-plus"></i> Add Friends</a>
                <a href="FriendsRequest.php" class="tab-btn active">
                    <i class="fa-solid fa-inbox"></i> Requests
                    <?php if (count($received_requests) > 0): ?>
                        <span class="tab-count"><?php echo count($received_requests); ?></span>
                    <?php endif; ?>
                </a>
            </div>

            <div class="friends-card">
                <!-- Received Requests -->
                <div class="sec-header">
                    <h2 class="sec-title">
                        <i class="fa-solid fa-inbox"></i>
                        Received Requests
                        <?php if (count($received_requests) > 0): ?>
                            <span class="sec-badge"><?php echo count($received_requests); ?></span>
                        <?php endif; ?>
                    </h2>
                </div>

                <?php if (empty($received_requests)): ?>
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fa-solid fa-envelope-open-text"></i></div>
                        <h3>No pending requests</h3>
                        <p>When someone sends you a friend request, it will appear here.</p>
                    </div>
                <?php else: ?>
                    <div class="requests-grid">
                        <?php foreach ($received_requests as $request): ?>
                            <div class="request-card" data-request-id="<?php echo $request['UserID']; ?>" data-name="<?php echo strtolower(htmlspecialchars($request['Username'])); ?>">
                                <div class="req-avatar">
                                    <img src="<?php
                                        $path = '../../../public/uploads/avatars/' . $request['ProfilePic'];
                                        echo (!empty($request['ProfilePic']) && file_exists($path)) ? $path : 'https://ui-avatars.com/api/?name=' . urlencode($request['Username']) . '&background=dbeafe&color=1d4ed8&bold=true';
                                    ?>" alt="<?php echo htmlspecialchars($request['Username']); ?>" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($request['Username']); ?>&background=dbeafe&color=1d4ed8&bold=true';">
                                </div>
                                <div class="req-info">
                                    <p class="req-name"><?php echo htmlspecialchars($request['Username']); ?></p>
                                    <p class="req-bio"><?php echo htmlspecialchars($request['Bio'] ?? 'No bio yet'); ?></p>
                                    <p class="req-time">
                                        <i class="fa-regular fa-clock"></i>
                                        <?php echo time_ago($request['request_sent_at']); ?>
                                    </p>
                                </div>
                                <div class="req-actions">
                                    <button class="btn-accept" onclick="handleRequest(<?php echo $request['UserID']; ?>, 'accept')">
                                        <i class="fa-solid fa-check"></i> Accept
                                    </button>
                                    <button class="btn-decline" onclick="handleRequest(<?php echo $request['UserID']; ?>, 'reject')">
                                        <i class="fa-solid fa-times"></i> Decline
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <hr class="sec-divider">

                <!-- Sent Requests -->
                <div class="sec-header">
                    <h2 class="sec-title">
                        <i class="fa-solid fa-paper-plane"></i>
                        Sent Requests
                    </h2>
                </div>

                <?php if (empty($sent_requests)): ?>
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fa-solid fa-paper-plane"></i></div>
                        <h3>No sent requests</h3>
                        <p>Friend requests you've sent will appear here.</p>
                    </div>
                <?php else: ?>
                    <div class="requests-grid">
                        <?php foreach ($sent_requests as $request): ?>
                            <div class="request-card" data-name="<?php echo strtolower(htmlspecialchars($request['Username'])); ?>">
                                <div class="req-avatar">
                                    <img src="<?php
                                        $path = '../../../public/uploads/avatars/' . $request['ProfilePic'];
                                        echo (!empty($request['ProfilePic']) && file_exists($path)) ? $path : 'https://ui-avatars.com/api/?name=' . urlencode($request['Username']) . '&background=dbeafe&color=1d4ed8&bold=true';
                                    ?>" alt="<?php echo htmlspecialchars($request['Username']); ?>" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($request['Username']); ?>&background=dbeafe&color=1d4ed8&bold=true';">
                                </div>
                                <div class="req-info">
                                    <p class="req-name"><?php echo htmlspecialchars($request['Username']); ?></p>
                                    <p class="req-bio"><?php echo htmlspecialchars($request['Bio'] ?? 'No bio yet'); ?></p>
                                    <p class="req-time">
                                        <i class="fa-regular fa-clock"></i>
                                        <?php echo time_ago($request['request_sent_at']); ?>
                                    </p>
                                </div>
                                <div class="req-actions">
                                    <button class="btn-decline" onclick="cancelRequest(<?php echo $request['UserID']; ?>)">
                                        <i class="fa-solid fa-ban"></i> Cancel
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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
