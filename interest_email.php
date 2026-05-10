<?php
// Allow frontend to read response as JSON
header('Content-Type: application/json');

// Load Composer Autoloader to use PHPMailer
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Receive data from Frontend (sent as JSON via Fetch API / Axios)
$input = json_decode(file_get_contents('php://input'), true);
$userEmail = $input['userEmail'] ?? '';
$eventId = $input['eventId'] ?? '';

// Validate that email is provided
if (empty($userEmail)) {
    http_response_code(400);
    echo json_encode(['error' => 'Please provide your email address!']);
    exit;
}

$mail = new PHPMailer(true);

try {
    // --- SMTP Server Configuration ---
    // Set $mail->SMTPDebug = 2 to see detailed logs when debugging
    $mail->SMTPDebug = 0;
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'shy784162@gmail.com';   // Sender email
    $mail->Password   = 'obmnqzigmrupwhbq';      // App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // --- Set Sender and Recipient ---
    $mail->setFrom('shy784162@gmail.com', 'G7 Social Networking');
    $mail->addAddress($userEmail);

    // --- Email Content ---
    $mail->isHTML(true);
    $mail->Subject = 'Thanks for your interest in our Event!';
    $mail->Body    = "Hello,<br><br>Thank you for showing interest in our event. (Event ID: $eventId)<br><br>See you soon!";

    // Send the email
    $mail->send();

    // Return success response to Frontend
    http_response_code(200);
    echo json_encode(['message' => 'Email sent successfully!']);

} catch (Exception $e) {
    // Return error response to Frontend
    http_response_code(500);
    echo json_encode(['error' => "Failed to send email. Error: {$mail->ErrorInfo}"]);

    // Log error to Apache/PHP error log
    error_log("PHPMailer Error: " . $mail->ErrorInfo);
}