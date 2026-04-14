<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . "/../../../config/db_config.php";
require_once __DIR__ . "/../../Models/Home/StoryModel.php";

function ensureDir(string $path): void {
    if (!is_dir($path)) {
        mkdir($path, 0777, true);
    }
}

function sanitizeOverlayText(string $value): string {
    $clean = trim(strip_tags($value));
    if ($clean === '') return '';
    return mb_substr($clean, 0, 140, 'UTF-8');
}

function sanitizeOverlayPosition(string $value): string {
    $v = strtolower(trim($value));
    if (in_array($v, ['top', 'center', 'bottom'], true)) {
        return $v;
    }
    return 'bottom';
}

function sanitizeZoomScale(float $value): float {
    $zoom = (float)$value;
    if ($zoom <= 0) {
        $zoom = 1.0;
    }
    if ($zoom < 1.0) $zoom = 1.0;
    if ($zoom > 3.0) $zoom = 3.0;
    return round($zoom, 2);
}

function buildMetaPath(string $metaDirAbs, string $storyMediaPath): string {
    $base = basename(str_replace('\\', '/', $storyMediaPath));
    return rtrim($metaDirAbs, '/\\') . DIRECTORY_SEPARATOR . $base . '.json';
}

function readOverlayMeta(string $metaDirAbs, string $storyMediaPath): array {
    $metaPath = buildMetaPath($metaDirAbs, $storyMediaPath);
    if (!is_file($metaPath)) {
        return ['text' => '', 'position' => 'bottom', 'zoom' => 1.0];
    }

    $raw = @file_get_contents($metaPath);
    if (!is_string($raw) || $raw === '') {
        return ['text' => '', 'position' => 'bottom', 'zoom' => 1.0];
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return ['text' => '', 'position' => 'bottom', 'zoom' => 1.0];
    }

    return [
        'text' => sanitizeOverlayText((string)($decoded['text'] ?? '')),
        'position' => sanitizeOverlayPosition((string)($decoded['position'] ?? 'bottom')),
        'zoom' => sanitizeZoomScale($decoded['zoom'] ?? 1)
    ];
}

function writeOverlayMeta(string $metaDirAbs, string $storyMediaPath, string $text, string $position, float $zoom): void {
    $text = sanitizeOverlayText($text);
    $position = sanitizeOverlayPosition($position);
    $zoom = sanitizeZoomScale($zoom);

    if ($text === '' && $position === 'bottom' && abs($zoom - 1.0) < 0.0001) {
        return;
    }

    ensureDir($metaDirAbs);
    $metaPath = buildMetaPath($metaDirAbs, $storyMediaPath);
    $payload = [
        'text' => $text,
        'position' => $position,
        'zoom' => $zoom
    ];
    @file_put_contents($metaPath, json_encode($payload, JSON_UNESCAPED_UNICODE));
}

function removeOverlayMeta(string $metaDirAbs, string $storyMediaPath): void {
    $metaPath = buildMetaPath($metaDirAbs, $storyMediaPath);
    if (is_file($metaPath)) {
        @unlink($metaPath);
    }
}

function isAllowedMime(string $tmpPath): bool {
    $allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/jpg',
        'image/webp',
        'video/mp4',
        'video/webm',
        'video/ogg'
    ];

    $type = mime_content_type($tmpPath);
    return in_array($type, $allowedTypes, true);
}

function moveTempToFinal(string $tempAbs, string $uploadDirAbs, string $uploadDirDb, int $userId): array {
    ensureDir($uploadDirAbs);

    $extension = strtolower(pathinfo($tempAbs, PATHINFO_EXTENSION));
    $finalName = 'story_' . $userId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $finalAbs = rtrim($uploadDirAbs, '/\\') . DIRECTORY_SEPARATOR . $finalName;
    $finalDb = rtrim($uploadDirDb, '/\\') . '/' . $finalName;

    if (!@rename($tempAbs, $finalAbs)) {
        if (!@copy($tempAbs, $finalAbs)) {
            return ['ok' => false, 'db' => '', 'abs' => ''];
        }
        @unlink($tempAbs);
    }

    return ['ok' => true, 'db' => $finalDb, 'abs' => $finalAbs];
}

$current_user_id = (int)($_SESSION['user_id'] ?? 0);
if (!$current_user_id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$storyModel = new StoryModel($conn);
$action = $_GET['action'] ?? $_POST['action'] ?? '';

$uploadDirAbs = __DIR__ . '/../../../public/uploads/stories/';
$uploadDirDb = '../../../public/uploads/stories/';
$tempDirAbs = $uploadDirAbs . 'temp/';
$metaDirAbs = $uploadDirAbs . 'meta/';

if ($action === 'fetch') {
    try {
        $stories = $storyModel->getRecentStories(24);
        foreach ($stories as &$story) {
            $meta = readOverlayMeta($metaDirAbs, (string)($story['StoryMedia'] ?? ''));
            $story['OverlayText'] = !empty($story['Content']) ? $story['Content'] : $meta['text'];
            $story['OverlayPosition'] = $meta['position'];
            $story['ZoomScale'] = $meta['zoom'];
        }
        unset($story);

        echo json_encode(['status' => 'success', 'stories' => $stories]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['story_media']) || $_FILES['story_media']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'error', 'message' => 'Please select an image or video.']);
        exit;
    }

    $file = $_FILES['story_media'];
    if (!isset($file['tmp_name']) || !isAllowedMime($file['tmp_name'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unsupported file type.']);
        exit;
    }

    if ($file['size'] > (100 * 1024 * 1024)) {
        echo json_encode(['status' => 'error', 'message' => 'File is too large (max 100MB).']);
        exit;
    }

    ensureDir($uploadDirAbs);

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = 'story_' . $current_user_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $targetAbs = $uploadDirAbs . $filename;
    $targetDb = $uploadDirDb . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetAbs)) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to upload file.']);
        exit;
    }

    try {
        $overlayText = sanitizeOverlayText((string)($_POST['overlay_text'] ?? ''));
        $overlayPosition = sanitizeOverlayPosition((string)($_POST['overlay_position'] ?? 'bottom'));
        $zoomScale = sanitizeZoomScale($_POST['zoom_scale'] ?? 1);

        $storyModel->createStory($current_user_id, $targetDb, $overlayText);
        writeOverlayMeta($metaDirAbs, $targetDb, $overlayText, $overlayPosition, $zoomScale);

        echo json_encode(['status' => 'success', 'message' => 'Story added successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'create_from_temp' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $tempMedia = (string)($_POST['temp_media'] ?? '');
    $tempBase = basename(str_replace('\\', '/', $tempMedia));

    if ($tempBase === '') {
        echo json_encode(['status' => 'error', 'message' => 'Invalid temporary media.']);
        exit;
    }

    $tempAbs = $tempDirAbs . $tempBase;
    if (!is_file($tempAbs)) {
        echo json_encode(['status' => 'error', 'message' => 'Temporary media not found.']);
        exit;
    }

    $moved = moveTempToFinal($tempAbs, $uploadDirAbs, $uploadDirDb, $current_user_id);
    if (!$moved['ok']) {
        echo json_encode(['status' => 'error', 'message' => 'Unable to prepare story media.']);
        exit;
    }

    try {
        $overlayText = sanitizeOverlayText((string)($_POST['overlay_text'] ?? ''));
        $overlayPosition = sanitizeOverlayPosition((string)($_POST['overlay_position'] ?? 'bottom'));
        $zoomScale = sanitizeZoomScale($_POST['zoom_scale'] ?? 1);

        $storyModel->createStory($current_user_id, $moved['db'], $overlayText);
        writeOverlayMeta($metaDirAbs, $moved['db'], $overlayText, $overlayPosition, $zoomScale);

        echo json_encode(['status' => 'success', 'message' => 'Story added successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $storyId = (int)($_POST['story_id'] ?? 0);
    if ($storyId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid story id.']);
        exit;
    }

    try {
        $story = $storyModel->getStoryById($storyId);
        if (!$story || (int)$story['UserID'] !== (int)$current_user_id) {
            echo json_encode(['status' => 'error', 'message' => 'You can only delete your own story.']);
            exit;
        }

        $deleted = $storyModel->deleteStoryByIdForUser($storyId, $current_user_id);
        if ($deleted && !empty($story['StoryMedia'])) {
            $fileName = basename(str_replace('\\', '/', $story['StoryMedia']));
            $filePath = $uploadDirAbs . $fileName;
            if (is_file($filePath)) {
                @unlink($filePath);
            }
            removeOverlayMeta($metaDirAbs, (string)$story['StoryMedia']);
        }

        echo json_encode(['status' => 'success', 'deleted' => $deleted]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
