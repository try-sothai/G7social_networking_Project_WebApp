<?php
session_start();

require_once __DIR__ . '/../../../config/db_config.php';
require_once __DIR__ . '/../../Helpers/admin.php';
require_once __DIR__ . '/../../Helpers/mailer.php';

lc_admin_ensure_schema($conn);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$formData = [
    'name' => '',
    'email' => '',
    'phone' => '',
];

if (isset($_POST['send_otp'])) {
    $csrf = (string) ($_POST['csrf_token'] ?? '');
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    $formData['name'] = $name;
    $formData['email'] = $email;
    $formData['phone'] = $phone;

    if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
        $error = 'Invalid request token. Please refresh and try again.';
    } elseif ($name === '' || $email === '' || $phone === '' || $password === '' || $confirmPassword === '') {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (mb_strlen($name, 'UTF-8') < 3 || mb_strlen($name, 'UTF-8') > 50) {
        $error = 'Name must be between 3 and 50 characters.';
    } elseif (!preg_match('/^[0-9+\-\s]{8,20}$/', $phone)) {
        $error = 'Please enter a valid phone number.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Password and Confirm Password do not match.';
    } else {
        try {
            $checkExisting = $conn->prepare('SELECT UserID, Email, Phone FROM users WHERE Email = :email OR Phone = :phone LIMIT 1');
            $checkExisting->bindValue(':email', $email, PDO::PARAM_STR);
            $checkExisting->bindValue(':phone', $phone, PDO::PARAM_STR);
            $checkExisting->execute();
            $existingUser = $checkExisting->fetch(PDO::FETCH_ASSOC);

            if ($existingUser) {
                if (strcasecmp((string) ($existingUser['Email'] ?? ''), $email) === 0) {
                    $error = 'This email is already registered.';
                } else {
                    $error = 'This phone number is already registered.';
                }
            } else {
                $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $_SESSION['signup_otp'] = [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    'otp' => $otp,
                    'expires_at' => time() + 300,
                    'sent_at' => time(),
                ];

                $sendResult = lc_send_otp_email($email, $name, $otp);

                if ($sendResult['success']) {
                    header('Location: sign_up_phone_otp.php');
                    exit();
                }

                unset($_SESSION['signup_otp']);
                $error = (string) $sendResult['message'];
            }
        } catch (PDOException $e) {
            $error = 'Unable to start verification right now. Please try again.';
        }
    }
}

if (false && isset($_POST['send_otp'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);

    if(!empty($name) && !empty($phone)){
        // រក្សាទុកទិន្នន័យក្នុង Session
        $_SESSION['signup_name'] = $name;
        $_SESSION['phone'] = $phone;
        
        // បង្កើតលេខកូដ OTP (ឧទាហរណ៍៖ 123456 សម្រាប់តេស្ត ឬ rand)
        $_SESSION['generated_otp'] = "123456"; 

        // ប្តូរទៅកាន់ទំព័របញ្ចូល OTP
        header("Location: sign_up_phone_otp.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social7 - Sign Up with Phone</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../../public/css/Login/auth.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="auth-card">
    <h1 class="auth-logo">Social7</h1>
    <p class="auth-subtitle">Verify phone sign up with a free email OTP</p>

    <div class="auth-tabs">
        <a href="sign_up_email.php" class="auth-tab"><span><i class="fa-regular fa-envelope"></i></span> Email</a>
        <a href="sign_up_phone.php" class="auth-tab active"><span><i class="fa-solid fa-phone"></i></span> Phone</a>
    </div>

    <?php if ($error !== ''): ?>
        <div class="auth-msg error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form class="auth-form" action="sign_up_phone.php" method="POST" autocomplete="off">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string) $_SESSION['csrf_token']); ?>">
    <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" placeholder="Enter your name" minlength="3" maxlength="50" value="<?php echo htmlspecialchars($formData['name']); ?>" required autofocus>
    </div>
    <div class="form-group">
        <label>Email for OTP</label>
        <input type="email" name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($formData['email']); ?>" required>
        <small>We will send a 6-digit code to this email.</small>
    </div>
    <div class="form-group">
        <label>Phone Number</label>
        <input type="text" name="phone" placeholder="+855..." value="<?php echo htmlspecialchars($formData['phone']); ?>" required>
    </div>
    <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Create a password" minlength="8" required>
    </div>
    <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" placeholder="Re-enter your password" minlength="8" required>
    </div>
    <button type="submit" name="send_otp" class="btn-primary">Send OTP to Email</button>
</form>

    <div class="auth-divider">or</div>

    <button type="button" class="btn-social" onclick="window.location.href='../../Controllers/Auth/google_auth.php'">
        <img src="https://www.google.com/favicon.ico" width="18" alt="Google"> Continue with Google
    </button>
    <button type="button" class="btn-social" onclick="window.location.href='../../Controllers/Auth/facebook_auth.php'">
        <img src="https://www.facebook.com/favicon.ico" width="18" alt="Facebook"> Continue with Facebook
    </button>

    <div class="auth-footer">
        Already have an account? <a href="login_gmail.php">Log in</a>
    </div>
</div>

</body>
</html>
