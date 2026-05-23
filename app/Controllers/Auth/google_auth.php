<?php
session_start();
require_once '../../../config/db_config.php';
require_once '../../Helpers/admin.php';
require_once '../../../config/env.php';

lc_admin_ensure_schema($conn);

$clientID = env('GOOGLE_CLIENT_ID');
$clientSecret = env('GOOGLE_CLIENT_SECRET');

// កំណត់ Redirect URI ដោយប្រើប្រាស់ BASE_URL ផ្ទាល់តែម្ដង (គាំទ្រទាំង Ngrok និង Localhost)
$redirectUri = BASE_URL . 'app/Controllers/Auth/google_auth.php';

if (isset($_GET['code'])) {
    // Exchange code for an Access Token
    $tokenUrl = 'https://oauth2.googleapis.com/token';
    $postData = [
        'code' => $_GET['code'],
        'client_id' => $clientID,
        'client_secret' => $clientSecret,
        'redirect_uri' => $redirectUri,
        'grant_type' => 'authorization_code'
    ];

    $caPath = ini_get('curl.cainfo');
    if (empty($caPath) || !file_exists($caPath)) {
        $phpVer = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION;
        $caPath = 'C:/wamp64/bin/php/php' . $phpVer . '/extras/ssl/cacert.pem';
    }
    $sslVerify = file_exists($caPath);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $sslVerify);
    if ($sslVerify) curl_setopt($ch, CURLOPT_CAINFO, $caPath);
    $response = curl_exec($ch);
    curl_close($ch);

    $token = json_decode($response, true);

    if (isset($token['access_token'])) {
        // Use the Access Token to fetch user info from Google (name, email, picture)
        $userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $token['access_token'];
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_URL, $userInfoUrl);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, $sslVerify);
        if ($sslVerify) curl_setopt($ch2, CURLOPT_CAINFO, $caPath);
        $userInfoResponse = curl_exec($ch2);
        curl_close($ch2);
        $userInfo = json_decode($userInfoResponse, true);

        if (!empty($userInfo['email'])) {
            $email = $userInfo['email'];
            $name = $userInfo['name'];
            $picture = $userInfo['picture'] ?? '';

            // Check if this email already exists in the database
            $stmt = $conn->prepare("SELECT UserID, Username, Email, Role, AccountStatus FROM users WHERE Email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // If found, log the user in immediately
                $_SESSION['user_id'] = (int)$user['UserID'];
                $_SESSION['Email'] = $user['Email'];
                $_SESSION['username'] = $user['Username'];
                $_SESSION['user_role'] = lc_admin_normalize_role($user['Role'] ?? 'User');
                $_SESSION['account_status'] = lc_admin_normalize_status($user['AccountStatus'] ?? 'Active');
            } else {
                // If not found, automatically create a new account
                $insert = $conn->prepare("INSERT INTO users (Username, Email, ProfilePic, Password, AccountStatus, Role) VALUES (:name, :email, :pic, :pass, 'Active', 'User')");
                $insert->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':pic' => $picture,
                    ':pass' => password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT)
                ]);
                $_SESSION['user_id'] = (int)$conn->lastInsertId();
                $_SESSION['Email'] = $email;
                $_SESSION['username'] = $name;
            }
            if (!empty($_SESSION['account_status']) && $_SESSION['account_status'] !== 'Active') {
                $blockedStatus = (string)$_SESSION['account_status'];
                session_unset();
                session_destroy();
                header('Location: ../../Views/Login/login_gmail.php?error=Account_' . rawurlencode($blockedStatus));
                exit();
            }

            session_regenerate_id(true);
            lc_admin_sync_session($conn);
            header('Location: ../../Views/Home/dashboard.php');
            exit();
        }
    }
    header('Location: ../../Views/Login/login_gmail.php?error=Google_Login_Failed');
    exit();
}

// Redirect to Google's authorization page when button is clicked
$authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query(['client_id' => $clientID, 'redirect_uri' => $redirectUri, 'response_type' => 'code', 'scope' => 'email profile', 'access_type' => 'online']);
header("Location: $authUrl");
exit();
?>
