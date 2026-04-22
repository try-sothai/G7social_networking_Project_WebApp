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
$suggested_friends = $friendModel->getSuggestedFriends($user_id, 20);
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
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Socail7 Add Friends</title>
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
            $addFriendsNotificationHtml = ob_get_clean();
            lc_shell_render_header([
                'search_input_id' => 'addfriends-search-input',
                'search_placeholder' => 'Search by name, email, or location...',
                'notification_html' => $addFriendsNotificationHtml,
                'notification_button_id' => 'notificationBtn',
                'user_name' => (string)$user_data['Username'],
                'profile_image' => $profilePicPath,
            ]);
        ?>

        <div class="friends-page">
            <div class="friends-tabs">
                <a href="AllFriends.php" class="tab-btn"><i class="fa-solid fa-user-group"></i> All Friends</a>
                <a href="AddFriends.php" class="tab-btn active"><i class="fa-solid fa-user-plus"></i> Add Friends</a>
                <a href="FriendsRequest.php" class="tab-btn"><i class="fa-solid fa-inbox"></i> Requests</a>
            </div>

            <div class="friends-card">
                <!-- Search Results -->
                <div id="searchResults" style="display:none; margin-bottom:28px;">
                    <div class="sec-header">
                        <h2 class="sec-title"><i class="fa-solid fa-magnifying-glass"></i> Search Results</h2>
                    </div>
                    <div id="searchResultsGrid" class="suggestions-grid"></div>
                    <hr class="sec-divider">
                </div>

                <!-- Suggestions -->
                <div id="suggestionsSection">
                    <div class="sec-header">
                        <h2 class="sec-title"><i class="fa-solid fa-wand-sparkles"></i> People You May Know</h2>
                    </div>
                    <div class="suggestions-grid" id="suggestionsGrid">
                        <?php if (empty($suggested_friends)): ?>
                            <div class="empty-state" style="grid-column:1/-1;">
                                <div class="empty-icon"><i class="fa-solid fa-users"></i></div>
                                <h3>No suggestions yet</h3>
                                <p>Check back later for friend suggestions</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($suggested_friends as $friend): ?>
                                <?php
                                    $fPath = '../../../public/img/' . $friend['ProfilePic'];
                                    $fImg  = (!empty($friend['ProfilePic']) && file_exists($fPath)) ? $fPath : 'https://ui-avatars.com/api/?name=' . urlencode($friend['Username']) . '&background=dbeafe&color=1d4ed8&bold=true';
                                ?>
                                <div class="suggest-card" data-user-id="<?php echo $friend['UserID']; ?>">
                                    <img src="<?php echo $fImg; ?>" alt="<?php echo htmlspecialchars($friend['Username']); ?>">
                                    <div class="suggest-name"><?php echo htmlspecialchars($friend['Username']); ?></div>
                                    <?php if (!empty($friend['Locations'])): ?>
                                        <div class="suggest-meta"><i class="fa-solid fa-location-dot" style="font-size:.7rem"></i> <?php echo htmlspecialchars($friend['Locations']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($friend['Bio'])): ?>
                                        <div class="suggest-bio"><?php echo htmlspecialchars(substr($friend['Bio'], 0, 70)); ?></div>
                                    <?php endif; ?>
                                    <button class="btn-add-friend" onclick="sendFriendRequest(<?php echo $friend['UserID']; ?>, this)">
                                        <i class="fa-solid fa-user-plus"></i> Add Friend
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php lc_shell_render_overlay(); ?>
    <div id="toast" class="toast"></div>

    <?php require_once __DIR__ . '/../Component/create_post_modal.php'; ?>
    <script>
        let searchTimeout;
        
        function searchUsers() {
            const input = document.getElementById('addfriends-search-input');
            if (!input) return;
            const searchTerm = input.value.trim();
            
            if (searchTerm.length < 2) {
                showToast('Please enter at least 2 characters', 'error');
                return;
            }
            
            fetch(`../../Controllers/Friends/search_users.php?q=${encodeURIComponent(searchTerm)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displaySearchResults(data.users);
                    } else {
                        showToast('No users found', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred', 'error');
                });
        }
        
        function displaySearchResults(users) {
            const searchResultsDiv = document.getElementById('searchResults');
            const resultsGrid = document.getElementById('searchResultsGrid');

            if (users.length === 0) {
                resultsGrid.innerHTML = '<div class="empty-state" style="grid-column:1/-1;"><div class="empty-icon"><i class="fa-solid fa-user-slash"></i></div><h3>No users found</h3><p>Try a different search term</p></div>';
            } else {
                resultsGrid.innerHTML = users.map(user => {
                    const img = user.ProfilePic && user.ProfilePic !== 'default.png'
                        ? '../../../public/img/' + user.ProfilePic
                        : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.Username)}&background=dbeafe&color=1d4ed8&bold=true`;
                    return `<div class="suggest-card" data-user-id="${user.UserID}">
                        <img src="${img}" alt="${escapeHtml(user.Username)}">
                        <div class="suggest-name">${escapeHtml(user.Username)}</div>
                        ${user.Locations ? `<div class="suggest-meta"><i class="fa-solid fa-location-dot" style="font-size:.7rem"></i> ${escapeHtml(user.Locations)}</div>` : ''}
                        ${user.Bio ? `<div class="suggest-bio">${escapeHtml(user.Bio.substring(0, 70))}</div>` : ''}
                        <div>${getActionButton(user.friendship_status, user.UserID)}</div>
                    </div>`;
                }).join('');
            }

            searchResultsDiv.style.display = 'block';
        }

        function getActionButton(status, userId) {
            switch(status) {
                case 'Accepted':
                    return '<button class="btn-add-friend is-friend" disabled><i class="fa-solid fa-check"></i> Friends</button>';
                case 'Pending':
                    return '<button class="btn-add-friend sent" disabled><i class="fa-solid fa-clock"></i> Request Sent</button>';
                default:
                    return `<button class="btn-add-friend" onclick="sendFriendRequest(${userId}, this)"><i class="fa-solid fa-user-plus"></i> Add Friend</button>`;
            }
        }
        
        function sendFriendRequest(userId, button) {
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';
            button.disabled = true;
            
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
                    button.innerHTML = '<i class="fa-solid fa-clock"></i> Request Sent';
                    button.classList.remove('btn-add-friend');
                    button.classList.add('btn-pending');
                    button.disabled = true;
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
        
        function showToast(message, type) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = `toast ${type}`;
            toast.style.display = 'block';
            
            setTimeout(() => {
                toast.style.display = 'none';
            }, 3000);
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Auto-search on input
        const searchInput = document.getElementById('addfriends-search-input');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    if (this.value.trim().length >= 2) {
                        searchUsers();
                    } else if (this.value.trim().length === 0) {
                        document.getElementById('searchResults').style.display = 'none';
                    }
                }, 500);
            });
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') e.preventDefault();
            });
        }
        
        // Theme toggle
        document.querySelector('.theme-toggle').addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('localconnect_theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
        });
        
        if (!localStorage.getItem('localconnect_theme')) { localStorage.setItem('localconnect_theme', 'dark'); }
        if (localStorage.getItem('localconnect_theme') === 'dark') {
            document.body.classList.add('dark-mode');
        }
    </script>
    <?php lc_shell_render_user_bootstrap((int)($_SESSION['user_id'] ?? 0), (string)($user_data['Username'] ?? 'User'), $profilePicPath ?? 'https://via.placeholder.com/150'); ?>
    <script src="<?php echo htmlspecialchars(lc_public_asset('js/Home/dashboard.js')); ?>"></script>
    <script src="<?php echo htmlspecialchars(lc_public_asset('js/Home/responsive.js')); ?>"></script>
</body>
</html>
