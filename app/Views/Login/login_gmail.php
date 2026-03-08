<?php
session_start();
require_once '../../../config/db_config.php'; // call db_config.php to connect to the database

if(isset($_POST['login'])) {
    $Email = trim($_POST['Email']);
    $Password = trim($_POST['Password']);

    if(!empty($Email) && !empty($Password)){
        try {
            // write SQL statement to check if the Email exists in the database
            $stmt = $conn->prepare("SELECT * FROM users WHERE Email = :Email LIMIT 1");
            $stmt->bindParam(':Email', $Email);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // កែសម្រួលក្នុង login_gmail.php ត្រង់ផ្នែកពិនិត្យ User
        if($user){
            // កែពី 'id' មកជា 'UserID' (ឱ្យត្រូវតាម DB Column របស់បង)
            $_SESSION['user_id'] = $user['UserID']; 
            $_SESSION['Email']   = $user['Email'];
            $_SESSION['username'] = $user['Username']; // ទុកប្រើបង្ហាញឈ្មោះលើ Header

            header("Location: ../Home/dashboard.php");
            exit();
        } else {
                // if the Email does not exist, show an alert message
                // echo "<script>alert('Not found Email This is it!');</script>"; 
            }
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>G7Connect - Login</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../../public/css/Login/login_gmail.css">
</head>
<body>
<div class="login-card">
    <h1>G7Connect</h1>
    <p>Connect with your community</p>

    <div class="tab-container">
        <a href="login_gmail.php" class="tab active">
            <span>📧</span> Email
        </a>
        <a href="login_phone.php" class="tab">
            <span>📞</span> Phone
        </a>
    </div>

     <form action="" method="POST">
        <div class="form-group">
            <label>Email</label>
            <input type="Email" name="Email" placeholder="Enter your Email address" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="Password" placeholder="Enter your Password" required>
        </div>   
        <button type="submit" name="login" class="btn-login">Log In</button>
    </form>

    <div class="divider">or</div>

    <button type="button" class="social-btn">
        <img src="https://www.google.com/favicon.ico" width="18" alt="Google"> Continue with Google
    </button>
    <button type="button" class="social-btn">
        <img src="https://www.facebook.com/favicon.ico" width="18" alt="Facebook"> Continue with Facebook
    </button>

    <div class="footer-text">
        Don't have an account? <a href="sign_up_email.php">Sign up now</a>
    </div>
</div>

</body>
</html>