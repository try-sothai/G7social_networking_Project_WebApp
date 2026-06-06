<?php

require_once __DIR__ . '/../../../config/db_config.php';
require_once __DIR__ . '/../../Helpers/admin.php';

lc_admin_ensure_schema($conn);

// AJAX handler for Firebase phone signup
if (isset($_POST['action']) && $_POST['action'] === 'firebase_phone_signup') {
    header('Content-Type: application/json');

    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($name) || empty($phone)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
        exit();
    }
    
    // Generate random password since phone signup doesn't require one
    $password = bin2hex(random_bytes(8));

    try {
        // Check if phone or email already exists
        $query = "SELECT UserID, Phone FROM users WHERE Phone = :phone LIMIT 1";

        $checkExisting = $conn->prepare($query);
        $checkExisting->bindValue(':phone', $phone, PDO::PARAM_STR);
        $checkExisting->execute();
        $existingUser = $checkExisting->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            echo json_encode(['success' => false, 'message' => 'This phone number is already registered.']);
            exit();
        }

        // Insert new user
        $stmt = $conn->prepare('INSERT INTO users (Username, Email, Phone, Password, Role, AccountStatus) VALUES (:username, :email, :phone, :password, :role, :status)');
        $stmt->bindValue(':username', $name, PDO::PARAM_STR);
        $stmt->bindValue(':email', null, PDO::PARAM_NULL); // កែសម្រួលឱ្យមានតែមួយខ្សែ និងប្រើ PARAM_NULL
        $stmt->bindValue(':phone', $phone, PDO::PARAM_STR);
        $stmt->bindValue(':password', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);
        $stmt->bindValue(':role', 'User', PDO::PARAM_STR);
        $stmt->bindValue(':status', 'Active', PDO::PARAM_STR);

        if ($stmt->execute()) {
            $newUserId = (int) $conn->lastInsertId();
            session_regenerate_id(true);
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['username'] = $name;
            $_SESSION['Email'] = '';
            $_SESSION['user_role'] = 'User';
            $_SESSION['account_status'] = 'Active';

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create your account.']);
        }
    } catch (PDOException $e) {
        error_log('Firebase signup error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error during sign up.']);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social7 - Sign Up with Phone</title>
    
<link rel="stylesheet" href="../../../public/css/login/auth.css">
<link rel="stylesheet" href="../../../public/css/fontawesome/all.min.css">
    <link rel="stylesheet" href="../../../public/css/fonts/fonts.css">
</head>
<body>

<div class="auth-card">
    <h1 class="auth-logo">Social7</h1>
    <p class="auth-subtitle">Sign up with your phone number</p>

    <div class="auth-tabs">
        <a href="sign_up_email.php" class="auth-tab"><span><i class="fa-regular fa-envelope"></i></span> Email</a>
        <a href="sign_up_phone.php" class="auth-tab active"><span><i class="fa-solid fa-phone"></i></span> Phone</a>
    </div>

    <div id="auth-messages"></div>

    <form class="auth-form" id="signup-form" onsubmit="return false;">
        <div id="step-1">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" placeholder="Enter your name" minlength="3" maxlength="50" required autofocus>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" placeholder="+855 12345678" required>
            </div>
            <div id="recaptcha-container"></div>
            <button type="button" id="btn-send-otp" onclick="sendSignupOTP()" class="btn-primary">Send OTP to Phone</button>
        </div>

        <div id="step-2" class="otp-section" style="display: none;">
            <div class="form-group">
                <label for="otp_code">Enter 6-digit OTP</label>
                <input type="text" id="otp_code" placeholder="Enter OTP code" maxlength="6">
            </div>
            <button type="button" onclick="verifySignupOTP()" class="btn-primary">Verify & Sign Up</button>
        </div>
    </form>

    <div class="auth-divider">or</div>

    <button type="button" class="btn-social" onclick="window.location.href='../../Controllers/Auth/google_auth.php'">
        <img src="../../../public/img/google.png" width="18" alt="Google"> Continue with Google
    </button>
    <button type="button" class="btn-social" onclick="window.location.href='../../Controllers/Auth/facebook_auth.php'">
        <img src="../../../public/img/facebook.png" width="18" alt="Facebook"> Continue with Facebook
    </button>

    <div class="auth-footer">
        Already have an account? <a href="login_phone.php">Log in</a>
    </div>
</div>

<script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.22.1/firebase-auth-compat.js"></script>

<script>
  const firebaseConfig = {
    apiKey: "AIzaSyBtPwcXmOtm9hfhAgtwVoEq5uzhR4aXw7M",
    authDomain: "social7-auth.firebaseapp.com",
    projectId: "social7-auth",
    storageBucket: "social7-auth.firebasestorage.app",
    messagingSenderId: "879266826673",
    appId: "1:879266826673:web:af4a873b712fc9bb03d590",
    measurementId: "G-85S0R9RZ3L"
  };

  firebase.initializeApp(firebaseConfig);
  const auth = firebase.auth();

  window.onload = function() {
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

  function sendSignupOTP() {
    const name = document.getElementById('name').value;
    const phone = document.getElementById('phone').value;

    if (!name || !phone) {
        showMessage('Please fill in all required fields.', true);
        return;
    }

    const appVerifier = window.recaptchaVerifier;
    
    auth.signInWithPhoneNumber(phone, appVerifier)
      .then((confirmationResult) => {
        window.confirmationResult = confirmationResult;
        showMessage("OTP has been sent to your phone!");
        document.getElementById('step-1').style.display = 'none';
        document.getElementById('step-2').style.display = 'block';
      }).catch((error) => {
        showMessage(error.message, true);
        grecaptcha.reset();
      });
  }

  function verifySignupOTP() {
    const code = document.getElementById('otp_code').value;
    if (!code || code.length !== 6) {
        showMessage('Please enter a valid 6-digit OTP.', true);
        return;
    }

    confirmationResult.confirm(code).then((result) => {
      const user = result.user;
      showMessage("Verification successful! Creating account...");

      const formData = new FormData();
      formData.append('action', 'firebase_phone_signup');
      formData.append('name', document.getElementById('name').value);
      formData.append('phone', user.phoneNumber);

      fetch('sign_up_phone.php', { method: 'POST', body: formData })
      .then(response => response.json())
      .then(data => {
          if (data.success) {
              window.location.href = '<?php echo BASE_URL; ?>app/Views/Home/dashboard.php';
          } else {
              showMessage('Sign up failed: ' + data.message, true);
          }
      })
      .catch(error => showMessage('Server error occurred.', true));
    }).catch((error) => {
      showMessage("Invalid OTP code!", true);
    });
  }
</script>
</body>
</html>
