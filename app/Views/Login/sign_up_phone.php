<?php
session_start();

if(isset($_POST['send_otp'])){
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
</head>
<body>

<div class="auth-card">
    <h1 class="auth-logo">Social7</h1>
    <p class="auth-subtitle">Connect with your community</p>

    <div class="auth-tabs">
        <a href="sign_up_email.php" class="auth-tab"><span>📧</span> Email</a>
        <a href="sign_up_phone.php" class="auth-tab active"><span>📞</span> Phone</a>
    </div>

    <form class="auth-form" action="sign_up_phone.php" method="POST">
    <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" placeholder="Enter your name" required autofocus>
    </div>
    <div class="form-group">
        <label>Phone Number</label>
        <input type="text" name="phone" placeholder="+855..." required>
    </div>
    <button type="submit" name="send_otp" class="btn-primary">Send OTP</button>
</form>

    <div class="auth-divider">or</div>

    <button type="button" class="btn-social">
        <img src="https://www.google.com/favicon.ico" width="18" alt="Google"> Continue with Google
    </button>
    <button type="button" class="btn-social">
        <img src="https://www.facebook.com/favicon.ico" width="18" alt="Facebook"> Continue with Facebook
    </button>

    <div class="auth-footer">
        Already have an account? <a href="login_phone.php">Log in</a>
    </div>
</div>

</body>
</html>
