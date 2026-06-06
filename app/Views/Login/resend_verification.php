<?php
require_once '../../../config/db_config.php';
require_once __DIR__ . '/../../../app/Helpers/mailer.php';

$message = '';
$error = '';

$emailParam = trim($_GET['email'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } else {
        try {
            $stmt = $conn->prepare('SELECT UserID, Username, Email, AccountStatus FROM users WHERE Email = :email LIMIT 1');
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $error = 'No account found with that email.';
            } elseif (strtolower($user['AccountStatus']) !== 'pending') {
                $message = 'This account is already verified. <a href="login_gmail.php">Log in</a>';
            } else {
                $newToken = bin2hex(random_bytes(32));
                $upd = $conn->prepare('UPDATE users SET VerificationToken = :token WHERE UserID = :id');
                $upd->execute([':token' => $newToken, ':id' => $user['UserID']]);

                $verifyUrl = BASE_URL . 'app/Views/Login/verify.php?token=' . urlencode($newToken);
                $sent = lc_send_verification_email($user['Email'], $user['Username'], $verifyUrl);

                if ($sent['success']) {
                    $message = 'Verification email sent! Please check your inbox.';
                } else {
                    $error = 'Could not send email. Please try again later or contact support.';
                }
            }
        } catch (PDOException $e) {
            $error = 'Database error. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resend Verification - Social7</title>
    <link rel="stylesheet" href="../../../public/css/login/auth.css">
</head>
<body>
<div class="auth-card" style="text-align:center;">
    <h1 class="auth-logo">Social7</h1>
    <h2 style="font-size:18px;margin-bottom:8px;">Resend Verification Email</h2>
    <p style="color:#64748b;font-size:14px;margin-bottom:20px;">Enter your email and we'll send a new verification link.</p>

    <?php if ($message): ?>
        <div class="auth-msg success" style="background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;padding:12px;border-radius:8px;margin-bottom:16px;">
            <?php echo $message; ?>
        </div>
        <a href="login_gmail.php" class="btn-primary" style="display:block;text-decoration:none;">Back to Login</a>
    <?php else: ?>
        <?php if ($error): ?>
            <div class="auth-msg error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form class="auth-form" action="" method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($emailParam); ?>" placeholder="Enter your email" required>
            </div>
            <button type="submit" class="btn-primary">Send Verification Email</button>
        </form>
        <div class="auth-footer" style="margin-top:16px;">
            <a href="login_gmail.php">Back to Login</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
