<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../config/db_config.php';
require_once __DIR__ . '/../../Models/Home/PostModel.php';

$current_user_id = (int)($_SESSION['user_id'] ?? 0);
if (!$current_user_id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$postModel = new PostModel($conn);
$action = $_GET['action'] ?? $_POST['action'] ?? '';

$uploadDirAbs = __DIR__ . '/../../../public/uploads/posts/';
$uploadDirDb = '../../../public/uploads/posts/';

function ensureDirExists(string $path): void {
    if (!is_dir($path)) {
        mkdir($path, 0777, true);
    }
}

function sanitizePostContent(string $content): string {
    $clean = trim(strip_tags($content));
    return mb_substr($clean, 0, 800, 'UTF-8');
}

function isAllowedPostMime(string $tmpPath): bool {
    $allowed = [
        'image/jpeg',
        'image/png',
        'image/jpg',
        'image/webp',
        'video/mp4',
        'video/webm',
        'video/ogg',
        'video/quicktime'
    ];
    $type = mime_content_type($tmpPath);
    return in_array($type, $allowed, true);
}

function isVideoMime(string $tmpPath): bool {
    $type = (string)mime_content_type($tmpPath);
    return str_starts_with($type, 'video/');
}

function getMaxPostFileSize(string $tmpPath): int {
    return isVideoMime($tmpPath) ? (100 * 1024 * 1024) : (15 * 1024 * 1024);
}

function ensurePostViewsTable(PDO $conn): void {
    $conn->exec("
        CREATE TABLE IF NOT EXISTS post_views (
            ViewID INT AUTO_INCREMENT PRIMARY KEY,
            PostID INT NOT NULL,
            UserID INT NOT NULL,
            ViewedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_post_viewer (PostID, UserID)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

function normalizeUploadedMedia(array $fileField): array {
    if (!isset($fileField['name'])) {
        return [];
    }

    if (!is_array($fileField['name'])) {
        if ((int)($fileField['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            return [[
                'name' => (string)($fileField['name'] ?? ''),
                'tmp_name' => (string)($fileField['tmp_name'] ?? ''),
                'size' => (int)($fileField['size'] ?? 0),
                'error' => (int)($fileField['error'] ?? UPLOAD_ERR_NO_FILE),
            ]];
        }
        return [];
    }

    $files = [];
    $count = count($fileField['name']);
    for ($i = 0; $i < $count; $i++) {
        $error = (int)($fileField['error'][$i] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            continue;
        }

        $files[] = [
            'name' => (string)($fileField['name'][$i] ?? ''),
            'tmp_name' => (string)($fileField['tmp_name'][$i] ?? ''),
            'size' => (int)($fileField['size'][$i] ?? 0),
            'error' => $error,
        ];
    }

    return $files;
}

function parseMediaList($imagePathValue): array {
    $raw = trim((string)$imagePathValue);
    if ($raw === '') {
        return [];
    }

    if (str_starts_with($raw, '[') && str_ends_with($raw, ']')) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return array_values(array_filter(array_map('strval', $decoded), fn($v) => trim($v) !== ''));
        }
    }

    return [$raw];
}

function safeMediaPath(string $uploadDirDb, string $mediaPath): string {
    $base = basename(str_replace('\\', '/', $mediaPath));
    return rtrim($uploadDirDb, '/\\') . '/' . $base;
}

function deleteMediaFiles(string $uploadDirAbs, array $mediaPaths): void {
    foreach ($mediaPaths as $path) {
        $base = basename(str_replace('\\', '/', (string)$path));
        if ($base === '') {
            continue;
        }
        $abs = rtrim($uploadDirAbs, '/\\') . DIRECTORY_SEPARATOR . $base;
        if (is_file($abs)) {
            @unlink($abs);
        }
    }
}

function mediaListToDbValue(array $mediaList): string {
    if (count($mediaList) === 1) {
        return $mediaList[0];
    }
    return json_encode(array_values($mediaList), JSON_UNESCAPED_SLASHES);
}

function uploadMediaFiles(array $uploadedFiles, int $current_user_id, string $uploadDirAbs, string $uploadDirDb): array {
    $savedMedia = [];
    foreach ($uploadedFiles as $file) {
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($extension === '') {
            $extension = 'jpg';
        }

        $filename = 'post_' . $current_user_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $targetAbs = $uploadDirAbs . $filename;
        $targetDb = $uploadDirDb . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetAbs)) {
            deleteMediaFiles($uploadDirAbs, $savedMedia);
            return ['ok' => false, 'paths' => []];
        }

        $savedMedia[] = $targetDb;
    }

    return ['ok' => true, 'paths' => $savedMedia];
}

if ($action === 'fetch') {
    try {
        ensurePostViewsTable($conn);
        $posts = $postModel->getRecentPosts(25);
        if (is_array($posts)) {
            foreach ($posts as &$post) {
                $postId = (int)$post['PostID'];
                try {
                    // Likes count
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM post_likes WHERE PostID = ?");
                    $stmt->execute([$postId]);
                    $post['LikesCount'] = (int)$stmt->fetchColumn();
                    
                    // Has liked
                    $stmt = $conn->prepare("SELECT 1 FROM post_likes WHERE PostID = ? AND UserID = ?");
                    $stmt->execute([$postId, $current_user_id]);
                    $post['IsLiked'] = (bool)$stmt->fetchColumn();
                    
                    // Comments count
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM post_comments WHERE PostID = ?");
                    $stmt->execute([$postId]);
                    $post['CommentsCount'] = (int)$stmt->fetchColumn();
                    
                    // Shares count
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM post_shares WHERE PostID = ?");
                    $stmt->execute([$postId]);
                    $post['SharesCount'] = (int)$stmt->fetchColumn();

                    // Views count
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM post_views WHERE PostID = ?");
                    $stmt->execute([$postId]);
                    $post['ViewsCount'] = (int)$stmt->fetchColumn();
                    
                    // Fetch comments
                    $stmt = $conn->prepare("SELECT c.CommentText, u.Username, u.ProfilePic FROM post_comments c JOIN users u ON c.UserID = u.UserID WHERE c.PostID = ? ORDER BY c.CreatedAt ASC");
                    $stmt->execute([$postId]);
                    $post['Comments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    // Ignore if tables don't exist yet
                }
            }
        }
        ob_clean(); // Prevent extra space/errors from breaking JSON
        echo json_encode(['status' => 'success', 'posts' => $posts]);
    } catch (PDOException $e) {
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = sanitizePostContent((string)($_POST['content'] ?? ''));
    $uploadedFiles = isset($_FILES['post_media']) ? normalizeUploadedMedia($_FILES['post_media']) : [];
    $savedMedia = [];
    $mediaValue = '';

    if (count($uploadedFiles) > 4) {
        echo json_encode(['status' => 'error', 'message' => 'You can upload up to 4 media files per post.']);
        exit;
    }

    foreach ($uploadedFiles as $file) {
        if ($file['tmp_name'] === '' || !isAllowedPostMime($file['tmp_name'])) {
            echo json_encode(['status' => 'error', 'message' => 'Only JPG, PNG, WEBP, MP4, WEBM, OGG, and MOV files are allowed.']);
            exit;
        }

        if ($file['size'] > getMaxPostFileSize($file['tmp_name'])) {
            echo json_encode(['status' => 'error', 'message' => 'Images must be 15MB or less, and videos must be 100MB or less.']);
            exit;
        }
    }

    if ($content === '' && count($uploadedFiles) === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Please add text or at least one media file.']);
        exit;
    }

    if (count($uploadedFiles) > 0) {
        ensureDirExists($uploadDirAbs);
        $uploaded = uploadMediaFiles($uploadedFiles, $current_user_id, $uploadDirAbs, $uploadDirDb);
        if (!$uploaded['ok']) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload media.']);
            exit;
        }

        $savedMedia = $uploaded['paths'];
        $mediaValue = mediaListToDbValue($savedMedia);
        if (strlen($mediaValue) > 255) {
            deleteMediaFiles($uploadDirAbs, $savedMedia);
            echo json_encode(['status' => 'error', 'message' => 'Too many media files selected. Please upload fewer files.']);
            exit;
        }
    }

    try {
        $postModel->createPost($current_user_id, $content, $mediaValue);
        echo json_encode(['status' => 'success', 'message' => 'Post published successfully.']);
    } catch (PDOException $e) {
        deleteMediaFiles($uploadDirAbs, $savedMedia);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'like' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = (int)($_POST['post_id'] ?? 0);
    if ($postId > 0) {
        try {
            $stmt = $conn->prepare("SELECT LikeID FROM post_likes WHERE PostID = ? AND UserID = ?");
            $stmt->execute([$postId, $current_user_id]);
            if ($stmt->fetch()) {
                $conn->prepare("DELETE FROM post_likes WHERE PostID = ? AND UserID = ?")->execute([$postId, $current_user_id]);
                echo json_encode(['status' => 'success', 'liked' => false]);
            } else {
                $conn->prepare("INSERT INTO post_likes (PostID, UserID) VALUES (?, ?)")->execute([$postId, $current_user_id]);
                echo json_encode(['status' => 'success', 'liked' => true]);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
        }
    }
    exit;
}

if ($action === 'comment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = (int)($_POST['post_id'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    if ($postId > 0 && $comment !== '') {
        try {
            $stmt = $conn->prepare("INSERT INTO post_comments (PostID, UserID, CommentText) VALUES (?, ?, ?)");
            $stmt->execute([$postId, $current_user_id, $comment]);
            echo json_encode(['status' => 'success']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Empty comment']);
    }
    exit;
}

    if ($action === 'share' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = (int)($_POST['post_id'] ?? 0);
    if ($postId > 0) {
        try {
            $stmt = $conn->prepare("SELECT * FROM posts WHERE PostID = ?");
            $stmt->execute([$postId]);
            $postToShare = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($postToShare) {
                $newContent = "🔄 Shared a post:\n\n" . $postToShare['Content'];
                $imagePath = $postToShare['ImagePath'];
                $postModel->createPost($current_user_id, $newContent, $imagePath);
                $conn->prepare("INSERT INTO post_shares (PostID, UserID) VALUES (?, ?)")->execute([$postId, $current_user_id]);
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Post not found']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    exit;
}

if ($action === 'view' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = (int)($_POST['post_id'] ?? 0);
    if ($postId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid post.']);
        exit;
    }

    try {
        ensurePostViewsTable($conn);

        $stmt = $conn->prepare("INSERT IGNORE INTO post_views (PostID, UserID) VALUES (?, ?)");
        $stmt->execute([$postId, $current_user_id]);

        $countStmt = $conn->prepare("SELECT COUNT(*) FROM post_views WHERE PostID = ?");
        $countStmt->execute([$postId]);
        $viewsCount = (int)$countStmt->fetchColumn();

        echo json_encode(['status' => 'success', 'views' => $viewsCount]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
    exit;
}

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = (int)($_POST['post_id'] ?? 0);
    if ($postId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid post.']);
        exit;
    }

    $post = $postModel->getPostByIdForUser($postId, $current_user_id);
    if (!$post) {
        echo json_encode(['status' => 'error', 'message' => 'Post not found.']);
        exit;
    }

    $existingMedia = parseMediaList($post['ImagePath'] ?? '');
    $keepRaw = (string)($_POST['keep_media'] ?? '[]');
    $keepDecoded = json_decode($keepRaw, true);
    $keepMedia = [];
    if (is_array($keepDecoded)) {
        foreach ($keepDecoded as $item) {
            $safe = safeMediaPath($uploadDirDb, (string)$item);
            if (in_array($safe, $existingMedia, true)) {
                $keepMedia[] = $safe;
            }
        }
    }
    $keepMedia = array_values(array_unique($keepMedia));

    $newFiles = isset($_FILES['new_media']) ? normalizeUploadedMedia($_FILES['new_media']) : [];
    foreach ($newFiles as $file) {
        if ($file['tmp_name'] === '' || !isAllowedPostMime($file['tmp_name'])) {
            echo json_encode(['status' => 'error', 'message' => 'Only JPG, PNG, WEBP, MP4, WEBM, OGG, and MOV files are allowed.']);
            exit;
        }
        if ($file['size'] > getMaxPostFileSize($file['tmp_name'])) {
            echo json_encode(['status' => 'error', 'message' => 'Images must be 15MB or less, and videos must be 100MB or less.']);
            exit;
        }
    }

    $content = sanitizePostContent((string)($_POST['content'] ?? ''));

    if (count($keepMedia) + count($newFiles) === 0 && $content === '') {
        echo json_encode(['status' => 'error', 'message' => 'Please add text or at least one media file.']);
        exit;
    }
    if (count($keepMedia) + count($newFiles) > 4) {
        echo json_encode(['status' => 'error', 'message' => 'You can keep up to 4 media files per post.']);
        exit;
    }

    ensureDirExists($uploadDirAbs);
    $uploaded = uploadMediaFiles($newFiles, $current_user_id, $uploadDirAbs, $uploadDirDb);
    if (!$uploaded['ok']) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to upload media.']);
        exit;
    }
    $newSavedMedia = $uploaded['paths'];

    $finalMedia = array_values(array_merge($keepMedia, $newSavedMedia));
    $mediaValue = mediaListToDbValue($finalMedia);
    if (strlen($mediaValue) > 255) {
        deleteMediaFiles($uploadDirAbs, $newSavedMedia);
        echo json_encode(['status' => 'error', 'message' => 'Too many media files selected. Please upload fewer files.']);
        exit;
    }

    try {
        $postModel->updatePostForUser($postId, $current_user_id, $content, $mediaValue);

        $removed = array_values(array_diff($existingMedia, $keepMedia));
        deleteMediaFiles($uploadDirAbs, $removed);

        echo json_encode(['status' => 'success', 'message' => 'Post updated successfully.']);
    } catch (PDOException $e) {
        deleteMediaFiles($uploadDirAbs, $newSavedMedia);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = (int)($_POST['post_id'] ?? 0);
    if ($postId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid post.']);
        exit;
    }

    $post = $postModel->getPostByIdForUser($postId, $current_user_id);
    if (!$post) {
        echo json_encode(['status' => 'error', 'message' => 'Post not found.']);
        exit;
    }

    try {
        $deleted = $postModel->deletePostForUser($postId, $current_user_id);
        if ($deleted) {
            $mediaList = parseMediaList($post['ImagePath'] ?? '');
            deleteMediaFiles($uploadDirAbs, $mediaList);
        }
        echo json_encode(['status' => 'success', 'deleted' => $deleted]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
