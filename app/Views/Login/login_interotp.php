<?php
session_start();
require_once '../../../config/db_config.php'; // ហៅ file connection មកប្រើ

$error = "";

// ១. ឆែកមើលថា តើមានលេខទូរស័ព្ទក្នុង Session ឬអត់ (ការពារការចូលមកត្រង់ៗ)
if(!isset($_SESSION['phone'])){
    header("Location: login_phone.php"); // បើអត់មាន ឱ្យត្រឡប់ទៅវាយលេខទូរស័ព្ទវិញ
    exit();
}

if(isset($_POST['verify'])){
    $input_otp = $_POST['otp'];
    $phone = $_SESSION['phone'];

    // ២. ទាញយក OTP ពី Database ដើម្បីផ្ទៀងផ្ទាត់
    $stmt = $conn->prepare("SELECT otp_code FROM users WHERE phone_number = :phone LIMIT 1");
    $stmt->bindParam(':phone', $phone);
    $stmt->execute();
    $user = $stmt->fetch();

    if($user && $input_otp == $user['otp_code']){
        // ៣. បើត្រូវ៖ លុប OTP ចេញពី database (ដើម្បីកុំឱ្យប្រើម្តងទៀតបាន)
        $update = $conn->prepare("UPDATE users SET otp_code = NULL WHERE phone_number = :phone");
        $update->bindParam(':phone', $phone);
        $update->execute();

        echo "<script>alert('Login successful!'); window.location='../Home/dashboard.php';</script>";
    } else {
        $error = "Invalid OTP code! Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social7 - Verify OTP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../../public/css/Login/auth.css">
</head>
<body>

<div class="auth-card">
    <h1 class="auth-logo">Social7</h1>
    <p class="auth-subtitle">Connect with your community</p>

    <div class="auth-tabs">
        <a href="login_gmail.php" class="auth-tab">
            <span>📧</span> Email
        </a>
        <a href="login_phone.php" class="auth-tab active">
            <span>📞</span> Phone
        </a>
    </div>

    <?php if($error): ?>
        <div class="auth-msg error">⚠️ <?php echo $error; ?></div>
    <?php endif; ?>

    <form class="auth-form" action="" method="POST">
        <div class="form-group">
            <label>Phone Number</label>
            <input type="text" value="<?php echo $_SESSION['phone']; ?>" readonly>
        </div>

        <div class="form-group">
            <label>Enter OTP (6-digit)</label>
            <input type="text" name="otp" placeholder="0 0 0 0 0 0" maxlength="6" 
                   style="text-align: center; letter-spacing: 5px; font-weight: bold; font-size: 20px;" 
                   required autofocus>
        </div>

        <button type="submit" name="verify" class="btn-primary">Log In</button>
    </form>

    <div class="auth-divider">or</div>

    <button type="button" class="btn-social">
        <img src="https://www.google.com/favicon.ico" width="18" alt="Google"> Continue with Google
    </button>
    <button type="button" class="btn-social">
        <img src="https://www.facebook.com/favicon.ico" width="18" alt="Facebook"> Continue with Facebook
    </button>

    <div class="auth-footer">
        Didn't receive the code? <a href="login_phone.php">Resend</a>
    </div>
</div>

</body>
</html>
