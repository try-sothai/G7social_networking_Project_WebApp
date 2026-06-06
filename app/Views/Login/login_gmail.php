<?php
require_once '../../../config/db_config.php';
require_once '../../../config/remember_me.php';
require_once '../../Helpers/admin.php';

lc_admin_ensure_schema($conn);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$pendingEmail = '';
$maxAttempts = 5;
$lockoutSeconds = 300;

if (isset($_GET['error'])) {
    $error = htmlspecialchars(str_replace('_', ' ', $_GET['error']));
}

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}
if (!isset($_SESSION['login_locked_until'])) {
    $_SESSION['login_locked_until'] = 0;
}

if (isset($_POST['login'])) {
    $csrf = $_POST['csrf_token'] ?? '';
    $now = time();

    if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
        $error = 'Invalid request token. Please refresh and try again.';
    } elseif ($_SESSION['login_locked_until'] > $now) {
        $remaining = (int) ($_SESSION['login_locked_until'] - $now);
        $error = 'Too many failed attempts. Try again in ' . $remaining . ' seconds.';
    } else {
        $Email = trim($_POST['Email'] ?? '');
        $Password = trim($_POST['Password'] ?? '');

        if (filter_var($Email, FILTER_VALIDATE_EMAIL) && $Password !== '') {
            try {
                $stmt = $conn->prepare('SELECT UserID, Username, Email, Password, Role, AccountStatus FROM users WHERE Email = :Email LIMIT 1');
                $stmt->bindValue(':Email', $Email, PDO::PARAM_STR);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                $passwordOk = false;
                if ($user && isset($user['Password'])) {
                    $stored = (string) $user['Password'];

                    if (password_verify($Password, $stored)) {
                        $passwordOk = true;
                    }
                }

                if ($user && $passwordOk) {
                    $accountStatus = lc_admin_normalize_status($user['AccountStatus'] ?? 'Active');
                    if ($accountStatus === 'Pending') {
                        $pendingEmail = $user['Email'];
                        $error = 'pending_verification';
                    } elseif ($accountStatus !== 'Active') {
                        $error = 'This account is currently ' . strtolower($accountStatus) . '.';
                    } else {
                        session_regenerate_id(true);
                        $_SESSION['login_attempts'] = 0;
                        $_SESSION['login_locked_until'] = 0;

                        $_SESSION['user_id'] = (int) $user['UserID'];
                        $_SESSION['Email'] = $user['Email'];
                        $_SESSION['username'] = $user['Username'];
                        $_SESSION['user_role'] = lc_admin_normalize_role($user['Role'] ?? 'User');
                        $_SESSION['account_status'] = $accountStatus;

                        lc_admin_sync_session($conn);

                        if (!empty($_POST['remember_me'])) {
                            lc_remember_create($conn, (int) $user['UserID']);
                        }

                        header('Location: ' . BASE_URL . 'app/Views/Home/dashboard.php');
                        exit();
                    }
                }

                if ($error === '') {
                    $_SESSION['login_attempts']++;
                    if ($_SESSION['login_attempts'] >= $maxAttempts) {
                        $_SESSION['login_locked_until'] = $now + $lockoutSeconds;
                        $_SESSION['login_attempts'] = 0;
                    }
                    $error = 'Invalid email or password.';
                }
            } catch (PDOException $e) {
                $error = 'Login failed. Please try again.';
            }
        } else {
            $error = 'Please enter a valid email and password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Social7 - Login</title>

<link rel="stylesheet" href="../../../public/css/login/auth.css">
<link rel="stylesheet" href="../../../public/css/fontawesome/all.min.css">
    <link rel="stylesheet" href="../../../public/css/fonts/fonts.css">
</head>
<body>
<div class="auth-card">
    <h1 class="auth-logo">Social7</h1>
    <p class="auth-subtitle">Connect with your community</p>

    <div class="auth-tabs">
        <a href="login_gmail.php" class="auth-tab active">
            <span><i class="fa-regular fa-envelope"></i></span> Email
        </a>
        <a href="login_phone.php" class="auth-tab">
            <span><i class="fa-solid fa-phone"></i></span> Phone
        </a>
    </div>

    <?php if ($error === 'pending_verification'): ?>
        <div class="auth-msg error">
            Your email is not verified yet.
            <a href="resend_verification.php?email=<?php echo urlencode($pendingEmail); ?>" style="color:#1d4ed8;font-weight:600;">Resend verification email</a>
        </div>
    <?php elseif ($error): ?>
        <div class="auth-msg error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

     <form class="auth-form" action="" method="POST" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="Email" placeholder="Enter your Email address" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="Password" placeholder="Enter your Password" minlength="8" required>
        </div>
        <div class="form-group" style="flex-direction:row;align-items:center;gap:8px;margin-top:-4px;">
            <input type="checkbox" name="remember_me" id="remember_me" value="1" style="width:16px;height:16px;cursor:pointer;accent-color:var(--primary,#1d4ed8);">
            <label for="remember_me" style="margin:0;font-size:0.9rem;cursor:pointer;font-weight:400;">Remember me for 30 days</label>
        </div>
        <button type="submit" name="login" class="btn-primary">Log In</button>
    </form>

    <div class="auth-divider">or</div>

    <button type="button" class="btn-social" onclick="window.location.href='../../Controllers/Auth/google_auth.php'">
        <img src="../../../public/img/google.png" width="18" alt="Google"> Continue with Google
    </button>
    <button type="button" class="btn-social" onclick="window.location.href='../../Controllers/Auth/facebook_auth.php'">
        <img src="../../../public/img/facebook.png" width="18" alt="Facebook"> Continue with Facebook
    </button>

    <div class="auth-footer">
        Don't have an account? <a href="sign_up_email.php">Sign up now</a>
    </div>
</div>

</body>
</html>
