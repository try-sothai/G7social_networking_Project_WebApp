<?php
// Hide PHP warnings/notices/deprecations from UI (log them instead)
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('html_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_NOTICE & ~E_WARNING);

$host = "bx6ifxp9uk5fmi1fqtia-mysql.services.clever-cloud.com";
$dbname = "bx6ifxp9uk5fmi1fqtia";
$username = "uovzq7arcre9qv6q";
$password = "Hfz2SqF8PpVxwzFKlnpC";

if (!defined('BASE_URL')) {
    // ពិនិត្យមើលថាតើកំពុងប្រើប្រាស់ HTTP ឬ HTTPS (Ngrok ប្រើ HTTPS)
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' 
        || $_SERVER['SERVER_PORT'] == 443 
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) 
        ? "https://" : "http://";

    // ទាញយកឈ្មោះ Host
    $serverHost = $_SERVER['HTTP_HOST'] ?? 'localhost';

    $projectRoot = str_replace('\\', '/', realpath(__DIR__ . '/..'));
    $documentRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));

    if ($projectRoot && $documentRoot && str_starts_with($projectRoot, $documentRoot)) {
        $basePath = str_replace($documentRoot, '', $projectRoot);
        $basePath = '/' . trim(str_replace('\\', '/', $basePath), '/') . '/';
    } else {
        // Fallback: use current project folder name
        $basePath = '/' . basename($projectRoot ?: 'G7social_networking_Project_WebApp') . '/';
    }

    // កំណត់ BASE_URL ដោយស្វ័យប្រវត្តិរួមទាំង Protocol និង Host
    define('BASE_URL', $protocol . $serverHost . $basePath);
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Generate CSRF token for the session
if (session_status() === PHP_SESSION_ACTIVE && empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
