
<?php
session_start();
require_once '../../../config/db_config.php';

// ⚠️ ១. សូមយក App ID និង App Secret របស់ហ្វេសប៊ុកបង មកដាក់ទីនេះ
$appID = '1823607298598252';
$appSecret = 'c3cf21805947286a1a3b4a05dd3a42be';

// ⚠️ ២. កំណត់ Link ឲ្យចំ (វាត្រូវគ្នានឹងអ្វីដែលបងបាន Save ក្នុង Facebook មិញនេះ)
$redirectUri = 'http://localhost/G7social_networking_Project_WebApp/app/Controllers/Auth/facebook_auth.php';

if (isset($_GET['code'])) {
    $tokenUrl = "https://graph.facebook.com/v12.0/oauth/access_token?" . http_build_query([
        'client_id' => $appID,
        'client_secret' => $appSecret,
        'redirect_uri' => $redirectUri,
        'code' => $_GET['code']
    ]);

    // ប្រើ cURL ដើម្បីរំលង SSL លើ WAMP
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    $response = curl_exec($ch);
    curl_close($ch);

    $token = json_decode($response, true);

    if (isset($token['access_token'])) {
        $userInfoUrl = "https://graph.facebook.com/me?fields=id,name,email,picture.type(large)&access_token=" . $token['access_token'];
        
        // រំលង SSL ពេលទាញយកព័ត៌មាន (ឈ្មោះ, អ៊ីមែល, រូបតំណាង)
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_URL, $userInfoUrl);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
        $userInfoResponse = curl_exec($ch2);
        curl_close($ch2);

        $userInfo = json_decode($userInfoResponse, true);

        // បើ Facebook អត់ឲ្យ Email មកទេ (ដោយសារ User ចុះឈ្មោះប្រើលេខទូរស័ព្ទ) យើងបង្កើត Email ជំនួសឲ្យគាត់
        $email = !empty($userInfo['email']) ? $userInfo['email'] : $userInfo['id'] . '@facebook.com';
        $name = $userInfo['name'] ?? 'Facebook User';
        $picture = $userInfo['picture']['data']['url'] ?? '';

        $stmt = $conn->prepare("SELECT UserID, Username, Email FROM users WHERE Email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['user_id'] = (int)$user['UserID'];
            $_SESSION['Email'] = $user['Email'];
            $_SESSION['username'] = $user['Username'];
        } else {
            $insert = $conn->prepare("INSERT INTO users (Username, Email, ProfilePic, Password) VALUES (:name, :email, :pic, :pass)");
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
        header('Location: ../../Views/Home/dashboard.php');
        exit();
    }
    header('Location: ../../Views/Login/login_gmail.php?error=Facebook_Login_Failed');
    exit();
}

$authUrl = "https://www.facebook.com/v12.0/dialog/oauth?" . http_build_query([
    'client_id' => $appID,
    'redirect_uri' => $redirectUri,
    'scope' => 'email,public_profile'
]);
header("Location: $authUrl");
exit();
?>