<?php
session_start();
require_once '../../../config/db_config.php';
require_once '../../Models/Profile/ProfileModel.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../Login/login_gmail.php');
    exit();
}

$model = new ProfileModel($conn);
$user_id = (int)$_SESSION['user_id'];
$u_d = $model->getUserData($user_id);

$allowedTypes = [
    'image/jpeg',
    'image/png',
    'image/jpg',
    'image/webp',
    'video/mp4',
    'video/webm',
    'video/ogg'
];

$tempDirAbs = __DIR__ . '/../../../public/uploads/stories/temp/';
$tempDirDb = '../../../public/uploads/stories/temp/';

if (!is_dir($tempDirAbs)) {
    mkdir($tempDirAbs, 0777, true);
}

$error = '';
$tempMediaDb = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['story_media'])) {
    $file = $_FILES['story_media'];

    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please choose an image or video first.';
    } elseif ($file['size'] > (25 * 1024 * 1024)) {
        $error = 'File is too large (max 25MB).';
    } else {
        $fileType = mime_content_type($file['tmp_name']);
        if (!in_array($fileType, $allowedTypes, true)) {
            $error = 'Unsupported file type.';
        } else {
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = 'temp_story_' . $user_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
            $targetAbs = $tempDirAbs . $filename;
            $targetDb = $tempDirDb . $filename;

            if (move_uploaded_file($file['tmp_name'], $targetAbs)) {
                $tempMediaDb = $targetDb;
            } else {
                $error = 'Unable to prepare the selected file.';
            }
        }
    }
} elseif (isset($_GET['temp'])) {
    $requested = basename((string)$_GET['temp']);
    if ($requested !== '') {
        $candidate = $tempDirAbs . $requested;
        if (is_file($candidate)) {
            $tempMediaDb = $tempDirDb . $requested;
        }
    }
}

if ($tempMediaDb === '' && $error === '') {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Story</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../public/css/Home/story_editor.css">
</head>
<body>
    <div class="editor-shell">
        <header class="editor-topbar">
            <a href="dashboard.php" class="topbar-btn"><i class="fa-solid fa-arrow-left"></i> Back</a>
            <h1>Story Editor</h1>
            <button id="post-story-btn" class="topbar-btn primary" type="button">Post Story</button>
        </header>

        <?php if ($error !== ''): ?>
            <div class="editor-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($tempMediaDb !== ''): ?>
            <main class="editor-main">
                <section class="editor-canvas-wrap">
                    <div class="editor-canvas" id="editor-canvas">
                        <?php
                        $mediaLower = strtolower($tempMediaDb);
                        $isVideo = str_ends_with($mediaLower, '.mp4') || str_ends_with($mediaLower, '.webm') || str_ends_with($mediaLower, '.ogg');
                        ?>
                        <?php if ($isVideo): ?>
                            <video class="editor-media-asset" src="<?php echo htmlspecialchars($tempMediaDb); ?>" controls autoplay muted loop playsinline></video>
                        <?php else: ?>
                            <img class="editor-media-asset" src="<?php echo htmlspecialchars($tempMediaDb); ?>" alt="Story preview">
                        <?php endif; ?>
                        <div id="overlay-preview" class="overlay-preview pos-bottom"></div>
                    </div>
                </section>

                <aside class="editor-panel">
                    <form id="story-editor-form" class="editor-form">
                        <input type="hidden" name="temp_media" value="<?php echo htmlspecialchars($tempMediaDb); ?>">
                        <input type="hidden" name="zoom_scale" id="zoom_scale" value="1">

                        <label for="overlay_text">Text on Story</label>
                        <textarea id="overlay_text" name="overlay_text" maxlength="140" placeholder="Write something..."></textarea>

                        <label for="overlay_position">Text Position</label>
                        <select id="overlay_position" name="overlay_position">
                            <option value="top">Top</option>
                            <option value="center">Center</option>
                            <option value="bottom" selected>Bottom</option>
                        </select>

                        <label for="zoom-range">Zoom</label>
                        <div class="zoom-controls">
                            <button type="button" id="zoom-out-btn" class="zoom-btn" aria-label="Zoom out">
                                <i class="fa-solid fa-minus"></i>
                            </button>
                            <input type="range" id="zoom-range" min="1" max="3" step="0.05" value="1">
                            <button type="button" id="zoom-in-btn" class="zoom-btn" aria-label="Zoom in">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                        <div id="zoom-value" class="zoom-value">100%</div>

                        <p id="editor-status" class="editor-status"></p>
                    </form>
                </aside>
            </main>
        <?php endif; ?>
    </div>

    <script>
        window.STORY_EDITOR_CONFIG = {
            api: '../../../app/Controllers/Home/StoryController.php',
            dashboardUrl: 'dashboard.php'
        };
    </script>
    <script src="../../../public/js/Home/story_editor.js"></script>
</body>
</html>
