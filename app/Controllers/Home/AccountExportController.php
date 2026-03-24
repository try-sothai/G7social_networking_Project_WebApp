<?php
session_start();

require_once __DIR__ . '/../../../config/db_config.php';

$currentUserId = (int)($_SESSION['user_id'] ?? 0);
if ($currentUserId <= 0) {
    http_response_code(401);
    echo 'Unauthorized';
    exit;
}

$projectRoot = realpath(__DIR__ . '/../../../');
if ($projectRoot === false) {
    http_response_code(500);
    echo 'Unable to locate project root.';
    exit;
}

function parseMediaListFromPath($value): array {
    $raw = trim((string)$value);
    if ($raw === '') {
        return [];
    }

    if (str_starts_with($raw, '[') && str_ends_with($raw, ']')) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return array_values(array_filter(array_map('strval', $decoded), static fn($v) => trim($v) !== ''));
        }
    }

    return [$raw];
}

function dbPathToAbsolute(string $projectRoot, string $dbPath): ?array {
    $normalized = str_replace('\\', '/', trim($dbPath));
    if ($normalized === '') {
        return null;
    }

    $relative = preg_replace('#^(\.\./)+#', '', $normalized);
    if (!is_string($relative) || $relative === '') {
        return null;
    }

    $absolute = $projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    $realAbs = realpath($absolute);
    if ($realAbs === false || !is_file($realAbs)) {
        return null;
    }

    $rootNorm = rtrim(str_replace('\\', '/', $projectRoot), '/');
    $absNorm = str_replace('\\', '/', $realAbs);
    if (strpos($absNorm, $rootNorm . '/') !== 0) {
        return null;
    }

    return [
        'abs' => $realAbs,
        'rel' => $relative
    ];
}

try {
    $userStmt = $conn->prepare("
        SELECT UserID, Username, Email, ProfilePic
        FROM users
        WHERE UserID = :user_id
        LIMIT 1
    ");
    $userStmt->execute([':user_id' => $currentUserId]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo 'User not found.';
        exit;
    }

    $postStmt = $conn->prepare("
        SELECT PostID, Content, ImagePath, CreatedAt
        FROM posts
        WHERE UserID = :user_id
        ORDER BY CreatedAt DESC
    ");
    $postStmt->execute([':user_id' => $currentUserId]);
    $posts = $postStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $storyStmt = $conn->prepare("
        SELECT StoryID, StoryMedia, CreatedAt
        FROM stories
        WHERE UserID = :user_id
        ORDER BY CreatedAt DESC
    ");
    $storyStmt->execute([':user_id' => $currentUserId]);
    $stories = $storyStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Database error: ' . $e->getMessage();
    exit;
}

$mediaForZip = [];
$postsForJson = [];
foreach ($posts as $post) {
    $mediaList = parseMediaListFromPath($post['ImagePath'] ?? '');
    $resolvedMedia = [];

    foreach ($mediaList as $mediaPath) {
        $resolved = dbPathToAbsolute($projectRoot, $mediaPath);
        if (!$resolved) {
            continue;
        }

        $zipRel = 'media/' . ltrim(str_replace('\\', '/', $resolved['rel']), '/');
        $mediaForZip[$resolved['abs']] = $zipRel;
        $resolvedMedia[] = str_replace('\\', '/', $resolved['rel']);
    }

    $postsForJson[] = [
        'post_id' => (int)$post['PostID'],
        'caption' => (string)($post['Content'] ?? ''),
        'created_at' => (string)($post['CreatedAt'] ?? ''),
        'media' => $resolvedMedia
    ];
}

$storiesForJson = [];
foreach ($stories as $story) {
    $resolvedMedia = [];
    $resolved = dbPathToAbsolute($projectRoot, (string)($story['StoryMedia'] ?? ''));
    if ($resolved) {
        $zipRel = 'media/' . ltrim(str_replace('\\', '/', $resolved['rel']), '/');
        $mediaForZip[$resolved['abs']] = $zipRel;
        $resolvedMedia[] = str_replace('\\', '/', $resolved['rel']);
    }

    $storiesForJson[] = [
        'story_id' => (int)$story['StoryID'],
        'created_at' => (string)($story['CreatedAt'] ?? ''),
        'media' => $resolvedMedia
    ];
}

$exportPayload = [
    'exported_at' => gmdate('c'),
    'user' => [
        'user_id' => (int)$user['UserID'],
        'username' => (string)($user['Username'] ?? ''),
        'email' => (string)($user['Email'] ?? ''),
        'profile_picture' => (string)($user['ProfilePic'] ?? '')
    ],
    'posts' => $postsForJson,
    'stories' => $storiesForJson
];

$fileDate = gmdate('Ymd_His');
$downloadBaseName = 'localconnect_account_export_' . $currentUserId . '_' . $fileDate;

if (!class_exists('ZipArchive')) {
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $downloadBaseName . '.json"');
    echo json_encode($exportPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

$tmpZip = tempnam(sys_get_temp_dir(), 'lc_export_');
if ($tmpZip === false) {
    http_response_code(500);
    echo 'Unable to prepare export file.';
    exit;
}

$zip = new ZipArchive();
$zipStatus = $zip->open($tmpZip, ZipArchive::OVERWRITE);
if ($zipStatus !== true) {
    @unlink($tmpZip);
    http_response_code(500);
    echo 'Unable to create export archive.';
    exit;
}

$zip->addFromString(
    'account_data.json',
    json_encode($exportPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
);

foreach ($mediaForZip as $abs => $zipRel) {
    if (is_file($abs)) {
        $zip->addFile($abs, $zipRel);
    }
}

$zip->close();

while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $downloadBaseName . '.zip"');
header('Content-Length: ' . filesize($tmpZip));

readfile($tmpZip);
@unlink($tmpZip);
exit;
