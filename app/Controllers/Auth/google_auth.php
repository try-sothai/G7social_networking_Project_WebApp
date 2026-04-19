<?php
session_start();
require_once '../../../config/db_config.php';
require_once '../../Helpers/admin.php';

lc_admin_ensure_schema($conn);

// ⚠️ Important: Paste your Client ID and Client Secret obtained from Google here
$clientID = '373812776585-kdb825tsdi71rq22gksnepnalpiaoegq.apps.googleusercontent.com';
$clientSecret = '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';

// Automatically build the full Redirect URI
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$redirectUri = $protocol . $host . rtrim(BASE_URL, '/') . '/app/Controllers/Auth/google_auth.php';

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

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Required on WAMP localhost to prevent SSL errors
    $response = curl_exec($ch);
    curl_close($ch);

    $token = json_decode($response, true);

    if (isset($token['access_token'])) {
        // Use the Access Token to fetch user info from Google (name, email, picture)
        $userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $token['access_token'];
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_URL, $userInfoUrl);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
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
                $insert = $conn->prepare("INSERT INTO users (Username, Email, ProfilePic, Password) VALUES (:name, :email, :pic, :pass)");
                $insert->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':pic' => $picture,
                    ':pass' => password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT) // Random password
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
