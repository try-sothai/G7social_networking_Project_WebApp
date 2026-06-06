<?php

require_once __DIR__ . '/../Component/dashboard_shell.php';

require_once '../../../config/db_config.php';
require_once '../../Models/Profile/ProfileModel.php';
require_once '../../Models/Friends/FriendModel.php';
require_once '../../Models/Events/EventModel.php';
require_once '../../Helpers/functions.php'; // Load if time_ago is defined in a Helper

// Security check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login_gmail.php");
    exit();
}

$model = new ProfileModel($conn);
$friendModel = new FriendModel($conn);
$user_id = $_SESSION['user_id'];
$u_d = $model->getUserData($user_id);

// Fetch the full list of friends
$friends = $friendModel->getFriends($user_id);
$friendCount = $friendModel->getFriendCount($user_id);
$unread_notifications = $friendModel->getUnreadNotificationsCount($user_id);
$suggested_friends = $friendModel->getSuggestedFriends($user_id, 5);
$eventModel = new EventModel($conn);
$upcoming_events = array_slice($eventModel->getUpcomingEvents($user_id), 0, 3);

$profilePicPath = getAvatarUrl($u_d['ProfilePic'] ?? null);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social7 All Friends</title>
    <?php lc_shell_render_head_assets(); ?>
    
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
            $allFriendsNotificationHtml = ob_get_clean();
            lc_shell_render_header([
                'search_input_id' => 'searchFriends',
                'search_placeholder' => 'Search your friends...',
                'notification_html' => $allFriendsNotificationHtml,
                'notification_button_id' => 'notificationBtn',
                'user_name' => (string)$u_d['Username'],
                'profile_image' => $profilePicPath,
            ]);
        ?>

        <div class="friends-page">
            <div class="friends-tabs">
                <a href="AllFriends.php" class="tab-btn active">
                    <i class="fa-solid fa-user-group"></i> All Friends
                    <span class="tab-count"><?php echo $friendCount; ?></span>
                </a>
                <a href="AddFriends.php" class="tab-btn">
                    <i class="fa-solid fa-user-plus"></i> Add Friends
                </a>
                <a href="FriendsRequest.php" class="tab-btn">
                    <i class="fa-solid fa-inbox"></i> Requests
                </a>
            </div>

            <div class="friends-layout">
                <div>
                    <div class="friends-card">
                        <div class="sec-header">
                            <h2 class="sec-title"><i class="fa-solid fa-users"></i> Your Friends</h2>
                        </div>
                        <?php if (empty($friends)): ?>
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fa-solid fa-user-group"></i></div>
                                <h3>No friends yet</h3>
                                <p>Start connecting with people!</p>
                                <a href="AddFriends.php" class="btn-find-friends"><i class="fa-solid fa-user-plus"></i> Find Friends</a>
                            </div>
                        <?php else: ?>
                            <div class="friends-grid" id="friendsGrid">
                                <?php foreach ($friends as $friend): ?>
                                    <?php $fImg = getAvatarUrl($friend['ProfilePic'] ?? null); ?>
                                    <div class="friend-card-item" data-name="<?php echo strtolower(htmlspecialchars($friend['Username'])); ?>">
                                        <div class="f-avatar">
                                            <img src="<?php echo $fImg; ?>" alt="<?php echo htmlspecialchars($friend['Username']); ?>">
                                        </div>
                                        <a href="../Profile/profile.php?user_id=<?php echo $friend['UserID']; ?>" class="f-name">
                                            <?php echo htmlspecialchars($friend['Username']); ?>
                                        </a>
                                        <span class="f-since">
                                            <i class="fa-regular fa-clock"></i>
                                            Since <?php echo date('M Y', strtotime($friend['friend_since'])); ?>
                                        </span>
                                        <div class="f-actions">
                                            <button class="btn-msg" onclick="startChat(<?php echo $friend['UserID']; ?>)">
                                                <i class="fa-regular fa-message"></i> Message
                                            </button>
                                            <button class="btn-rm" title="Remove friend" onclick="removeFriend(<?php echo $friend['UserID']; ?>, this)">
                                                <i class="fa-solid fa-user-minus"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <aside class="right-sidebar">
                    <div class="sidebar-box">
                        <div class="sec-header">
                            <h3 class="sec-title"><i class="fa-solid fa-user-plus"></i> Suggestions</h3>
                        </div>
                        <?php if (empty($suggested_friends)): ?>
                            <p style="font-size:.8rem;color:#94a3b8;text-align:center;padding:12px 0;">No suggestions right now.</p>
                        <?php else: ?>
                            <?php foreach ($suggested_friends as $suggest): ?>
                                <?php $sImg = getAvatarUrl($suggest['ProfilePic'] ?? null); ?>
                                <div class="inline-suggestion">
                                    <img src="<?php echo $sImg; ?>" alt="<?php echo htmlspecialchars($suggest['Username']); ?>">
                                    <div class="inline-suggest-info">
                                        <div class="inline-suggest-name"><?php echo htmlspecialchars($suggest['Username']); ?></div>
                                        <?php if (!empty($suggest['Locations'])): ?>
                                            <div class="inline-suggest-sub"><i class="fa-solid fa-location-dot" style="font-size:.65rem"></i> <?php echo htmlspecialchars($suggest['Locations']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <button class="btn-add-inline" title="Add friend" onclick="sendFriendRequest(<?php echo $suggest['UserID']; ?>, this)">
                                        <i class="fa-solid fa-user-plus"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="sidebar-box">
                        <div class="sec-header">
                            <h3 class="sec-title"><i class="fa-solid fa-calendar-days"></i> Upcoming Events</h3>
                        </div>
                        <?php if (empty($upcoming_events)): ?>
                            <p style="font-size:.8rem;color:#94a3b8;text-align:center;padding:10px 0;">No upcoming events.</p>
                        <?php else: ?>
                            <?php foreach ($upcoming_events as $ev): ?>
                                <?php
                                    $dt    = new DateTime($ev['EventDateTime']);
                                    $month = strtoupper($dt->format('M'));
                                    $day   = $dt->format('j');
                                ?>
                                <div class="ev-item">
                                    <div class="ev-date">
                                        <div class="ev-month"><?php echo $month; ?></div>
                                        <div class="ev-day"><?php echo $day; ?></div>
                                    </div>
                                    <div class="ev-info">
                                        <span class="ev-name"><?php echo htmlspecialchars($ev['Title']); ?></span>
                                        <span class="ev-loc"><i class="fa-solid fa-location-dot" style="font-size:.62rem"></i> <?php echo htmlspecialchars($ev['Location']); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <button class="btn-all-events" onclick="location.href='../Events/events.php'">
                            <i class="fa-solid fa-calendar-days"></i> View All Events
                        </button>
                    </div>
                </aside>
            </div>
        </div>
    </div>

    <?php lc_shell_render_overlay(); ?>
    <div id="toast" class="toast"></div>

    <?php require_once __DIR__ . '/../Component/create_post_modal.php'; ?>
    <script src="../../../public/js/common/theme-toggle.js"></script>
    <script>
        // Search friends functionality
        document.getElementById('searchFriends').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const friends = document.querySelectorAll('.friend-profile');
            friends.forEach(friend => {
                const name = friend.getAttribute('data-name');
                if (name.includes(searchTerm)) {
                    friend.style.display = 'flex';
                } else {
                    friend.style.display = 'none';
                }
            });
        });
        
        // Send friend request
        function sendFriendRequest(userId, element) {
            const icon = element;
            icon.style.transform = 'scale(0.9)';
            
            fetch('../../Controllers/Friends/friend_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=send_request&friend_id=${userId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    icon.style.color = '#10b981';
                    icon.classList.remove('fa-user-plus');
                    icon.classList.add('fa-check');
                    icon.style.cursor = 'default';
                    icon.onclick = null;
                } else {
                    showToast(data.message, 'error');
                    icon.style.transform = 'scale(1)';
                }
            })
            .catch(error => {
                showToast('An error occurred', 'error');
                icon.style.transform = 'scale(1)';
            });
        }
        
        // Remove friend
        function removeFriend(userId, button) {
            if (confirm('Are you sure you want to remove this friend?')) {
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
                button.disabled = true;
                
                fetch('../../Controllers/Friends/friend_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=remove_friend&friend_id=${userId}`
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
        }
        
        // Start chat with friend
        function startChat(userId) {
            window.location.href = `../Messages/message.php?user_id=${userId}`;
        }
        
        // Show toast notification
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
    <?php lc_shell_render_user_bootstrap((int)($_SESSION['user_id'] ?? 0), (string)($u_d['Username'] ?? 'User'), $profilePicPath); ?>
    <script src="<?php echo htmlspecialchars(lc_public_asset('js/Home/dashboard.js')); ?>"></script>
    <script src="<?php echo htmlspecialchars(lc_public_asset('js/Home/responsive.js')); ?>"></script>
</body>
</html>
