<?php
session_start();
require_once '../../../config/db_config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$error = '';

if (isset($_POST['signup'])) {
    $csrf = $_POST['csrf_token'] ?? '';
    $username_val = trim($_POST['Username'] ?? '');
    $email_val = trim($_POST['Email'] ?? '');
    $pass_val = trim($_POST['Password'] ?? '');
    $confirm_val = trim($_POST['ConfirmPassword'] ?? '');

    if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
        $error = 'Invalid request token. Please refresh and try again.';
    } elseif ($username_val === '' || $email_val === '' || $pass_val === '' || $confirm_val === '') {
        $error = 'Please fill in all fields!';
    } elseif (!filter_var($email_val, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($username_val) < 3 || strlen($username_val) > 50) {
        $error = 'Username must be between 3 and 50 characters.';
    } elseif ($pass_val !== $confirm_val) {
        $error = 'Password and Confirm Password do not match.';
    } elseif (strlen($pass_val) < 8 || !preg_match('/[A-Za-z]/', $pass_val) || !preg_match('/\d/', $pass_val)) {
        $error = 'Password must be at least 8 characters and include letters and numbers.';
    } else {
        try {
            $checkEmail = $conn->prepare('SELECT UserID FROM users WHERE Email = :Email LIMIT 1');
            $checkEmail->bindValue(':Email', $email_val, PDO::PARAM_STR);
            $checkEmail->execute();

            if ($checkEmail->fetch()) {
                $error = 'This Email is already registered!';
            } else {
                $passwordHash = password_hash($pass_val, PASSWORD_DEFAULT);

                $stmt = $conn->prepare('INSERT INTO users (Username, Email, Password) VALUES (:Username, :Email, :Password)');
                $stmt->bindValue(':Username', $username_val, PDO::PARAM_STR);
                $stmt->bindValue(':Email', $email_val, PDO::PARAM_STR);
                $stmt->bindValue(':Password', $passwordHash, PDO::PARAM_STR);

                if ($stmt->execute()) {
                    $new_user_id = (int) $conn->lastInsertId();
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $new_user_id;
                    $_SESSION['username'] = $username_val;
                    $_SESSION['Email'] = $email_val;

                    header('Location: ' . BASE_URL . 'app/Views/Home/dashboard.php');
                    exit();
                }

                $error = 'Failed to create account.';
            }
        } catch (PDOException $e) {
            $error = 'Unable to create account right now. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social7 - Sign Up</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../../public/css/Login/auth.css">
</head>
<body>

<div class="auth-card">
    <h1 class="auth-logo">Social7</h1>
    <p class="auth-subtitle">Connect with your community</p>

    <div class="auth-tabs">
        <a href="sign_up_email.php" class="auth-tab active"><span>??</span> Email</a>
        <a href="sign_up_phone.php" class="auth-tab"><span>??</span> Phone</a>
    </div>

    <?php if($message): ?>
        <div class="auth-msg success">? <?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if($error): ?>
        <div class="auth-msg error">? <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form class="auth-form" action="" method="POST" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="Username" placeholder="Enter your Username" minlength="3" maxlength="50" required autofocus>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="Email" placeholder="Enter your Email" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="Password" placeholder="Enter your Password" minlength="8" required>
        </div>
        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="ConfirmPassword" placeholder="Re-enter your Password" minlength="8" required>
        </div>

        <button type="submit" name="signup" class="btn-primary">Sign Up</button>
    </form>

    <div class="auth-divider">or</div>

    <button type="button" class="btn-social">
        <img src="https://www.google.com/favicon.ico" width="18" alt="Google"> Continue with Google
    </button>
    <button type="button" class="btn-social">
        <img src="https://www.facebook.com/favicon.ico" width="18" alt="Facebook"> Continue with Facebook
    </button>

    <div class="auth-footer">
        Already have an account? <a href="login_gmail.php">Log in</a>
    </div>
</div>

</body>
</html>
