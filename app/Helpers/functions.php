<?php
// app/Helpers/functions.php

// Get the image URL path
function getImageUrl($imagePath = '') {
    if (empty($imagePath)) return '';
    if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://')) {
        return $imagePath;
    }
    return BASE_URL . 'public/uploads/avatars/' . ltrim($imagePath, '/');
}

/**
 * Returns the avatar URL for a user.
 * Falls back to default-avatar.svg when no real photo is uploaded.
 *
 * Usage: getAvatarUrl($u_d['ProfilePic'])
 */
function getAvatarUrl(?string $profilePic): string
{
    $base    = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
    $default = $base . '/public/img/default-avatar.svg';

    if (empty($profilePic)) {
        return $default;
    }

    // External URL (Google, Cloudinary, etc.) — return directly
    if (str_starts_with($profilePic, 'http://') || str_starts_with($profilePic, 'https://')) {
        return $profilePic;
    }

    // Strip any URL prefix that may have been stored accidentally
    if (str_contains($profilePic, '/public/uploads/avatars/')) {
        $parts      = explode('/public/uploads/avatars/', $profilePic, 2);
        $profilePic = basename($parts[1]);
    }

    $diskPath = dirname(__DIR__, 2) . '/public/uploads/avatars/' . $profilePic;

    return is_file($diskPath)
        ? $base . '/public/uploads/avatars/' . $profilePic
        : $default;
}

function getProfileUrl($userId = 0) {
    $id = (int)$userId;
    if ($id > 0) {
        return BASE_URL . 'app/Views/Profile/profile.php?user_id=' . urlencode((string)$id);
    }

    return BASE_URL . 'app/Views/Profile/profile.php';
}

// Get JavaScript config for global use
function getJsConfig() {
    return json_encode([
        'baseUrl' => BASE_URL,
        'imagePath' => BASE_URL . 'public/uploads/avatars/',
        'postsImagePath' => BASE_URL . 'public/uploads/posts/',
        'uploadsPath' => BASE_URL . 'public/uploads/',
        'chatPath' => BASE_URL . 'public/uploads/chat/',
        'csrfToken' => $_SESSION['csrf_token'] ?? ''
    ]);
}

function time_ago($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return $diff . ' seconds ago';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 2592000) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $time);
    }
}
?>
