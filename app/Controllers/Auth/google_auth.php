<?php
session_start();
require_once '../../../config/db_config.php';
require_once '../../Helpers/admin.php';

lc_admin_ensure_schema($conn);

// ⚠️ សំខាន់៖ សូមយក Client ID និង Client Secret ដែលបងទើបតែបានពី Google មក Paste ជំនួសត្រង់នេះ
$clientID = '373812776585-kdb825tsdi71rq22gksnepnalpiaoegq.apps.googleusercontent.com';
$clientSecret = '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';

// បង្កើត Redirect URI ពេញលេញដោយស្វ័យប្រវត្តិ
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$redirectUri = $protocol . $host . rtrim(BASE_URL, '/') . '/app/Controllers/Auth/google_auth.php';

if (isset($_GET['code'])) {
    // ប្តូរ Code យក Access Token
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
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // សំខាន់សម្រាប់ WAMP Localhost ដើម្បីកុំឲ្យ Error SSL
    $response = curl_exec($ch);
    curl_close($ch);

    $token = json_decode($response, true);

    if (isset($token['access_token'])) {
        // ប្រើ Access Token ទៅទាញយកទិន្នន័យពី Google (ឈ្មោះ, អ៊ីមែល, រូបភាព)
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

            // ឆែកមើលថាមាន Email នេះក្នុង Database ឬនៅ
            $stmt = $conn->prepare("SELECT UserID, Username, Email, Role, AccountStatus FROM users WHERE Email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // បើមាន ឲ្យ Login ចូលតែម្ដង
                $_SESSION['user_id'] = (int)$user['UserID'];
                $_SESSION['Email'] = $user['Email'];
                $_SESSION['username'] = $user['Username'];
                $_SESSION['user_role'] = lc_admin_normalize_role($user['Role'] ?? 'User');
                $_SESSION['account_status'] = lc_admin_normalize_status($user['AccountStatus'] ?? 'Active');
            } else {
                // បើមិនទាន់មាន បង្កើតគណនីថ្មីឲ្យដោយស្វ័យប្រវត្តិ
                $insert = $conn->prepare("INSERT INTO users (Username, Email, ProfilePic, Password) VALUES (:name, :email, :pic, :pass)");
                $insert->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':pic' => $picture,
                    ':pass' => password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT) // លេខកូដសម្ងាត់ចៃដន្យ
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

// បញ្ជូនទៅកាន់ទំព័រ Google ពេលចុចប៊ូតុង
$authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query(['client_id' => $clientID, 'redirect_uri' => $redirectUri, 'response_type' => 'code', 'scope' => 'email profile', 'access_type' => 'online']);
header("Location: $authUrl");
exit();
?>
