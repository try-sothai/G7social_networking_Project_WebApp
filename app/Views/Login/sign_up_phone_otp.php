<?php
session_start();
require_once __DIR__ . '/../../../config/db_config.php';
require_once __DIR__ . '/../../Helpers/admin.php';
require_once __DIR__ . '/../../Helpers/mailer.php';

lc_admin_ensure_schema($conn);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// បើមិនទាន់ឆ្លងកាត់ទំព័រទី១ មិនឱ្យចូលទំព័រនេះឡើយ
if (empty($_SESSION['signup_otp']) || !is_array($_SESSION['signup_otp'])) {
    header('Location: sign_up_phone.php');
    exit();
}

function sign_up_phone_mask_email(string $email): string
{
    if (strpos($email, '@') === false) {
        return $email;
    }

    [$localPart, $domain] = explode('@', $email, 2);
    $visible = substr($localPart, 0, 2);
    $masked = str_repeat('*', max(strlen($localPart) - 2, 2));

    return $visible . $masked . '@' . $domain;
}

$error = '';
$message = '';
$signupData = $_SESSION['signup_otp'];
$phone = (string) ($signupData['phone'] ?? '');
$name = (string) ($signupData['name'] ?? '');
$email = (string) ($signupData['email'] ?? '');

if (isset($_POST['resend_otp']) || isset($_POST['signup'])) {
    $csrf = (string) ($_POST['csrf_token'] ?? '');

    if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
        $error = 'Invalid request token. Please refresh and try again.';
    } elseif (isset($_POST['resend_otp'])) {
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $_SESSION['signup_otp']['otp'] = $otp;
        $_SESSION['signup_otp']['expires_at'] = time() + 300;
        $_SESSION['signup_otp']['sent_at'] = time();

        $sendResult = lc_send_otp_email($email, $name, $otp);

        if ($sendResult['success']) {
            $message = 'A new OTP was sent to your email.';
        } else {
            $error = (string) $sendResult['message'];
        }
    } elseif (isset($_POST['signup'])) {
        $userOtp = trim((string) ($_POST['otp'] ?? ''));

        if ($userOtp === '') {
            $error = 'Please enter the OTP code.';
        } elseif (time() > (int) ($_SESSION['signup_otp']['expires_at'] ?? 0)) {
            $error = 'This OTP has expired. Please resend a new code.';
        } elseif (!hash_equals((string) ($_SESSION['signup_otp']['otp'] ?? ''), $userOtp)) {
            $error = 'Invalid OTP code. Please try again.';
        } else {
            try {
                $checkExisting = $conn->prepare('SELECT UserID FROM users WHERE Email = :email OR Phone = :phone LIMIT 1');
                $checkExisting->bindValue(':email', $email, PDO::PARAM_STR);
                $checkExisting->bindValue(':phone', $phone, PDO::PARAM_STR);
                $checkExisting->execute();

                if ($checkExisting->fetch(PDO::FETCH_ASSOC)) {
                    $error = 'This email or phone number is already registered.';
                } else {
                    $stmt = $conn->prepare('INSERT INTO users (Username, Email, Phone, Password, Role, AccountStatus) VALUES (:username, :email, :phone, :password, :role, :status)');
                    $stmt->bindValue(':username', $name, PDO::PARAM_STR);
                    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
                    $stmt->bindValue(':phone', $phone, PDO::PARAM_STR);
                    $stmt->bindValue(':password', (string) $_SESSION['signup_otp']['password_hash'], PDO::PARAM_STR);
                    $stmt->bindValue(':role', 'User', PDO::PARAM_STR);
                    $stmt->bindValue(':status', 'Active', PDO::PARAM_STR);

                    if ($stmt->execute()) {
                        $newUserId = (int) $conn->lastInsertId();
                        unset($_SESSION['signup_otp']);

                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $newUserId;
                        $_SESSION['username'] = $name;
                        $_SESSION['Email'] = $email;
                        $_SESSION['user_role'] = 'User';
                        $_SESSION['account_status'] = 'Active';

                        header('Location: ' . BASE_URL . 'app/Views/Home/dashboard.php');
                        exit();
                    }

                    $error = 'Failed to create your account.';
                }
            } catch (PDOException $e) {
                $error = 'Unable to complete sign up right now. Please try again.';
            }
        }
    }
}

if (false && isset($_POST['signup'])) {
    $user_otp = trim($_POST['otp']);

    if (!empty($user_otp)) {
        // ផ្ទៀងផ្ទាត់ជាមួយ OTP ដែលនៅក្នុង Session
        if ($user_otp === $_SESSION['generated_otp']) {
            try {
                // បញ្ចូលក្នុង Database
                $stmt = $conn->prepare("INSERT INTO users (Username, Email) VALUES (:name, :phone)");
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':phone', $phone);

                if ($stmt->execute()) {
                    $message = "Account Created Successfully!";
                    // លុប Session ចោលក្រោយជោគជ័យ
                    session_unset();
                    session_destroy();
                    // រុញទៅទំព័រ Login ក្រោយ 2 វិនាទី
                    header("Refresh: 2; url=login_gmail.php");
                }
            } catch (PDOException $e) {
                $error = "Database Error: " . $e->getMessage();
            }
        } else {
            $error = "Invalid OTP code. Please try again!";
        }
    } else {
        $error = "Please enter the OTP code!";
    }
}

$displayMessage = $message;
$displayError = $error;
$message = '';
$error = '';
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social7 - Sign Up</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../../public/css/Login/auth.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="auth-card">
    <h1 class="auth-logo">Social7</h1>
    <p class="auth-subtitle">Verify OTP for <strong><?php echo htmlspecialchars(sign_up_phone_mask_email($email)); ?></strong></p>
    <div class="auth-tabs">
        <a href="sign_up_email.php" class="auth-tab"><span><i class="fa-regular fa-envelope"></i></span> Email</a>
        <a href="sign_up_phone.php" class="auth-tab active"><span><i class="fa-solid fa-phone"></i></span> Phone</a>
    </div>
    <?php if($message) echo "<div class='auth-msg success'>✅ $message</div>"; ?>
    <?php if($error) echo "<div class='auth-msg error'>❌ $error</div>"; ?>

    <?php if ($displayMessage !== ''): ?>
        <div class="auth-msg success"><?php echo htmlspecialchars($displayMessage); ?></div>
    <?php endif; ?>
    <?php if ($displayError !== ''): ?>
        <div class="auth-msg error"><?php echo htmlspecialchars($displayError); ?></div>
    <?php endif; ?>

    <form class="auth-form" action="" method="POST" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string) $_SESSION['csrf_token']); ?>">
        <div class="form-group">
            <label>Name</label>
            <input type="text" value="<?php echo htmlspecialchars($name); ?>" readonly>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="text" value="<?php echo htmlspecialchars($email); ?>" readonly>
        </div>

        <div class="form-group">
            <label>Phone Number</label>
            <input type="text" value="<?php echo htmlspecialchars($phone); ?>" readonly>
        </div>

        <div class="form-group">
            <label>Enter 6-digit OTP</label>
            <input type="text" name="otp" placeholder="Enter OTP" maxlength="6" required autofocus>
            <small>Code sent to <?php echo htmlspecialchars(sign_up_phone_mask_email($email)); ?>. It expires in 5 minutes.</small>
        </div>

        <button type="submit" name="signup" class="btn-primary">Verify & Sign Up</button>
    </form>

    <form class="auth-form" action="" method="POST" style="margin-top: 12px;">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string) $_SESSION['csrf_token']); ?>">
        <button type="submit" name="resend_otp" class="btn-social">Resend OTP</button>
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
