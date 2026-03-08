<?php
session_start();
require_once '../../../config/db_config.php';

$message = "";
$error = "";

if (isset($_POST['signup'])) {
    // ១. ទទួលទិន្នន័យពី Form (ប្រើអក្សរតូចធំឱ្យត្រូវតាម name="..." ក្នុង input)
    $username_val = trim(htmlspecialchars($_POST['Username'])); 
    $email_val    = trim(htmlspecialchars($_POST['Email']));
    $pass_val     = trim(htmlspecialchars($_POST['Password']));
    
    if (!empty($username_val) && !empty($email_val) && !empty($pass_val)) {
        try {
            // ២. ពិនិត្យមើល Email ស្ទួន (ប្រើ 'Email' តាមឈ្មោះ Column ក្នុង DB)
            $checkEmail = $conn->prepare("SELECT Email FROM users WHERE Email = :Email");
            $checkEmail->bindParam(':Email', $email_val);
            $checkEmail->execute();

            if ($checkEmail->rowCount() > 0) {
                $error = "This Email is already registered!";
            } else {
                // $hashed_password = password_hash($pass_val, PASSWORD_DEFAULT);
                // ៣. បញ្ចូលទិន្នន័យ (កែពី 'name' មកជា 'Username' ឱ្យត្រូវតាម Table Structure របស់អ្នក)
                $stmt = $conn->prepare("INSERT INTO users (Username, Email, Password) VALUES (:Username, :Email, :Password)");
                $stmt->bindParam(':Username', $username_val);
                $stmt->bindParam(':Email', $email_val);
                
                // $stmt->bindParam(':Password', $hashed_password);
                $stmt->bindParam(':Password', $pass_val);
                
                // កែសម្រួលក្នុង sign_up_email.php ត្រង់ផ្នែក $stmt->execute()
                if ($stmt->execute()){
                    // ចាប់យក ID ដែលទើបតែបង្កើតថ្មីៗ
                    $new_user_id = $conn->lastInsertId(); 
                    
                    // បង្កើត Session ភ្លាមៗដើម្បីឱ្យឆាតដើរ
                    $_SESSION['user_id'] = $new_user_id;
                    $_SESSION['username'] = $username_val;
                    
                    $message = "Account **" . $username_val . "** Created Successfully!";
                    // បាញ់ទៅកាន់ Dashboard តែម្ដង
                    // header("Refresh: 2; url=../Home/dashboard.php"); 
                } else {
                    $error = "Failed to create account.";
                }
            }
        } catch (PDOException $e) {
            // បើនៅ Error វានឹងបង្ហាញសារប្រាប់យើងនៅទីនេះ
            $error = "Database Error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all fields!";
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
    <link rel="stylesheet" href="../../../public/css/Login/sign_up_email.css">
</head>
<body>

<div class="card">
    <h1 class="logo">LocalConnect</h1>
    <p class="subtitle">Connect with your community</p>

    <div class="tab-container">
        <a href="sign_up_email.php" class="tab active"><span>📧</span> Email</a>
        <a href="sign_up_phone.php" class="tab"><span>📞</span> Phone</a>
    </div>

    <?php if($message): ?>
        <div class="msg success">✅ <?php echo $message; ?></div>
    <?php endif; ?>

    <?php if($error): ?>
        <div class="msg error">❌ <?php echo $error; ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="Username" placeholder="Enter your Username" required autofocus>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="Email" placeholder="Enter your Email" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="Password" placeholder="Enter your Password" required>
        </div>

        <button type="submit" name="signup" class="btn-signup">Sign Up</button>
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