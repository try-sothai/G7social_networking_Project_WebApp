<?php
    require_once __DIR__ . "/../../../config/db_config.php"; 
    session_start();
    require_once '../../Helpers/functions.php';
    require_once '../../Models/Profile/ProfileModel.php';
    require_once '../../Models/Messages/MessageModel.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../Login/login_gmail.php");
        exit();
    }

    $model = new ProfileModel($conn);
    $messageModel = new MessageModel($conn);
    $user_id = $_SESSION['user_id'];
    $u_d = $model->getUserData($user_id);
    $unreadCount = $messageModel->getUnreadCount($user_id);
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - LocalConnect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous"> 
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/css/Messages/message.css">
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
            <a href="../Groups/groups.php" class="item"><i class="fa-solid fa-people-group"></i> Groups</a>
            <a href="../Events/events.php" class="item"><i class="fa-solid fa-calendar-days"></i> Events</a>
            <a href="../Friends/AddFriends.php" class="item"><i class="fa-solid fa-user-friends"></i> Friends</a>
            <a href="../Messages/message.php" class="item active"><i class="fa-solid fa-message"></i> Messages</a>
            <a href="../Report/userReport.php" class="item"><i class="fa-solid fa-chart-simple"></i> Report</a>      
        </nav>

        <button class="btn-create" onclick="location.href='../Posts/create.php'">
            <i class="fa-solid fa-plus-circle"></i>
            <span>Create New Post</span>
        </button>
    </div>

    <div class="main-content">
        <header>
            <div class="search-bar">
                <i class="fa-solid fa-search text-muted"></i>
                <input type="text" id="search-input" placeholder="Search friends...">
            </div>
            
            <div class="header-actions">
                <div class="icon-btn theme-toggle"><i class="fa-solid fa-moon"></i></div>
                <div class="icon-btn" style="position: relative;">
                    <i class="fa-solid fa-bell"></i>
                    <?php if ($unreadCount > 0): ?>
                        <span class="notification-badge"><?php echo $unreadCount; ?></span>
                    <?php endif; ?>
                </div>
                <div class="user-profile">
                    <span id="current-user-name" data-id="<?= $user_id ?>"><?= htmlspecialchars($u_d['Username']); ?></span>
                    <a href="../Profile/profile.php">
                        <img id="my-avatar-img" src="<?= !empty($u_d['ProfilePic']) ? '../../../public/img/' . $u_d['ProfilePic'] : 'https://via.placeholder.com/50' ?>" alt="Profile">
                    </a>
                </div>
            </div>
        </header>

        <div class="chat-container">
            <div class="chat-sidebar">
                <div class="chat-search">
                    <input type="text" id="search-input-sidebar" placeholder="Search friends...">
                </div>
                <div class="contact-list" id="user-list-container">
                    <div class="loading-state"><i class="fa-solid fa-spinner fa-spin"></i> Loading friends...</div>
                </div>
            </div>

            <div id="chat-welcome" class="chat-main empty-state">
                <i class="fa-regular fa-comments"></i>
                <h3>Select a conversation</h3>
                <p>Choose a friend to start messaging</p>
            </div>
                
            <div id="chat-active" class="chat-main" style="display: none;">
                <div class="chat-header">
                    <div class="header-info">
                        <div class="avatar-wrapper">
                            <img src="" class="header-avatar" id="active-user-img">
                            <span class="online-status"></span>
                        </div>
                        <div class="user-meta">
                            <span class="header-name" id="active-user-name">...</span>
                            <span class="header-status">Online</span>
                        </div>
                    </div>
                    <div class="header-actions">
                        <button class="call-btn" id="call-audio-btn" title="Audio call">
                            <i class="fa-solid fa-phone"></i>
                        </button>
                        <button class="call-btn" id="call-video-btn" title="Video call">
                            <i class="fa-solid fa-video"></i>
                        </button>
                        <button class="call-btn" id="attach-btn" title="Attach file">
                            <i class="fa-solid fa-paperclip"></i>
                        </button>
                    </div>
                </div>

                <div class="chat-messages" id="chat-box"></div>

                <div class="chat-input-area">
                    <button class="action-btn" id="audio-btn" title="Voice message">
                        <i class="fa-solid fa-microphone"></i>
                    </button>
                    <div class="input-wrapper">
                        <textarea id="msg-input" rows="1" placeholder="Type a message..."></textarea>
                    </div>
                    <input type="file" id="file-input" style="display: none;" accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.ppt,.pptx">
                    <button id="send-btn" class="send-btn">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>            
    </div>

    <script>
        const API_PATH = '../../Controllers/Messages/ChatController.php';
        const CURRENT_USER_ID = <?= json_encode($user_id) ?>;
        const MY_AVATAR = <?= json_encode(!empty($u_d['ProfilePic']) ? getImageUrl($u_d['ProfilePic']) : 'https://via.placeholder.com/40') ?>;
        window.APP_CONFIG = <?php echo getJsConfig(); ?>;
    </script>
    <script src="../../../public/js/Messages/message.js"></script>
    <script src="../../../public/js/Messages/send_img.js"></script>
    <script src="../../../public/js/common/theme-toggle.js"></script>
</body>
</html>
