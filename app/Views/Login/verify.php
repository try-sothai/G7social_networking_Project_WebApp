<?php
session_start();
require_once '../../../config/db_config.php';

$message = '';
$error = '';

if (isset($_GET['token'])) {
    $token = trim($_GET['token']);

    if (!empty($token)) {
        try {
            $stmt = $conn->prepare("SELECT UserID, Username, Email, Role, AccountStatus FROM users WHERE VerificationToken = :token LIMIT 1");
            $stmt->execute([':token' => $token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                if ($user['AccountStatus'] === 'Active') {
                    // Already verified — just log them in and go
                    session_regenerate_id(true);
                    $_SESSION['user_id']        = (int) $user['UserID'];
                    $_SESSION['Email']          = $user['Email'];
                    $_SESSION['username']       = $user['Username'];
                    $_SESSION['user_role']      = $user['Role'] ?? 'User';
                    $_SESSION['account_status'] = 'Active';
                    header('Location: ' . BASE_URL . 'app/Views/Home/dashboard.php');
                    exit();
                } else {
                    $update = $conn->prepare("UPDATE users SET AccountStatus = 'Active', VerificationToken = NULL WHERE UserID = :id");
                    if ($update->execute([':id' => $user['UserID']])) {
                        // Auto-login right after verification
                        session_regenerate_id(true);
                        $_SESSION['user_id']        = (int) $user['UserID'];
                        $_SESSION['Email']          = $user['Email'];
                        $_SESSION['username']       = $user['Username'];
                        $_SESSION['user_role']      = $user['Role'] ?? 'User';
                        $_SESSION['account_status'] = 'Active';
                        header('Location: ' . BASE_URL . 'app/Views/Home/dashboard.php');
                        exit();
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
    <link rel="stylesheet" href="../../../public/css/login/auth.css">
</head>
<body>
<div class="auth-card" style="text-align:center;">
    <h1 class="auth-logo">Social7</h1>
    <h2 style="margin-bottom:20px;font-size:20px;">Email Verification</h2>

    <?php if ($error): ?>
        <div class="auth-msg error" style="margin-bottom:20px;"><?php echo htmlspecialchars($error); ?></div>
        <a href="sign_up_email.php" class="btn-primary" style="display:inline-block;text-decoration:none;background:#64748b;">Back to Sign Up</a>
    <?php endif; ?>
</div>
</body>
</html>