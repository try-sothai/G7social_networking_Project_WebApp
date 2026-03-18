<?php
    require_once __DIR__ . "/../../../config/db_config.php"; 

    $current_user_id = 6;
    session_start();
    $current_user_id = $_SESSION['user_id'] ?? 6;
    // ProfileModel ត្រូវបានប្រើសម្រាប់ទាញយកព័ត៌មានអ្នកប្រើ
    require_once '../../Models/Profile/ProfileModel.php';
    // សុវត្ថិភាព
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../Login/login_gmail.php");
        exit();
    }

    $model = new ProfileModel($conn);
    $user_id = $_SESSION['user_id'];
    $u_d = $model->getUserData($user_id);
    
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social7 Creative Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> 
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
        
        <nav class="nav-menu"> 
            <a href="../Home/dashboard.php" class="nav-item"><i class="fa-solid fa-home"></i> Home</a>
            <a href="../Explore/explore.php" class="nav-item"><i class="fa-solid fa-compass"></i> Explore</a>
            <a href="../Groups/groups.php" class="nav-item"><i class="fa-solid fa-people-group"></i> Groups</a>
            <a href="../Events/events.php" class="nav-item"><i class="fa-solid fa-calendar-days"></i> Events</a>
            <a href="../Friends/AddFriends.php" class="nav-item"><i class="fa-solid fa-user-friends"></i> Friends</a>
            <a href="../Messages/message.php" class="nav-item active"><i class="fa-solid fa-message"></i> Messages</a>           
             
        </nav>

        <button class="btn-create">
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
                <div class="icon-btn"><i class="fa-solid fa-moon"></i></div>
                <div class="icon-btn" style="position: relative;">
                    <i class="fa-solid fa-bell"></i>
                    <span style="position: absolute; top: 12px; right: 12px; width: 8px; height: 8px; background: #ef4444; border-radius: 50%;"></span>
                </div>
                <div class="user-profile">
                    <span style="font-weight: 700;"><?php echo htmlspecialchars($u_d['Username']); ?></span>
                    <a href="../Profile/profile.php">
                        <img src="<?php 
                            $path = '../../../public/img/' . $u_d['ProfilePic'];
                            if (!empty($u_d['ProfilePic']) && file_exists($path)) {
                                echo $path;
                            } else {
                                echo 'https://via.placeholder.com/50'; // ដាក់រូប default បើអត់មានរូបពិត
                            }
                        ?>" alt="Profile">
                    </a>
                </div>
            </div>
        </header>

        <div class="chat-container">
            <div class="chat-sidebar">
                <!-- <div class="chat-search">
                    <input type="text" placeholder="Search messages...">
                </div> -->

                <div class="contact-list" id="user-list-container">
                    <div style="padding: 20px; text-align: center; color: #888;">Loading users...</div>
                </div>
            </div>

            <div id="chat-welcome" class="chat-main" style="display: flex; align-items: center; justify-content: center; flex-direction: column; opacity: 0.6;">
                <i class="fa-regular fa-comments" style="font-size: 60px; margin-bottom: 15px;"></i>
                <h3>Please select a chat to start a conversation.</h3>
            </div>
                
            <div id="chat-active" class="chat-main" style="display: none;">
                </div>
        </div>            
    </div>

    <script>
    const API_PATH = '../../../app/Controllers/Messages/ChatController.php';
    const CURRENT_USER_ID = <?php echo $_SESSION['user_id'] ?? 0; ?>;
</script>
    <script src="../../../public/js/Messages/message.js"></script> 
</body>
</html>