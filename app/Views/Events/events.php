<?php
    session_start();

    require_once '../../../config/db_config.php';

    require_once '../../Models/Profile/ProfileModel.php';
    require_once '../../Models/Friends/FriendModel.php';
    require_once __DIR__ . '/../Component/dashboard_shell.php';
    // Security check
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../Login/login_gmail.php");
        exit();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $model = new ProfileModel($conn);
    $user_id = $_SESSION['user_id'];
    $u_d = $model->getUserData($user_id);
    $stats = $model->getProfileStats($user_id);
    $friendModel = new FriendModel($conn);
    $unread_notifications = $friendModel->getUnreadNotificationsCount($user_id);

    require_once '../../Models/Events/EventModel.php';

    $eventModel = new EventModel($conn);
    $upcomingEvents = $eventModel->getUpcomingEvents($user_id);
    $myEvents = $eventModel->getEventsByUserId($user_id, $user_id);
    $allEvents = $eventModel->getAllEvents($user_id);
    $eventFlash = '';
    if (isset($_GET['success']) && $_GET['success'] === 'event_created') {
        $eventFlash = 'Event created successfully.';
    } elseif (isset($_GET['success']) && $_GET['success'] === 'event_updated') {
        $eventFlash = 'Event updated successfully.';
    } elseif (isset($_GET['success']) && $_GET['success'] === 'event_deleted') {
        $eventFlash = 'Event deleted successfully.';
    } elseif (isset($_GET['error'])) {
        $eventFlash = 'Unable to process event request.';
    }

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

    function resolveEventImageUrl($imagePath) {
        $raw = trim(str_replace('\\', '/', (string)$imagePath));
        $fallback = 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?auto=format&fit=crop&w=800&q=80';

        if ($raw === '') {
            return $fallback;
        }

        if (str_starts_with($raw, 'data:')) {
            return $raw;
        }

        if (preg_match('#^(https?:)?//#i', $raw)) {
            $urlParts = parse_url($raw);
            $pathPart = (string)($urlParts['path'] ?? '');
            if ($pathPart !== '' && str_contains($pathPart, '/public/')) {
                $parts = explode('/public/', $pathPart, 2);
                $raw = $parts[1] ?? $pathPart;
            } else {
                return $raw;
            }
        }

        if (str_contains($raw, '/public/')) {
            $parts = explode('/public/', $raw, 2);
            $raw = $parts[1] ?? $raw;
        }

        $normalized = ltrim($raw, '/');
        $candidates = [$normalized];

        if (str_starts_with($normalized, 'events/')) {
            $candidates[] = 'uploads/' . $normalized;
        }

        if ($normalized !== '' && !str_contains($normalized, '/')) {
            $candidates[] = 'uploads/events/' . $normalized;
        }

        foreach (array_unique(array_filter($candidates)) as $candidate) {
            $absolutePath = __DIR__ . '/../../../public/' . $candidate;
            if (is_file($absolutePath)) {
                return '../../../public/' . $candidate;
            }
        }

        return $fallback;
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
$eventsCssVersion = (string) (@filemtime(__DIR__ . '/../../../public/css/Events/events.css') ?: time());
$dashboardCssVersion = (string) (@filemtime(__DIR__ . '/../../../public/css/Home/dashboard.css') ?: time());
$dashboardJsVersion = (string) (@filemtime(__DIR__ . '/../../../public/js/Home/dashboard.js') ?: time());
$responsiveJsVersion = (string) (@filemtime(__DIR__ . '/../../../public/js/Home/responsive.js') ?: time());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social7 Events</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/css/Events/events.css?v=<?php echo $eventsCssVersion; ?>">
    <link rel="stylesheet" href="../../../public/css/Home/dashboard.css?v=<?php echo $dashboardCssVersion; ?>">
    <script>
        window.APP_CONFIG = {
            csrfToken: '<?php echo $_SESSION['csrf_token']; ?>'
        };
    </script>
    <style>
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

    <?php lc_shell_render_sidebar(['active' => 'events']); ?>

    <div class="main-content">
        <?php if ($eventFlash !== ''): ?>
            <div style="margin: 12px 16px; padding: 10px 12px; border-radius: 10px; background: #0f172a; color: #fff;">
                <?php echo htmlspecialchars($eventFlash); ?>
            </div>
        <?php endif; ?>
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
            $eventsNotificationHtml = ob_get_clean();
            lc_shell_render_header([
                'search_input_id' => 'events-search-input',
                'notification_html' => $eventsNotificationHtml,
                'notification_button_id' => 'notificationBtn',
                'user_name' => (string)$u_d['Username'],
                'profile_image' => $profilePicPath,
            ]);
        ?>
                
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
                                <img src="<?php echo htmlspecialchars(resolveEventImageUrl($event['ImagePath'] ?? '')); ?>" alt="<?php echo htmlspecialchars($event['Title']); ?>">
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
                                    <?php 
                                        $eventId = (int)($event['EventID'] ?? $event['id'] ?? $event['ID'] ?? 0);
                                        $isInterested = !empty($event['is_interested']) && $event['is_interested'] > 0;
                                        $interestedCount = (int)($event['interested_count'] ?? 0);
                                    ?>
                                    <button class="btn-status interested <?php echo $isInterested ? 'active' : ''; ?>" onclick="toggleInterest(<?php echo $eventId; ?>, this)" <?php echo $isInterested ? 'style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; border-color: #10b981;"' : ''; ?>>
                                        <?php if($isInterested): ?>
                                            <i class="fa-solid fa-star"></i> Interested
                                        <?php else: ?>
                                            <i class="fa-regular fa-star"></i> Interest
                                        <?php endif; ?>
                                    </button>
                                    <span style="align-self: center; font-size: 14px; color: var(--text-muted); margin-left: 8px;">
                                        <?php echo $interestedCount; ?> interested
                                    </span>
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
                        <?php $eventId = (int)($event['EventID'] ?? $event['id'] ?? $event['ID'] ?? 0); ?>
                        <div class="event-card">
                            <div class="event-image">
                                <img src="<?php echo htmlspecialchars(resolveEventImageUrl($event['ImagePath'] ?? '')); ?>" alt="<?php echo htmlspecialchars($event['Title']); ?>">
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
                                <?php if ($eventId > 0): ?>
                                    <div class="event-actions" style="display:flex; gap:8px; margin-top:12px;">
                                        <a href="create_event.php?mode=edit&id=<?php echo $eventId; ?>" class="btn-status interested" style="text-decoration:none; display:inline-flex; align-items:center;">Edit</a>
                                        <form action="../../Controllers/Events/EventController.php" method="POST" onsubmit="return confirm('Delete this event?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="event_id" value="<?php echo $eventId; ?>">
                                            <button type="submit" class="btn-status" style="background:#ef4444; color:#fff;">Delete</button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>


        </div>
    </div>
    <?php lc_shell_render_overlay(); ?>
    <?php require_once __DIR__ . '/../Component/create_post_modal.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const bellBtn = document.querySelector('.notifications-btn');

            const upcomingTab = document.querySelector('.events-tabs .tab:first-child');
            const myEventsTab = document.querySelector('.events-tabs .tab:last-child');
            const upcomingSection = document.getElementById('upcoming-events');
            const myEventsSection = document.getElementById('my-events');

            if (bellBtn) {
                bellBtn.addEventListener('click', () => {
                    bellBtn.classList.add('active');
                    setTimeout(() => bellBtn.classList.remove('active'), 350);
                    alert('No new notifications.');
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
            
            // Search functionality for Events (Real-time filter)
            const searchInput = document.getElementById('events-search-input');
            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    const term = e.target.value.toLowerCase().trim();
                    const cards = document.querySelectorAll('.events-list .event-card');
                    
                    cards.forEach(card => {
                        const textContent = card.innerText.toLowerCase();
                        card.style.display = textContent.includes(term) ? '' : 'none';
                    });
                });
                
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                    }
                });
            }

            const toastEl = document.getElementById('toast');
            function showToast(message) {
                if (!toastEl) return;
                toastEl.textContent = message;
                toastEl.classList.add('show');
                setTimeout(() => toastEl.classList.remove('show'), 2200);
            }
        });

        function toggleInterest(eventId, buttonElement) {
            const formData = new FormData();
            formData.append('action', 'toggle_interest');
            formData.append('event_id', eventId);
            
            fetch('../../Controllers/Events/EventController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    if (buttonElement.classList.contains('active')) {
                        buttonElement.classList.remove('active');
                        buttonElement.innerHTML = '<i class="fa-regular fa-star"></i> Interest';
                        buttonElement.style.background = '';
                        buttonElement.style.color = '';
                        buttonElement.style.borderColor = '';
                    } else {
                        buttonElement.classList.add('active');
                        buttonElement.innerHTML = '<i class="fa-solid fa-star"></i> Interested';
                        buttonElement.style.background = 'linear-gradient(135deg, #10b981, #059669)';
                        buttonElement.style.color = '#fff';
                        buttonElement.style.borderColor = '#10b981';
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
    <?php lc_shell_render_user_bootstrap((int)($_SESSION['user_id'] ?? 0), (string)($u_d['Username'] ?? 'User'), $profilePicPath); ?>
    <script src="../../../public/js/common/theme-toggle.js"></script>
    <script src="../../../public/js/Home/responsive.js?v=<?php echo $responsiveJsVersion; ?>"></script>
    <script src="../../../public/js/Home/dashboard.js?v=<?php echo $dashboardJsVersion; ?>"></script>
</body>
</html>
