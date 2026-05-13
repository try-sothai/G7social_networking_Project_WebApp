<?php
session_start();
require_once '../../../config/db_config.php';

// AJAX handler for Firebase phone login
if (isset($_POST['action']) && $_POST['action'] === 'firebase_phone_login') {
    header('Content-Type: application/json');

    if (!isset($_POST['phone'])) {
        echo json_encode(['success' => false, 'message' => 'Phone number not provided.']);
        exit();
    }

    try {
        $phone = $_POST['phone'];

        // Find user by phone number. Your database seems to use 'Phone'.
        // If your column is `phone_number`, change `Phone` to `phone_number` below.
        $stmt = $conn->prepare("SELECT UserID, Username, Email, Role, AccountStatus FROM users WHERE Phone = :phone LIMIT 1");
        $stmt->bindParam(':phone', $phone);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (($user['AccountStatus'] ?? 'Active') !== 'Active') {
                 echo json_encode(['success' => false, 'message' => 'Account is not active.']);
                 exit();
            }

            // User exists, log them in
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['UserID'];
            $_SESSION['Email'] = $user['Email'];
            $_SESSION['username'] = $user['Username'];
            $_SESSION['user_role'] = $user['Role'] ?? 'User';
            $_SESSION['account_status'] = $user['AccountStatus'] ?? 'Active';

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User with this phone number not found. Please sign up.']);
        }
    } catch (PDOException $e) {
        // In a production app, you should log this error instead of echoing.
        error_log('Firebase login error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error during login.']);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social7 - Phone Login</title>
    
<link rel="stylesheet" href="../../../public/css/Login/auth.css">
<link rel="stylesheet" href="../../../public/css/fontawesome/all.min.css">
    <link rel="stylesheet" href="../../../public/css/fonts/fonts.css">
<style>
    .otp-section {
        display: none; /* Initially hidden */
        margin-top: 15px;
    }
</style>
</head>
<body>

<div class="auth-card">
    <h1 class="auth-logo">Social7</h1>
    <p class="auth-subtitle">Connect with your community</p>

    <div class="auth-tabs">
        <a href="login_gmail.php" class="auth-tab"><span><i class="fa-regular fa-envelope"></i></span> Email</a>
        <a href="login_phone.php" class="auth-tab active"><span><i class="fa-solid fa-phone"></i></span> Phone</a>
    </div>

    <div id="auth-messages"></div>

    <div class="auth-form">
        <div id="step-1">
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" placeholder="+855 12345678" required autofocus>
            </div>
            <div id="recaptcha-container"></div>
            <button type="button" onclick="sendOTP()" class="btn-primary">Send OTP</button>
        </div>

        <div id="step-2" class="otp-section">
            <div class="form-group">
                <label for="otp_code">Enter 6-digit OTP</label>
                <input type="text" id="otp_code" placeholder="Enter OTP code" maxlength="6">
            </div>
            <button type="button" onclick="verifyOTP()" class="btn-primary">Verify & Log In</button>
        </div>
    </div>

    <div class="auth-divider">or</div>

    <button type="button" class="btn-social" onclick="window.location.href='../../Controllers/Auth/google_auth.php'">
        <img src="../../../public/img/google.png" width="18" alt="Google">
        Continue with Google
    </button>

    <button type="button" class="btn-social" onclick="window.location.href='../../Controllers/Auth/facebook_auth.php'">
        <img src="../../../public/img/facebook.png" width="18" alt="Facebook">
        Continue with Facebook
    </button>

    <div class="auth-footer">
        don't have an account? <a href="sign_up_phone.php">Sign up now</a>
    </div>
</div>

<script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-auth-compat.js"></script>

<script>
  // Your web app's Firebase configuration from your instructions
  const firebaseConfig = {
    apiKey: "AIzaSyBtPwcXmOtm9hfhAgtwVoEq5uzhR4aXw7M",
    authDomain: "social7-auth.firebaseapp.com",
    projectId: "social7-auth",
    storageBucket: "social7-auth.firebasestorage.app",
    messagingSenderId: "879266826673",
    appId: "1:879266826673:web:af4a873b712fc9bb03d590",
    measurementId: "G-85S0R9RZ3L"
  };

  // Initialize Firebase
  firebase.initializeApp(firebaseConfig);
  const auth = firebase.auth();

  window.onload = function() {
    // This renders the invisible reCAPTCHA
    window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
      'size': 'invisible'
    });
  };

  function showMessage(msg, isError = false) {
      const container = document.getElementById('auth-messages');
      if (container) {
        container.innerHTML = `<div class="auth-msg ${isError ? 'error' : 'success'}">${msg}</div>`;
      }
  }

  function sendOTP() {
    const phoneNumber = document.getElementById('phone').value;
    const appVerifier = window.recaptchaVerifier;
    
    auth.signInWithPhoneNumber(phoneNumber, appVerifier)
      .then((confirmationResult) => {
        window.confirmationResult = confirmationResult;
        showMessage("OTP has been sent!");
        document.getElementById('step-1').style.display = 'none';
        document.getElementById('step-2').style.display = 'block';
      }).catch((error) => {
        showMessage(error.message, true);
        // Reset reCAPTCHA so user can try again.
        grecaptcha.reset();
      });
  }

  function verifyOTP() {
    const code = document.getElementById('otp_code').value;
    if (!code || code.length !== 6) {
        showMessage('Please enter a valid 6-digit OTP.', true);
        return;
    }

    confirmationResult.confirm(code).then((result) => {
      const user = result.user;
      showMessage("Verification successful! Logging you in...");

      const formData = new FormData();
      formData.append('action', 'firebase_phone_login');
      formData.append('phone', user.phoneNumber);

      fetch('login_phone.php', { method: 'POST', body: formData })
      .then(response => response.json())
      .then(data => {
          if (data.success) {
              window.location.href = '<?php echo BASE_URL; ?>app/Views/Home/dashboard.php';
          } else {
              showMessage('Login failed: ' + data.message, true);
          }
      })
      .catch(error => showMessage('An error occurred while communicating with the server.', true));
    }).catch((error) => {
      showMessage("Invalid OTP code!", true);
    });
  }
</script>
</body>
</html>
