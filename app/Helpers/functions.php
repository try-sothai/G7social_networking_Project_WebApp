<?php
// app/Helpers/functions.php

// Get the image URL path
function getImageUrl($imagePath = '') {
    return BASE_URL . 'public/img/' . ltrim($imagePath, '/');
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
        'imagePath' => BASE_URL . 'public/img/',
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
