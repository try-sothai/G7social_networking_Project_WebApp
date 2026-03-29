<?php
session_start();

if(isset($_POST['send_otp'])){
    $phone = trim($_POST['phone']);
    if(!empty($phone)){
     
        $otp = rand(100000, 999999);
        $_SESSION['phone'] = $phone;
        $_SESSION['otp'] = $otp;

        header("Location: login_interotp.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social7 - Phone Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../../public/css/Login/auth.css">
</head>
<body>

<div class="auth-card">
    <h1 class="auth-logo">Social7</h1>
    <p class="auth-subtitle">Connect with your community</p>

    <div class="auth-tabs">
        <a href="login_gmail.php" class="auth-tab"><span>📧</span> Email</a>
        <a href="login_phone.php" class="auth-tab active"><span>📞</span> Phone</a>
    </div>

    <form class="auth-form" method="POST">
        <div class="form-group">
            <label>Phone Number</label>
            <input type="text" name="phone" placeholder="+1 (555) 000-0000" required autofocus>
        </div>

        <button type="submit" name="send_otp" class="btn-primary">
            Send OTP
        </button>
    </form>

    <div class="auth-divider">or</div>

    <button type="button" class="btn-social">
        <img src="https://www.google.com/favicon.ico" width="18" alt="Google">
        Continue with Google
    </button>

    <button type="button" class="btn-social">
        <img src="https://www.facebook.com/favicon.ico" width="18" alt="Facebook">
        Continue with Facebook
    </button>

    <div class="auth-footer">
        don't have an account? <a href="sign_up_phone.php">Sign up now</a>
    </div>
</div>

</body>
</html>
