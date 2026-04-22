
<?php
session_start();
require_once '../../../config/db_config.php';
require_once '../../Helpers/admin.php';
require_once '../../../config/env.php';

lc_admin_ensure_schema($conn);

$appID = env('FACEBOOK_APP_ID');
$appSecret = env('FACEBOOK_APP_SECRET');

// កំណត់ Redirect URI ដោយប្រើប្រាស់ BASE_URL ផ្ទាល់ (គាំទ្រទាំង Ngrok ទាំង Localhost)
$redirectUri = BASE_URL . 'app/Controllers/Auth/facebook_auth.php';

if (isset($_GET['code'])) {
    $tokenUrl = "https://graph.facebook.com/v12.0/oauth/access_token?" . http_build_query([
        '​client_id' => $appID,
        'client_secret' => $appSecret,
        'redirect_uri' => $redirectUri,
        'code' => $_GET['code']
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_CAINFO, ini_get('curl.cainfo') ?: 'C:/wamp64/bin/php/php8.2.29/extras/ssl/cacert.pem');
    $response = curl_exec($ch);
    curl_close($ch);

    $token = json_decode($response, true);

    if (isset($token['access_token'])) {
        $userInfoUrl = "https://graph.facebook.com/me?fields=id,name,email,picture.type(large)&access_token=" . $token['access_token'];
        
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_URL, $userInfoUrl);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch2, CURLOPT_CAINFO, ini_get('curl.cainfo') ?: 'C:/wamp64/bin/php/php8.2.29/extras/ssl/cacert.pem');
        $userInfoResponse = curl_exec($ch2);
        curl_close($ch2);

        $userInfo = json_decode($userInfoResponse, true);

        // If Facebook doesn't provide an email (e.g., user registered with phone number), generate a fallback email
        $email = !empty($userInfo['email']) ? $userInfo['email'] : $userInfo['id'] . '@facebook.com';
        $name = $userInfo['name'] ?? 'Facebook User';
        $picture = $userInfo['picture']['data']['url'] ?? '';

        $stmt = $conn->prepare("SELECT UserID, Username, Email, Role, AccountStatus FROM users WHERE Email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['user_id'] = (int)$user['UserID'];
            $_SESSION['Email'] = $user['Email'];
            $_SESSION['username'] = $user['Username'];
            $_SESSION['user_role'] = lc_admin_normalize_role($user['Role'] ?? 'User');
            $_SESSION['account_status'] = lc_admin_normalize_status($user['AccountStatus'] ?? 'Active');
        } else {
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
    header('Location: ../../Views/Login/login_gmail.php?error=Facebook_Login_Failed');
    exit();
}

$authUrl = "https://www.facebook.com/v12.0/dialog/oauth?" . http_build_query([
    'client_id' => $appID,
    'redirect_uri' => $redirectUri,
    'scope' => 'email,public_profile',
    'display' => 'popup'
]);
header("Location: $authUrl");
exit();
?>
