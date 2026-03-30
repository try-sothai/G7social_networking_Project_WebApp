<?php
session_start();
require_once '../../../config/db_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login_gmail.php");
    exit();
}

// Dummy data for user profile - replace with actual data fetching
require_once '../../Models/Profile/ProfileModel.php';
require_once '../../Models/Events/EventModel.php';
$model = new ProfileModel($conn);
$user_id = $_SESSION['user_id'];
$u_d = $model->getUserData($user_id);
$eventModel = new EventModel($conn);

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/css/Home/dashboard.css">
    <link rel="stylesheet" href="../../../public/css/Events/create_event.css">
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
    <div class="sidebar">
        <div class="logo-section">
            <i class="fa-solid fa-cube"></i>
            <span>LocalConnect</span>
        </div>
        
        <nav class="menu">
            <a href="../Home/dashboard.php" class="item"><i class="fa-solid fa-home"></i> Home</a>
            <a href="../Explore/explore.php" class="item"><i class="fa-solid fa-compass"></i> Explore</a>
            <a href="../Groups/groups.php" class="item"><i class="fa-solid fa-people-group"></i> Groups</a>
            <a href="events.php" class="item active"><i class="fa-solid fa-calendar-days"></i> Events</a>
            <a href="../Friends/AllFriends.php" class="item"><i class="fa-solid fa-user-friends"></i> Friends</a>
            <a href="../Messages/message.php" class="item"><i class="fa-solid fa-message"></i> Messages</a>
            <a href="../Report/userReport.php" class="item"><i class="fa-solid fa-chart-simple"></i> Report</a>
        </nav>

        <button class="btn-create">
            <i class="fa-solid fa-plus-circle"></i>
            <span>Create New Post</span>
        </button>
    </div>

    <div class="main-content">
        <?php if ($alert): ?>
            <div class="alert-box <?php echo $alert['type']; ?>">
                <?php echo htmlspecialchars($alert['text']); ?>
            </div>
        <?php endif; ?>
        <header>
            <button class="icon-btn menu-toggle"><i class="fa-solid fa-bars"></i></button>
            <div class="search-bar">
                <i class="fa-solid fa-search text-muted"></i>
                <input type="text" placeholder="Search for anything...">
            </div>
            
            <div class="header-actions">
                <div class="icon-btn search-toggle-btn"><i class="fa-solid fa-search"></i></div>
                <div class="icon-btn theme-toggle"><i class="fa-solid fa-moon"></i></div>
                <div class="icon-btn" style="position: relative;">
                    <i class="fa-solid fa-bell"></i>
                    <span style="position: absolute; top: 12px; right: 12px; width: 8px; height: 8px; background: #ef4444; border-radius: 50%;"></span>
                </div>
                <div class="user-profile">
                <span style="font-weight: 700;"><?php echo htmlspecialchars($u_d['Username']); ?></span>
                    <a href="../Profile/profile.php">
                        <img src="<?php 
                            if (!empty($u_d['ProfilePic']) && file_exists('../../../public/img/' . $u_d['ProfilePic'])) {
                                echo '../../../public/img/' . $u_d['ProfilePic'];
                            } else {
                                echo 'https://i.pravatar.cc/150?u=' . $user_id;
                            }
                        ?>" alt="Profile">
                    </a>
                </div>
            </div>
        </header>

        <div class="create-event-container">
            <h1><?php echo $isEditMode ? 'Edit Event' : 'Create New Event'; ?></h1>
            <form action="../../Controllers/Events/EventController.php" method="POST" class="create-event-form" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $isEditMode ? 'update' : 'create'; ?>">
                <?php if ($isEditMode): ?>
                    <input type="hidden" name="event_id" value="<?php echo (int)($editingEvent['EventID'] ?? $editingEvent['id'] ?? $editingEvent['ID'] ?? 0); ?>">
                <?php endif; ?>

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
                    <input type="datetime-local" id="event_datetime" name="event_datetime" value="<?php echo !empty($editingEvent['EventDateTime']) ? htmlspecialchars(date('Y-m-d\TH:i', strtotime($editingEvent['EventDateTime']))) : ''; ?>" required>
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

    <div class="overlay"></div>
    <script src="../../../public/js/common/theme-toggle.js"></script>
    <script src="../../../public/js/Home/responsive.js"></script>
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
