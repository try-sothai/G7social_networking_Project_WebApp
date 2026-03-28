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
    <title>LocalConnect - Add Friends</title>
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

        .search-section {
            margin-bottom: 30px;
        }

        .search-box {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .search-box input {
            flex: 1;
            padding: 12px 20px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            outline: none;
            transition: 0.3s;
        }

        .search-box input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .search-box button {
            padding: 12px 24px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }

        .search-box button:hover {
            background: #1d4ed8;
        }

        .search-results {
            display: none;
            margin-bottom: 30px;
        }

        .search-results.active {
            display: block;
        }

        .suggestions-section h3,
        .search-results h3 {
            font-size: 1.2rem;
            margin-bottom: 20px;
            color: #1e293b;
        }

        .friends-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .friend-card {
            background: #f8fafc;
            border-radius: 16px;
            padding: 20px;
            transition: 0.3s;
            border: 1px solid #e2e8f0;
        }

        .friend-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .friend-avatar {
            text-align: center;
            margin-bottom: 15px;
        }

        .friend-avatar img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .friend-info {
            text-align: center;
            margin-bottom: 15px;
        }

        .friend-name {
            font-weight: 700;
            font-size: 1.1rem;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .friend-location {
            font-size: 0.85rem;
            color: #64748b;
        }

        .friend-bio {
            font-size: 0.9rem;
            color: #475569;
            margin-top: 8px;
            line-height: 1.4;
        }

        .friend-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn-add-friend,
        .btn-pending,
        .btn-friends {
            padding: 8px 20px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-add-friend {
            background: #2563eb;
            color: white;
        }

        .btn-add-friend:hover {
            background: #1d4ed8;
        }

        .btn-pending {
            background: #e2e8f0;
            color: #475569;
            cursor: not-allowed;
        }

        .btn-friends {
            background: #10b981;
            color: white;
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
                <i class="fa-solid fa-search text-muted"></i>
                <input type="text" placeholder="Search for anything...">
            </div>
            <div class="header-actions">
                <div class="icon-btn theme-toggle"><i class="fa-solid fa-moon"></i></div>
                <div class="icon-btn" style="position: relative;">
                    <i class="fa-solid fa-bell"></i>
                    <?php if ($unread_notifications > 0): ?>
                        <span style="position: absolute; top: -5px; right: -5px; width: 8px; height: 8px; background: #ef4444; border-radius: 50%;"></span>
                    <?php endif; ?>
                </div>
                <div class="user-profile">
                    <span style="font-weight: 700;"><?php echo htmlspecialchars($user_data['Username']); ?></span>
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
                <a href="AddFriends.php" class="tab-btn active">Add Friends</a>
                <a href="FriendsRequest.php" class="tab-btn">Friend Request</a>
            </div>

            <div class="friends-card">
                <!-- Search Section -->
                <div class="search-section">
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="Search by name, email, or location...">
                        <button onclick="searchUsers()"><i class="fa-solid fa-search"></i> Search</button>
                    </div>
                    
                    <div id="searchResults" class="search-results">
                        <h3>Search Results</h3>
                        <div id="searchResultsGrid" class="friends-grid"></div>
                    </div>
                </div>

                <!-- Suggestions Section -->
                <div class="suggestions-section" id="suggestionsSection">
                    <h3>People You May Know</h3>
                    <div class="friends-grid" id="suggestionsGrid">
                        <?php if (empty($suggested_friends)): ?>
                            <div class="empty-state">
                                <i class="fa-solid fa-users"></i>
                                <h3>No suggestions available</h3>
                                <p>Check back later for friend suggestions</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($suggested_friends as $friend): ?>
                                <div class="friend-card" data-user-id="<?php echo $friend['UserID']; ?>">
                                    <div class="friend-avatar">
                                        <img src="<?php 
                                            $path = '../../../public/img/' . $friend['ProfilePic'];
                                            echo (!empty($friend['ProfilePic']) && file_exists($path)) ? $path : 'https://via.placeholder.com/80';
                                        ?>" alt="<?php echo htmlspecialchars($friend['Username']); ?>">
                                    </div>
                                    <div class="friend-info">
                                        <div class="friend-name"><?php echo htmlspecialchars($friend['Username']); ?></div>
                                        <?php if (!empty($friend['Locations'])): ?>
                                            <div class="friend-location">
                                                <i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($friend['Locations']); ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($friend['Bio'])): ?>
                                            <div class="friend-bio"><?php echo htmlspecialchars(substr($friend['Bio'], 0, 60)); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="friend-actions">
                                        <button class="btn-add-friend" onclick="sendFriendRequest(<?php echo $friend['UserID']; ?>, this)">
                                            <i class="fa-solid fa-user-plus"></i> Add Friend
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="toast" class="toast"></div>

    <?php require_once __DIR__ . '/../Component/create_post_modal.php'; ?>
    <script>
        let searchTimeout;
        
        function searchUsers() {
            const searchTerm = document.getElementById('searchInput').value.trim();
            
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
                resultsGrid.innerHTML = '<div class="empty-state"><i class="fa-solid fa-user-slash"></i><h3>No users found</h3><p>Try a different search term</p></div>';
            } else {
                resultsGrid.innerHTML = users.map(user => `
                    <div class="friend-card" data-user-id="${user.UserID}">
                        <div class="friend-avatar">
                            <img src="${user.ProfilePic && user.ProfilePic !== 'default.png' ? '../../../public/img/' + user.ProfilePic : 'https://via.placeholder.com/80'}" 
                                 alt="${escapeHtml(user.Username)}">
                        </div>
                        <div class="friend-info">
                            <div class="friend-name">${escapeHtml(user.Username)}</div>
                            ${user.Locations ? `<div class="friend-location"><i class="fa-solid fa-location-dot"></i> ${escapeHtml(user.Locations)}</div>` : ''}
                            ${user.Bio ? `<div class="friend-bio">${escapeHtml(user.Bio.substring(0, 60))}</div>` : ''}
                        </div>
                        <div class="friend-actions">
                            ${getActionButton(user.friendship_status, user.UserID)}
                        </div>
                    </div>
                `).join('');
            }
            
            searchResultsDiv.classList.add('active');
        }
        
        function getActionButton(status, userId) {
            switch(status) {
                case 'Accepted':
                    return '<button class="btn-friends" disabled><i class="fa-solid fa-check"></i> Friends</button>';
                case 'Pending':
                    return '<button class="btn-pending" disabled><i class="fa-solid fa-clock"></i> Request Sent</button>';
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
        document.getElementById('searchInput').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.trim().length >= 2) {
                    searchUsers();
                } else if (this.value.trim().length === 0) {
                    document.getElementById('searchResults').classList.remove('active');
                }
            }, 500);
        });
        
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
    <script>
        const CURRENT_USER_ID = <?php echo (int)($_SESSION['user_id'] ?? 0); ?>;
        const CURRENT_USER_NAME = <?php echo json_encode((string)($user_data['Username'] ?? 'User')); ?>;
        const CURRENT_USER_AVATAR = <?php echo json_encode($profilePicPath ?? 'https://via.placeholder.com/150'); ?>;
    </script>
    <script src="../../../public/js/Home/dashboard.js"></script>
</body>
</html>


