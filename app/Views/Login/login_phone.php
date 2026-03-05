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
    <title>LocalConnect - Phone Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/css/Login/login_phone.css">
</head>
<body>

<div class="login-card">
    <div class="logo-title">G7Connect</div>
    <div class="subtitle">Connect with your community</div>

    <div class="tab-container">
        <a href="login_gmail.php" class="tab"><span>📧</span> Email</a>
        <a href="login_phone.php" class="tab active"><span>📞</span> Phone</a>
    </div>

    <form method="POST">
        <div class="form-group">
            <label>Phone Number</label>
            <input type="text" name="phone" placeholder="+1 (555) 000-0000" required autofocus>
        </div>

        <button type="submit" name="send_otp" class="btn-main">
            Send OTP
        </button>
    </form>

    <div class="divider">or</div>

    <button type="button" class="social-btn">
        <img src="https://www.google.com/favicon.ico" width="18" alt="Google">
        Continue with Google
    </button>

    <button type="button" class="social-btn">
        <img src="https://www.facebook.com/favicon.ico" width="18" alt="Facebook">
        Continue with Facebook
    </button>

    <div class="footer-link">
        don't have an account? <a href="sign_up_phone.php">Sign up now</a>
    </div>
</div>

</body>
</html>