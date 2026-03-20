<?php
session_start();
require_once '../../../config/db_config.php';

// បើមិនទាន់ឆ្លងកាត់ទំព័រទី១ មិនឱ្យចូលទំព័រនេះឡើយ
if (!isset($_SESSION['phone'])) {
    header("Location: sign_up_phone.php");
    exit();
}

$error = "";
$message = "";
$phone = $_SESSION['phone'];
$name = $_SESSION['signup_name'];

if (isset($_POST['signup'])) {
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
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LocalConnect - Sign Up</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/css/Login/sign_up_phone_otp.css">
</head>
<body>
<div class="card">
    <h1 class="logo">LocalConnect</h1>
    <p class="subtitle">Verify OTP for <strong><?php echo $phone; ?></strong></p>
    <div class="tab-container">
        <a href="sign_up_email.php" class="tab"><span>📧</span> Email</a>
        <a href="sign_up_phone.php" class="tab active"><span>📞</span> Phone</a>
    </div>
    <?php if($message) echo "<div class='msg success'>✅ $message</div>"; ?>
    <?php if($error) echo "<div class='msg error'>❌ $error</div>"; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label>Name</label>
            <input type="text" value="<?php echo $name; ?>" class="readonly-input" readonly>
        </div>

        <div class="form-group">
            <label>Phone Number</label>
            <input type="text" value="<?php echo $phone; ?>" class="readonly-input" readonly>
        </div>

        <div class="form-group">
            <label>Enter 6-digit OTP</label>
            <input type="text" name="otp" placeholder="Enter OTP" maxlength="6" required autofocus>
            <small style="color: #64748b;">Code sent to 123456</small>
        </div>

        <button type="submit" name="signup" class="btn-signup">Verify & Sign Up</button>
    </form>

    <div class="divider">or</div>

    <button type="button" class="social-btn">
        <img src="https://www.google.com/favicon.ico" width="18" alt="Google"> Continue with Google
    </button>
    <button type="button" class="social-btn">
        <img src="https://www.facebook.com/favicon.ico" width="18" alt="Facebook"> Continue with Facebook
    </button>

    <div class="footer-text">
        Already have an account? <a href="login_gmail.php">Log in</a>
    </div>
</div>

</body>
</html>
