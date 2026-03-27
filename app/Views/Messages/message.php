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
    <style>
        /* * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            min-height: 100vh; 
        } */

        /* ===== CHAT CONTAINER ===== */
        .chat-container {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 24px;
            margin-top: 20px;
            height: calc(100vh - 120px);
        }

        /* ===== CHAT SIDEBAR ===== */
        .chat-sidebar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 28px;
            padding: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            flex-direction: column;
        }

        .chat-search {
            margin-bottom: 20px;
        }

        .chat-search input {
            width: 100%;
            padding: 12px 16px;
            background: linear-gradient(135deg, #f8fafc 0%, #f0f4f8 100%);
            border: 2px solid rgba(102, 126, 234, 0.2);
            border-radius: 12px;
            font-size: 0.9rem;
            outline: none;
            transition: all 0.3s;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .chat-search input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-1px);
        }

        .contact-list {
            flex: 1;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(102, 126, 234, 0.3) transparent;
        }

        .contact-list::-webkit-scrollbar {
            width: 6px;
        }

        .contact-list::-webkit-scrollbar-track {
            background: transparent;
        }

        .contact-list::-webkit-scrollbar-thumb {
            background: rgba(102, 126, 234, 0.3);
            border-radius: 3px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 4px;
            position: relative;
        }

        .contact-item:hover {
            background: rgba(102, 126, 234, 0.1);
            transform: translateX(4px);
        }

        .contact-item.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .contact-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .contact-item.active .contact-avatar {
            border-color: rgba(255, 255, 255, 0.8);
        }

        .contact-info {
            flex: 1;
            min-width: 0;
        }

        .contact-name {
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .contact-last-msg {
            font-size: 0.8rem;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .contact-item.active .contact-last-msg {
            color: rgba(255, 255, 255, 0.8);
        }

        .contact-time {
            font-size: 0.75rem;
            color: #94a3b8;
            align-self: flex-start;
        }

        .contact-item.active .contact-time {
            color: rgba(255, 255, 255, 0.7);
        }

        .unread-badge {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 600;
            align-self: flex-start;
            margin-left: auto;
        }

        /* ===== CHAT MAIN ===== */
        .chat-main {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 28px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            text-align: center;
            padding: 40px;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #1e293b;
        }

        .empty-state p {
            font-size: 1rem;
            color: #64748b;
        }

        /* ===== CHAT HEADER ===== */
        .chat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            border-bottom: 1px solid rgba(102, 126, 234, 0.1);
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
        }

        .header-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar-wrapper {
            position: relative;
        }

        .header-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .online-status {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 12px;
            height: 12px;
            background: #10b981;
            border: 2px solid white;
            border-radius: 50%;
        }

        .user-meta {
            display: flex;
            flex-direction: column;
        }

        .header-name {
            font-weight: 700;
            font-size: 1.1rem;
            color: #1e293b;
        }

        .header-status {
            font-size: 0.85rem;
            color: #10b981;
            font-weight: 500;
        }

        .header-actions {
            display: flex;
            gap: 8px;
        }

        .call-btn {
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 12px;
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .call-btn:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: scale(1.05);
        }

        /* ===== CHAT MESSAGES ===== */
        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(102, 126, 234, 0.3) transparent;
        }

        .chat-messages::-webkit-scrollbar {
            width: 6px;
        }

        .chat-messages::-webkit-scrollbar-track {
            background: transparent;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: rgba(102, 126, 234, 0.3);
            border-radius: 3px;
        }

        .message-wrapper {
            display: flex;
            margin-bottom: 16px;
            align-items: flex-end;
            gap: 8px;
        }

        .message-wrapper.me {
            justify-content: flex-end;
        }

        .message-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .message-content {
            max-width: 60%;
            position: relative;
        }

        .message-bubble {
            padding: 12px 16px;
            border-radius: 18px;
            font-size: 0.9rem;
            line-height: 1.4;
            word-wrap: break-word;
            position: relative;
        }

        .message-wrapper:not(.me) .message-bubble {
            background: linear-gradient(135deg, #f8fafc 0%, #f0f4f8 100%);
            color: #1e293b;
            border-bottom-left-radius: 4px;
        }

        .message-wrapper.me .message-bubble {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message-time {
            font-size: 0.7rem;
            margin-top: 4px;
            opacity: 0.7;
        }

        .message-wrapper:not(.me) .message-time {
            text-align: left;
        }

        .message-wrapper.me .message-time {
            text-align: right;
        }

        .message-content {
            position: relative;
            padding: 10px 15px;
            border-radius: 15px;
            background-color: #6366f1;
            color: white;
            display: inline-block;
            max-width: 68%;
            word-wrap: break-word;
        }

        .message-actions {
            position: absolute;
            right: 0;
            top: -26px;
            display: flex;
            gap: 5px;
            opacity: 0;
            transition: opacity 0.25s ease;
        }

        .message-content:hover .message-actions,
        .message-wrapper.me:hover .message-actions {
            opacity: 1;
        }

        .message-actions button {
            background: #f1f5f9;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
            font-size: 12px;
            color: #333;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        .message-actions .btn-delete:hover { color: red; }
        .message-actions .btn-edit:hover { color: blue; }

        .msg-actions {
            display: none;
            position: absolute;
            top: -8px;
            right: -8px;
            background: white;
            border-radius: 50%;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            z-index: 10;
            gap: 4px;
        }

        .message-wrapper.me .msg-actions {
            display: flex;
        }

        .msg-action-btn {
            width: 24px;
            height: 24px;
            border: none;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s;
        }

        .msg-action-btn:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }

        /* ===== CHAT INPUT AREA ===== */
        .chat-input-area {
            display: flex;
            align-items: flex-end;
            gap: 12px;
            padding: 20px 24px;
            border-top: 1px solid rgba(102, 126, 234, 0.1);
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
        }

        .action-btn {
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 12px;
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .action-btn:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: scale(1.05);
        }

        .input-wrapper {
            flex: 1;
            position: relative;
        }

        .input-wrapper textarea {
            width: 100%;
            min-height: 40px;
            max-height: 120px;
            padding: 12px 16px;
            border: 2px solid rgba(102, 126, 234, 0.2);
            border-radius: 20px;
            outline: none;
            resize: none;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9rem;
            transition: all 0.3s;
            background: rgba(255, 255, 255, 0.8);
        }

        .input-wrapper textarea:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
        }

        .send-btn {
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .send-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .send-btn:active {
            transform: scale(0.95);
        }

        /* ===== LOADING STATE ===== */
        .loading-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 200px;
            color: #64748b;
        }

        .loading-state i {
            font-size: 2rem;
            margin-bottom: 12px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            min-width: 220px;
            max-width: 340px;
            background: rgba(20, 20, 20, 0.95);
            color: white;
            padding: 12px 14px;
            border-radius: 10px;
            display: none;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.35);
            z-index: 2000;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.87rem;
        }

        .toast.info { background: rgba(30, 136, 221, 0.95); }
        .toast.success { background: rgba(34, 197, 94, 0.95); }
        .toast.error { background: rgba(239, 68, 68, 0.95); }

        .toast-action {
            border: 1px solid rgba(255,255,255,0.6);
            background: rgba(255,255,255,0.1);
            color: white;
            border-radius: 6px;
            cursor: pointer;
            padding: 5px 8px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .toast-action:hover {
            background: rgba(255,255,255,0.2);
        }

        /* ===== EDIT MODAL ===== */
        .edit-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .edit-modal {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 24px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .edit-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .edit-modal-title {
            font-weight: 700;
            color: #1e293b;
        }

        .edit-modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #64748b;
        }

        .edit-modal-body {
            margin-bottom: 20px;
        }

        .edit-textarea {
            width: 100%;
            min-height: 80px;
            padding: 12px 16px;
            border: 2px solid rgba(102, 126, 234, 0.2);
            border-radius: 12px;
            outline: none;
            resize: vertical;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9rem;
        }

        .edit-textarea:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .edit-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .btn-cancel, .btn-save {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-cancel {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .btn-cancel:hover {
            background: #ef4444;
            color: white;
        }

        .btn-save {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-save:hover {
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1024px) {
            .chat-container { grid-template-columns: 280px 1fr; }
        }

        @media (max-width: 768px) {
            .chat-container { 
                grid-template-columns: 1fr; 
                height: calc(100vh - 100px);
            }
            .chat-main { display: none; }
            .chat-main.show-mobile { display: flex; }
            .chat-sidebar { display: block; }
            .chat-sidebar.hide-mobile { display: none; }
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
            <a href="../Friends/AllFriends.php" class="item"><i class="fa-solid fa-user-friends"></i> Friends</a>
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
                        <img id="my-avatar-img" src="<?= !empty($u_d['ProfilePic']) ? '../../../public/img/' . $u_d['ProfilePic'] : 'https://via.placeholder.com/40' ?>" alt="Profile">
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
        const API_PATH = '../../../app/Controllers/Messages/ChatController.php';
        const CURRENT_USER_ID = <?= json_encode($user_id) ?>;
        const MY_AVATAR = <?= json_encode(!empty($u_d['ProfilePic']) ? getImageUrl($u_d['ProfilePic']) : 'https://via.placeholder.com/40') ?>;
        window.APP_CONFIG = <?php echo getJsConfig(); ?>;
    </script>
    <script src="../../../public/js/config.js"></script>
    <script src="../../../public/js/Messages/message.js"></script>
</body>
</html>