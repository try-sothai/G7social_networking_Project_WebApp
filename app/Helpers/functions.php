<?php
// app/Helpers/functions.php

// Get the base URL for the application
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $dir = '/ss/G7social_networking';
    return $protocol . '://' . $host . $dir;
}

// Get the image URL path
function getImageUrl($imagePath = '') {
    return getBaseUrl() . '/public/img/' . ltrim($imagePath, '/');
}

// Get JavaScript config for global use
function getJsConfig() {
    return json_encode([
        'baseUrl' => getBaseUrl(),
        'imagePath' => getBaseUrl() . '/public/img/',
        'postsImagePath' => getBaseUrl() . '/public/img/posts/',
        'uploadsPath' => getBaseUrl() . '/public/uploads/',
        'chatPath' => getBaseUrl() . '/public/uploads/chat/'
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