<?php
// Hide PHP warnings/notices/deprecations from UI (log them instead)
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('html_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_NOTICE & ~E_WARNING);

$host = "localhost";
$dbname = "g7social_networking";
$username = "root";
$password = "";

if (!defined('BASE_URL')) {
    $projectRoot = str_replace('\\', '/', realpath(__DIR__ . '/..'));
    $documentRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));

    if ($projectRoot && $documentRoot && str_starts_with($projectRoot, $documentRoot)) {
        $basePath = str_replace($documentRoot, '', $projectRoot);
        $basePath = '/' . trim(str_replace('\\', '/', $basePath), '/') . '/';
    } else {
        // Fallback: use current project folder name
        $basePath = '/' . basename($projectRoot ?: 'G7social_networking_Project_WebApp') . '/';
    }

    define('BASE_URL', $basePath);
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
