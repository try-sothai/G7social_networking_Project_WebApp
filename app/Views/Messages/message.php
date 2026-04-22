<?php
    require_once __DIR__ . "/../../../config/db_config.php"; 
    session_start();
    require_once '../../Helpers/functions.php';
    require_once '../../Models/Profile/ProfileModel.php';
    require_once '../../Models/Messages/MessageModel.php';
    require_once '../../Models/Friends/FriendModel.php';
    require_once __DIR__ . '/../Component/dashboard_shell.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../Login/login_gmail.php");
        exit();
    }

    $model = new ProfileModel($conn);
    $messageModel = new MessageModel($conn);
    $friendModel = new FriendModel($conn);
    $user_id = $_SESSION['user_id'];
    $u_d = $model->getUserData($user_id);
    $unreadCount = $messageModel->getUnreadCount($user_id);
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Socail7 Messages Page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous"> 
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/css/Messages/message.css?v=<?php echo filemtime(__DIR__.'/../../../public/css/Messages/message.css'); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(lc_public_asset('css/Home/dashboard.css')); ?>">
    <style>
        /* Hide Create Post FAB on Messages page */
        .mobile-fab { display: none !important; }

        /* Mobile Chat Responsive Fix */
        .mobile-back-btn {
            display: none;
            background: none;
            border: none;
            font-size: 20px;
            color: inherit;
            cursor: pointer;
            margin-right: 15px;
            padding: 5px;
        }
        @media (max-width: 768px) {
            .chat-container {
                position: relative;
                overflow: hidden;
                display: flex;
            }
            .chat-sidebar {
                width: 100% !important;
                flex: none !important;
                border-right: none !important;
            }
            .chat-main {
                position: absolute;
                top: 0;
                left: 0;
                width: 100% !important;
                height: 100%;
                background: var(--bg-card, #ffffff);
                transform: translateX(100%);
                transition: transform 0.3s ease;
                z-index: 10;
            }
            body.dark-mode .chat-main { background: #111827; }
            #chat-welcome { display: none !important; }
            .chat-main.mobile-active { transform: translateX(0); }
            .mobile-back-btn { display: block; }
        }
    </style>
</head>
<body>
    <?php lc_shell_render_sidebar(['active' => 'messages']); ?>

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
            $messagesNotificationHtml = ob_get_clean();
            lc_shell_render_header([
                'search_input_id' => 'message-header-search',
                'search_placeholder' => 'Search friends...',
                'notification_html' => $messagesNotificationHtml,
                'notification_button_id' => 'notificationBtn',
                'user_name' => (string)$u_d['Username'],
                'user_name_class' => '',
                'user_name_attrs' => 'id="current-user-name" data-id="' . (int)$user_id . '"',
                'profile_image' => $profilePicPath,
                'profile_image_attrs' => 'id="my-avatar-img"',
            ]);
        ?>

        <div class="chat-container">
            <div class="chat-sidebar">
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
                        <button id="mobile-back-to-list" class="mobile-back-btn"><i class="fa-solid fa-arrow-left"></i></button>
                        <a href="#" id="active-user-profile-link" class="avatar-wrapper" style="text-decoration:none;">
                            <img src="" class="header-avatar" id="active-user-img">
                            <span class="online-status"></span>
                        </a>
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

    <?php lc_shell_render_overlay(); ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const userList = document.getElementById('user-list-container');
            const chatActive = document.getElementById('chat-active');
            const backBtn = document.getElementById('mobile-back-to-list');

            // Show chat panel when a friend name is clicked (mobile only)
            if (userList) {
                userList.addEventListener('click', (e) => {
                    const item = e.target.closest('div');
                    if (item && !item.classList.contains('loading-state') && window.innerWidth <= 768) {
                        chatActive.classList.add('mobile-active');
                    }
                });
            }
            // Hide the chat panel when the Back button is pressed
            if (backBtn) {
                backBtn.addEventListener('click', () => {
                    chatActive.classList.remove('mobile-active');
                });
            }
            
            // Real-time friend search using header input
            const msgSearchInput = document.getElementById('message-header-search');
            if (msgSearchInput) {
                msgSearchInput.addEventListener('input', function() {
                    const term = this.value.toLowerCase().trim();
                    const items = document.querySelectorAll('#user-list-container > div:not(.loading-state)');
                    items.forEach(item => {
                        item.style.display = item.textContent.toLowerCase().includes(term) ? 'flex' : 'none';
                    });
                });
                msgSearchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') e.preventDefault();
                });
            }
        });
    </script>
    <?php lc_shell_render_user_bootstrap((int)($_SESSION['user_id'] ?? 0), (string)($u_d['Username'] ?? 'User'), $profilePicPath); ?>
    <script>
        const API_PATH = '../../Controllers/Messages/ChatController.php';
        const MY_AVATAR = <?= json_encode(!empty($u_d['ProfilePic']) ? getImageUrl($u_d['ProfilePic']) : 'https://via.placeholder.com/40') ?>;
        window.APP_CONFIG = <?php echo getJsConfig(); ?>;
    </script>
    <script src="../../../public/js/Messages/message.js?v=<?php echo filemtime(__DIR__.'/../../../public/js/Messages/message.js'); ?>"></script>
    <script src="../../../public/js/Messages/send_img.js"></script>
    <script src="../../../public/js/common/theme-toggle.js"></script>
    <script src="../../../public/js/common/notifications.js"></script>
    <script src="<?php echo htmlspecialchars(lc_public_asset('js/Home/responsive.js')); ?>"></script>
</body>
</html>
