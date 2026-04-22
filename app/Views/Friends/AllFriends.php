<?php
session_start();

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social7 All Friends</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(lc_public_asset('css/Home/dashboard.css')); ?>">
    <style>
        /* Friends Container Layout */
        .friends-container {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 30px;
            margin-top: 20px;
        }

        /* Left Content */
        .left-content {
            background: white;
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(255, 37, 37, 0.05);
        }

        /* Friends Tabs */
        .friends-tabs {
            background: #e2e8f0;
            padding: 8px;
            border-radius: 16px;
            display: inline-flex;
            gap: 10px;
            width: fit-content;
            margin-bottom: 30px;
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

        /* Friends Grid */
        .friends-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 30px;
        }

        .friend-profile {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: 0.3s;
            text-align: center;
            padding: 15px;
            border-radius: 16px;
            background: #f8fafc;
            transition: all 0.3s ease;
        }

        .friend-profile:hover {
            transform: translateY(-5px);
            background: #f1f5f9;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .friend-profile img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .friend-profile .name {
            font-weight: 700;
            font-size: 1rem;
            color: #1e293b;
        }

        .friend-profile .friend-since {
            font-size: 0.75rem;
            color: #64748b;
        }

        .friend-actions {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }

        .btn-message, .btn-remove-friend {
            padding: 6px 12px;
            border-radius: 8px;
            border: none;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-message {
            background: #2563eb;
            color: white;
        }

        .btn-message:hover {
            background: #1d4ed8;
        }

        .btn-remove-friend {
            background: #ef4444;
            color: white;
        }

        .btn-remove-friend:hover {
            background: #dc2626;
        }

        /* Right Sidebar */
        .right-sidebar {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .sidebar-section {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Suggestions */
        .suggestion-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .suggestion-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px;
            border-radius: 12px;
            transition: 0.3s;
        }

        .suggestion-item:hover {
            background: #f8fafc;
        }

        .suggestion-item img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
        }

        .suggest-info {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .suggest-name {
            font-weight: 600;
            font-size: 0.95rem;
            color: #1e293b;
        }

        .suggest-mutual {
            font-size: 0.75rem;
            color: #64748b;
        }

        .suggest-add {
            color: #2563eb;
            cursor: pointer;
            font-size: 1.2rem;
            transition: 0.2s;
            padding: 8px;
            border-radius: 50%;
        }

        .suggest-add:hover {
            background: #eff6ff;
            transform: scale(1.1);
        }

        /* Event Card */
        .event-card-small {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .event-item {
            display: flex;
            gap: 12px;
            padding: 10px;
            border-radius: 12px;
            background: #f8fafc;
        }

        .event-date-box {
            background: #dbeafe;
            padding: 8px 12px;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-width: 55px;
        }

        .event-month {
            font-size: 0.7rem;
            font-weight: 700;
            color: #2563eb;
        }

        .event-day {
            font-size: 1.2rem;
            font-weight: 800;
            color: #1e3a8a;
        }

        .event-details {
            flex: 1;
        }

        .event-name {
            display: block;
            font-weight: 700;
            font-size: 0.95rem;
            color: #1e293b;
        }

        .event-location {
            font-size: 0.8rem;
            color: #64748b;
        }

        .btn-view-event {
            width: 100%;
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        .btn-view-event:hover {
            background: #1d4ed8;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }

        .empty-state i {
            font-size: 4rem;
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

        /* Toast Notification */
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

        /* Dark Mode */
        body.dark-mode .left-content,
        body.dark-mode .sidebar-section {
            background: #1e293b;
            color: #f1f5f9;
        }

        body.dark-mode .friend-profile {
            background: #0f172a;
        }

        body.dark-mode .friend-profile .name {
            color: #f1f5f9;
        }

        body.dark-mode .suggestion-item:hover {
            background: #0f172a;
        }

        body.dark-mode .event-item {
            background: #0f172a;
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

        @media (max-width: 1024px) {
            .friends-container {
                grid-template-columns: 1fr;
            }
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

        <div class="friends-container">
            <div class="left-content">
                <div class="friends-tabs">
                    <a href="AllFriends.php"><button class="tab-btn active">All Friends <span style="background: #2563eb20; padding: 2px 8px; border-radius: 20px; margin-left: 8px;"><?php echo $friendCount; ?></span></button></a>
                    <a href="AddFriends.php"><button class="tab-btn">Add Friends</button></a>
                    <a href="FriendsRequest.php"><button class="tab-btn">Friend Request</button></a>
                </div>

                <div class="friends-card">
                    <?php if (empty($friends)): ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-user-friends"></i>
                            <h3>No friends yet</h3>
                            <p>Start connecting with people by adding friends!</p>
                            <a href="AddFriends.php" style="display: inline-block; margin-top: 15px; padding: 10px 20px; background: #2563eb; color: white; border-radius: 8px; text-decoration: none;">Find Friends</a>
                        </div>
                    <?php else: ?>
                        <div class="friends-grid" id="friendsGrid">
                            <?php foreach ($friends as $friend): ?>
                                <div class="friend-profile" data-name="<?php echo strtolower(htmlspecialchars($friend['Username'])); ?>">
                                <a href="../Profile/profile.php?user_id=<?php echo $friend['UserID']; ?>" style="text-decoration: none; color: inherit; display: flex; flex-direction: column; align-items: center; gap: 12px;">
                                    <img src="<?php 
                                        $path = '../../../public/img/' . $friend['ProfilePic'];
                                        if (!empty($friend['ProfilePic']) && file_exists($path)) {
                                            echo $path;
                                        } else {
                                            echo 'https://via.placeholder.com/100';
                                        }
                                    ?>" alt="<?php echo htmlspecialchars($friend['Username']); ?>">
                                    <span class="name"><?php echo htmlspecialchars($friend['Username']); ?></span>
                                </a>
                                    <span class="friend-since">
                                        <i class="fa-regular fa-clock"></i> 
                                        Friend since <?php echo date('M Y', strtotime($friend['friend_since'])); ?>
                                    </span>
                                    <div class="friend-actions">
                                        <button class="btn-message" onclick="startChat(<?php echo $friend['UserID']; ?>)">
                                            <i class="fa-regular fa-message"></i> Message
                                        </button>
                                        <button class="btn-remove-friend" onclick="removeFriend(<?php echo $friend['UserID']; ?>, this)">
                                            <i class="fa-regular fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <aside class="right-sidebar">
                <div class="sidebar-section">
                    <h3 class="section-title">
                        <i class="fa-solid fa-user-plus"></i>
                        Suggestions
                    </h3>
                    <div class="suggestion-list">
                        <?php if (empty($suggested_friends)): ?>
                            <div style="text-align: center; color: #64748b; padding: 20px;">
                                <i class="fa-solid fa-users-slash"></i>
                                <p>No suggestions available</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($suggested_friends as $suggest): ?>
                                <div class="suggestion-item">
                                    <img src="<?php 
                                        $path = '../../../public/img/' . $suggest['ProfilePic'];
                                        echo (!empty($suggest['ProfilePic']) && file_exists($path)) ? $path : 'https://via.placeholder.com/45';
                                    ?>" alt="<?php echo htmlspecialchars($suggest['Username']); ?>">
                                    <div class="suggest-info">
                                        <span class="suggest-name"><?php echo htmlspecialchars($suggest['Username']); ?></span>
                                        <?php if (!empty($suggest['Locations'])): ?>
                                            <span class="suggest-mutual"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($suggest['Locations']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <i class="fa-solid fa-user-plus suggest-add" onclick="sendFriendRequest(<?php echo $suggest['UserID']; ?>, this)"></i>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="sidebar-section">
                    <h3 class="section-title">
                        <i class="fa-solid fa-calendar-days"></i>
                        Upcoming Events
                    </h3>
                    <div class="event-card-small">
                        <?php if (empty($upcoming_events)): ?>
                            <p style="font-size: 0.85rem; color: #64748b; text-align: center; padding: 10px 0;">No upcoming events.</p>
                        <?php else: ?>
                            <?php foreach ($upcoming_events as $ev): ?>
                                <?php
                                    $dt = new DateTime($ev['EventDateTime']);
                                    $month = strtoupper($dt->format('M'));
                                    $day   = $dt->format('j');
                                ?>
                                <div class="event-item">
                                    <div class="event-date-box">
                                        <span class="event-month"><?php echo $month; ?></span>
                                        <span class="event-day"><?php echo $day; ?></span>
                                    </div>
                                    <div class="event-details">
                                        <span class="event-name"><?php echo htmlspecialchars($ev['Title']); ?></span>
                                        <span class="event-location"><?php echo htmlspecialchars($ev['Location']); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <button class="btn-view-event" onclick="location.href='../Events/events.php'">
                            View All Events
                        </button>
                    </div>
                </div>
            </aside>
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
