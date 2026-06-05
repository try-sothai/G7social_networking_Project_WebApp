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
    define('BASE_URL', 'https://social7.win/');
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Store sessions in DB so they survive Render server restarts
if (session_status() === PHP_SESSION_NONE) {
    require_once __DIR__ . '/session_handler.php';
    lc_init_db_sessions($conn);
}

// Auto-login from remember-me cookie when no active user session
if (session_status() === PHP_SESSION_ACTIVE && empty($_SESSION['user_id']) && !empty($_COOKIE['s7_remember'])) {
    require_once __DIR__ . '/remember_me.php';
    $rememberedUser = lc_remember_check($conn);
    if ($rememberedUser && ($rememberedUser['AccountStatus'] ?? 'Active') === 'Active') {
        session_regenerate_id(true);
        $_SESSION['user_id']        = (int) $rememberedUser['user_id'];
        $_SESSION['Email']          = $rememberedUser['Email'];
        $_SESSION['username']       = $rememberedUser['Username'];
        $_SESSION['user_role']      = $rememberedUser['Role'] ?? 'User';
        $_SESSION['account_status'] = $rememberedUser['AccountStatus'] ?? 'Active';
    }
    unset($rememberedUser);
}

// Generate CSRF token for the session
if (session_status() === PHP_SESSION_ACTIVE && empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
