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
    define('BASE_URL', 'https://g7social-networking-project-webapp.onrender.com/');
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
