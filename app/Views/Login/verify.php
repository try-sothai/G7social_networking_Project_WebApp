<?php
session_start();
require_once '../../../config/db_config.php';

$message = '';
$error = '';

if (isset($_GET['token'])) {
    $token = trim($_GET['token']);
    
    if (!empty($token)) {
        try {
            $stmt = $conn->prepare("SELECT UserID, AccountStatus FROM users WHERE VerificationToken = :token LIMIT 1");
            $stmt->execute([':token' => $token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                if ($user['AccountStatus'] === 'Active') {
                    $message = 'Your account is already verified! You can log in now.';
                } else {
                    $update = $conn->prepare("UPDATE users SET AccountStatus = 'Active', VerificationToken = NULL WHERE UserID = :id");
                    if ($update->execute([':id' => $user['UserID']])) {
                        $message = 'Your account has been successfully verified! You can now log in.';
                    } else {
                        $error = 'Failed to verify account. Please try again.';
                    }
                }
            } else {
                $error = 'Invalid or expired verification token.';
            }
        } catch (PDOException $e) {
            $error = 'Database error during verification.';
        }
    } else {
        $error = 'No token provided.';
    }
} else {
    $error = 'Invalid request.';
}
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Verification - Social7</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/css/Login/auth.css">
</head>
<body>
<div class="auth-card" style="text-align: center;">
    <h1 class="auth-logo">Social7</h1>
    <h2 style="margin-bottom: 20px; font-size: 20px;">Email Verification</h2>
    
    <?php if ($message): ?>
        <div class="auth-msg success" style="margin-bottom: 20px;"><?php echo htmlspecialchars($message); ?></div>
        <a href="login_gmail.php" class="btn-primary" style="display: inline-block; text-decoration: none;">Go to Login</a>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="auth-msg error" style="margin-bottom: 20px;"><?php echo htmlspecialchars($error); ?></div>
        <a href="sign_up_email.php" class="btn-primary" style="display: inline-block; text-decoration: none; background: #64748b;">Back to Sign Up</a>
    <?php endif; ?>
</div>
</body>
</html>