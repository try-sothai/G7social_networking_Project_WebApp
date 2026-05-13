<?php
session_start();
require_once '../../../config/db_config.php';
require_once __DIR__ . '/../Component/dashboard_shell.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login_gmail.php");
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Dummy data for user profile - replace with actual data fetching
require_once '../../Models/Profile/ProfileModel.php';
require_once '../../Models/Events/EventModel.php';
$model = new ProfileModel($conn);
$user_id = $_SESSION['user_id'];
$u_d = $model->getUserData($user_id);
/** @var EventModel $eventModel */
$eventModel = new EventModel($conn);
$profilePicPath = '';
if (!empty($u_d['ProfilePic'])) {
    $candidate = __DIR__ . '/../../../public/uploads/avatars/' . $u_d['ProfilePic'];
    if (is_file($candidate)) {
        $profilePicPath = '../../../public/uploads/avatars/' . $u_d['ProfilePic'];
    }
}
if ($profilePicPath === '') {
    $profilePicPath = 'https://i.pravatar.cc/150?u=' . $user_id;
}

$createEventCssVersion = (string) (@filemtime(__DIR__ . '/../../../public/css/Events/create_event.css') ?: time());
$dashboardCssVersion = (string) (@filemtime(__DIR__ . '/../../../public/css/Home/dashboard.css') ?: time());
$dashboardJsVersion = (string) (@filemtime(__DIR__ . '/../../../public/js/Home/dashboard.js') ?: time());
$responsiveJsVersion = (string) (@filemtime(__DIR__ . '/../../../public/js/Home/responsive.js') ?: time());

$isEditMode = (isset($_GET['mode']) && $_GET['mode'] === 'edit');
$editingEvent = null;
if ($isEditMode) {
    $eventId = (int)($_GET['id'] ?? 0);
    if ($eventId > 0) {
        $editingEvent = $eventModel->getEventByIdForUser($eventId, $user_id);
    }
    if (!$editingEvent) {
        header('Location: events.php?error=not_found');
        exit();
    }
}

$alert = null;
if (isset($_GET['error'])) {
    $error = $_GET['error'];
    if ($error === 'missing_fields') {
        $alert = ['type' => 'error', 'text' => 'Please complete title, location and date/time.'];
    } elseif ($error === 'upload_failed') {
        $alert = ['type' => 'error', 'text' => 'Image upload failed. Please try again with a different file.'];
    } elseif ($error === 'db_error') {
        $alert = ['type' => 'error', 'text' => 'Unable to save event. Please try again later.'];
    } elseif ($error === 'past_date') {
        $alert = ['type' => 'error', 'text' => 'Event date and time cannot be in the past.'];
    }
} elseif (isset($_GET['success']) && $_GET['success'] === 'event_created') {
    $alert = ['type' => 'success', 'text' => 'Event created successfully!'];
} elseif (isset($_GET['success']) && $_GET['success'] === 'event_updated') {
    $alert = ['type' => 'success', 'text' => 'Event updated successfully!'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEditMode ? 'Edit Event' : 'Create New Event'; ?></title>
    <?php lc_shell_render_head_assets(); ?>    
    <link rel="stylesheet" href="../../../public/css/Home/dashboard.css?v=<?php echo $dashboardCssVersion; ?>">
    <link rel="stylesheet" href="../../../public/css/Events/create_event.css?v=<?php echo $createEventCssVersion; ?>">
    <script>
        window.APP_CONFIG = {
            csrfToken: '<?php echo $_SESSION['csrf_token']; ?>'
        };
    </script>
    <style>
        .alert-box {
            margin: 1rem 2rem 0;
            padding: 0.85rem 1rem;
            border-radius: 10px;
            font-weight: 600;
            color: #fff;
            box-shadow: 0 6px 12px rgba(0,0,0,0.13);
            max-width: calc(100% - 4rem);
        }
        .alert-box.success {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        .alert-box.error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
    </style>
</head>
<body>
    <?php lc_shell_render_sidebar(['active' => 'events']); ?>

    <div class="main-content">
        <?php if ($alert): ?>
            <div class="alert-box <?php echo $alert['type']; ?>">
                <?php echo htmlspecialchars($alert['text']); ?>
            </div>
        <?php endif; ?>
        <?php
            ob_start();
        ?>
            <i class="fa-solid fa-bell"></i>
            <span class="app-shell-notification-dot"></span>
        <?php
            $createEventNotificationHtml = ob_get_clean();
            lc_shell_render_header([
                'notification_html' => $createEventNotificationHtml,
                'user_name' => (string)$u_d['Username'],
                'profile_image' => $profilePicPath,
            ]);
        ?>

        <div class="create-event-container">
            <h1><?php echo $isEditMode ? 'Edit Event' : 'Create New Event'; ?></h1>
            <form action="../../Controllers/Events/EventController.php" method="POST" class="create-event-form" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $isEditMode ? 'update' : 'create'; ?>">
                <?php if ($isEditMode): ?>
                    <input type="hidden" name="event_id" value="<?php echo (int)($editingEvent['EventID'] ?? $editingEvent['id'] ?? $editingEvent['ID'] ?? 0); ?>">
                <?php endif; ?>
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div class="form-group">
                    <label for="title">Event Title</label>
                    <input type="text" id="title" name="title" placeholder="e.g., Community Yoga in the Park" value="<?php echo htmlspecialchars($editingEvent['Title'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" placeholder="Tell everyone about your event..."><?php echo htmlspecialchars($editingEvent['Description'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" placeholder="e.g., Golden Gate Park, San Francisco" value="<?php echo htmlspecialchars($editingEvent['Location'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="event_datetime">Date and Time</label>
                    <input type="datetime-local" id="event_datetime" name="event_datetime" value="<?php echo !empty($editingEvent['EventDateTime']) ? htmlspecialchars(date('Y-m-d\TH:i', strtotime($editingEvent['EventDateTime']))) : ''; ?>" min="<?php echo date('Y-m-d\TH:i'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="event_image">Event Image</label>
                    <input type="file" id="event_image" name="event_image" accept="image/*">
                </div>

                <div class="form-actions">
                    <a href="events.php" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-submit"><?php echo $isEditMode ? 'Update Event' : 'Create Event'; ?></button>
                </div>
            </form>
        </div>
    </div>

    <?php lc_shell_render_overlay(); ?>
    <?php require_once __DIR__ . '/../Component/create_post_modal.php'; ?>
    <?php lc_shell_render_user_bootstrap((int)($_SESSION['user_id'] ?? 0), (string)($u_d['Username'] ?? 'User'), $profilePicPath); ?>
    <script src="../../../public/js/Home/dashboard.js?v=<?php echo $dashboardJsVersion; ?>"></script>
    <script src="../../../public/js/common/theme-toggle.js"></script>
    <script src="../../../public/js/Home/responsive.js?v=<?php echo $responsiveJsVersion; ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alertBox = document.querySelector('.alert-box');
            if (alertBox) {
                setTimeout(() => {
                    alertBox.style.transform = 'translateY(-24px)';
                    alertBox.style.opacity = '0';
                }, 3500);
                setTimeout(() => {
                    alertBox && alertBox.remove();
                }, 4500);
            }
        });
    </script>
</body>
</html>
