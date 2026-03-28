<?php
    session_start();

    require_once '../../../config/db_config.php';

    require_once '../../Models/Profile/ProfileModel.php';
    // សុវត្ថិភាព
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../Login/login_gmail.php");
        exit();
    }

    $model = new ProfileModel($conn);
    $user_id = $_SESSION['user_id'];
    $u_d = $model->getUserData($user_id);
    $stats = $model->getProfileStats($user_id);

    require_once '../../Models/Events/EventModel.php';

    $eventModel = new EventModel($conn);
    $upcomingEvents = $eventModel->getUpcomingEvents();
    $myEvents = $eventModel->getEventsByUserId($user_id);
    $allEvents = $eventModel->getAllEvents();

    function formatEventDate($datetime) {
        $eventDate = new DateTime($datetime);
        $now = new DateTime();
        $interval = $now->diff($eventDate);

        if ($eventDate->format('Y-m-d') == $now->format('Y-m-d')) {
            return 'Today';
        }
        if ($eventDate->format('Y-m-d') == (new DateTime('+1 day'))->format('Y-m-d')) {
            return 'Tomorrow';
        }
        if ($interval->days < 7) {
            return $eventDate->format('l'); // e.g., Monday
        }
        return $eventDate->format('M j, Y'); // e.g., Mar 22, 2026
    }
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
    <title>Events</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/css/Events/events.css">
    <link rel="stylesheet" href="../../../public/css/Home/dashboard.css">
    <style>
        .icon-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #1e293b;
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.4);
            transition: all 0.25s ease;
            cursor: pointer;
            margin-left: 8px;
            box-sizing: border-box;
        }
        .icon-btn.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            transform: scale(1.07);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.25);
        }
        .tab {
            cursor: pointer;
            border-radius: 12px;
            color: #1e293b;
            background: rgba(255,255,255,0.7);
            transition: all 0.3s ease;
            padding: 10px 16px;
        }
        .tab.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
        }
        .events-list.hidden {
            display: none;
        }
        .dark-mode .icon-btn {
            background: rgba(255,255,255,0.12);
            color: #e2e8f0;
            border-color: rgba(226,232,240,0.3);
        }
        .dark-mode .icon-btn.active {
            background: linear-gradient(135deg, #f97316, #fb923c);
            color: #fff;
        }

        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(15, 23, 42, 0.95);
            color: #fff;
            padding: 10px 16px;
            border-radius: 10px;
            z-index: 9999;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.25s ease, transform 0.25s ease;
            pointer-events: none;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
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
            <a href="../Home/dashboard.php" class="item "><i class="fa-solid fa-home"></i> Home</a>
            <a href="../Explore/explore.php" class="item"><i class="fa-solid fa-compass"></i> Explore</a>
            <a href="../Groups/groups.php" class="item"><i class="fa-solid fa-people-group"></i> Groups</a>
            <a href="../Events/events.php" class="item active"><i class="fa-solid fa-calendar-days"></i> Events</a>
            <a href="../Friends/AllFriends.php" class="item"><i class="fa-solid fa-user-friends"></i> Friends</a>
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
                <div class="icon-btn notifications-btn" style="position: relative;">
                    <i class="fa-solid fa-bell"></i>
                    <span style="position: absolute; top: 12px; right: 12px; width: 8px; height: 8px; background: #ef4444; border-radius: 50%;"></span>
                </div>
                <div class="user-profile">
                    <span style="font-weight: 700;">Dara S.</span>
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
                
                <div class="events-container">
            <div id="toast" class="toast"></div>
            <div class="events-header">
                <h1>Events</h1>
                <a href="create_event.php" class="btn-primary"><i class="fa-solid fa-plus"></i> Create Event</a>
            </div>

            <div class="events-tabs">
                <div class="tab active">Upcoming Events</div>
                <div class="tab">My Events (<?php echo count($myEvents); ?>)</div>
            </div>

            <div id="upcoming-events" class="events-list">
                <?php if (empty($upcomingEvents) && empty($allEvents)): ?>
                    <p class="no-events-message" style="text-align: center; padding: 2rem;">No events found.</p>
                <?php endif; ?>

                <?php $eventsToRender = !empty($upcomingEvents) ? $upcomingEvents : $allEvents; ?>
                <?php foreach ($eventsToRender as $event): ?>
                    <div class="event-card">
                            <div class="event-image">
                                <img src="<?php echo !empty($event['ImagePath']) ? '../../../public/' . htmlspecialchars($event['ImagePath']) : 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?auto=format&fit=crop&w=800&q=80'; ?>" alt="<?php echo htmlspecialchars($event['Title']); ?>">
                            </div>
                            <div class="event-details">
                                <div class="event-info-top">
                                    <span class="tag health">Event</span>
                                    <span class="event-date-text"><?php echo formatEventDate($event['EventDateTime']); ?></span>
                                </div>
                                <h3><?php echo htmlspecialchars($event['Title']); ?></h3>
                                <p class="event-desc"><?php echo nl2br(htmlspecialchars($event['Description'])); ?></p>
                                
                                <div class="event-meta">
                                    <span><i class="fa-regular fa-clock"></i> <?php echo (new DateTime($event['EventDateTime']))->format('g:i A'); ?></span>
                                    <span><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($event['Location']); ?></span>
                                    <span><i class="fa-solid fa-user"></i> Created by <?php echo htmlspecialchars($event['Username']); ?></span>
                                </div>

                                <div class="event-actions">
                                    <button class="btn-status interested">Interested</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
            </div>

            <div id="my-events" class="events-list hidden">
                <?php if (empty($myEvents)): ?>
                    <p class="no-events-message" style="text-align: center; padding: 2rem;">You have no events yet.</p>
                <?php else: ?>
                    <?php foreach ($myEvents as $event): ?>
                        <div class="event-card">
                            <div class="event-image">
                                <img src="<?php echo !empty($event['ImagePath']) ? '../../../public/' . htmlspecialchars($event['ImagePath']) : 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?auto=format&fit=crop&w=800&q=80'; ?>" alt="<?php echo htmlspecialchars($event['Title']); ?>">
                            </div>
                            <div class="event-details">
                                <div class="event-info-top">
                                    <span class="tag health">My Event</span>
                                    <span class="event-date-text"><?php echo formatEventDate($event['EventDateTime']); ?></span>
                                </div>
                                <h3><?php echo htmlspecialchars($event['Title']); ?></h3>
                                <p class="event-desc"><?php echo nl2br(htmlspecialchars($event['Description'])); ?></p>
                                <div class="event-meta">
                                    <span><i class="fa-regular fa-clock"></i> <?php echo (new DateTime($event['EventDateTime']))->format('g:i A'); ?></span>
                                    <span><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($event['Location']); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>


        </div>
    </div>
    <?php require_once __DIR__ . '/../Component/create_post_modal.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const createPostBtn = document.querySelector('.btn-create');
            
            const themeBtn = document.querySelector('.theme-toggle');
            const bellBtn = document.querySelector('.notifications-btn');
            const themeIcon = themeBtn?.querySelector('i');

            const upcomingTab = document.querySelector('.events-tabs .tab:first-child');
            const myEventsTab = document.querySelector('.events-tabs .tab:last-child');
            const upcomingSection = document.getElementById('upcoming-events');
            const myEventsSection = document.getElementById('my-events');

            const THEME_KEY = 'localconnect_theme';

            function applyTheme(theme) {
                if (theme === 'dark') {
                    document.body.classList.add('dark-mode');
                    themeBtn?.classList.add('active');
                    themeIcon && (themeIcon.className = 'fa-solid fa-sun');
                } else {
                    document.body.classList.remove('dark-mode');
                    themeBtn?.classList.remove('active');
                    themeIcon && (themeIcon.className = 'fa-solid fa-moon');
                }
                localStorage.setItem(THEME_KEY, theme);
            }

            const savedTheme = localStorage.getItem(THEME_KEY);
            if (!savedTheme) {
                applyTheme('dark');
            } else {
                applyTheme(savedTheme);
            }

            if (themeBtn) {
                themeBtn.addEventListener('click', () => {
                    const newTheme = document.body.classList.contains('dark-mode') ? 'light' : 'dark';
                    applyTheme(newTheme);
                });
            }

            if (bellBtn) {
                bellBtn.addEventListener('click', () => {
                    bellBtn.classList.add('active');
                    setTimeout(() => bellBtn.classList.remove('active'), 350);
                    alert('មិនមានការជូនដំណឹងថ្មីទេ');
                });
            }

            function switchTab(tab) {
                if (tab === 'upcoming') {
                    upcomingTab?.classList.add('active');
                    myEventsTab?.classList.remove('active');
                    upcomingSection?.classList.remove('hidden');
                    myEventsSection?.classList.add('hidden');
                } else {
                    myEventsTab?.classList.add('active');
                    upcomingTab?.classList.remove('active');
                    myEventsSection?.classList.remove('hidden');
                    upcomingSection?.classList.add('hidden');
                }
            }

            if (upcomingTab) {
                upcomingTab.addEventListener('click', () => switchTab('upcoming'));
            }
            if (myEventsTab) {
                myEventsTab.addEventListener('click', () => switchTab('my'));
            }

            switchTab('upcoming');

            const toastEl = document.getElementById('toast');
            function showToast(message) {
                if (!toastEl) return;
                toastEl.textContent = message;
                toastEl.classList.add('show');
                setTimeout(() => toastEl.classList.remove('show'), 2200);
            }

            document.querySelectorAll('.btn-status.interested').forEach(button => {
                button.addEventListener('click', () => {
                    button.textContent = 'Interest Sent';
                    button.disabled = true;
                    button.style.background = 'linear-gradient(135deg, #10b981, #059669)';
                    showToast('Interest registered!');
                });
            });
        });
    </script>
    <script>
        const CURRENT_USER_ID = <?php echo (int)($_SESSION['user_id'] ?? 0); ?>;
        const CURRENT_USER_NAME = <?php echo json_encode((string)($u_d['Username'] ?? 'User')); ?>;
        const CURRENT_USER_AVATAR = <?php echo json_encode($profilePicPath); ?>;
    </script>
    <script src="../../../public/js/Home/dashboard.js"></script>
</body>
</html>
