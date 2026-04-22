<?php
// អនុញ្ញាតឲ្យ Frontend អានទិន្នន័យជាទម្រង់ JSON
header('Content-Type: application/json');

// ទាញយក Autoloader របស់ Composer ដើម្បីប្រើប្រាស់ PHPMailer
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ទទួលទិន្នន័យពី Frontend (ឧបមាថាបញ្ជូនមកជា JSON តាមរយៈ Fetch API / Axios)
$input = json_decode(file_get_contents('php://input'), true);
$userEmail = $input['userEmail'] ?? '';
$eventId = $input['eventId'] ?? '';

// ឆែកមើលថាតើមាន Email ដែរឬទេ
if (empty($userEmail)) {
    http_response_code(400);
    echo json_encode(['error' => 'សូមបញ្ជាក់អាសយដ្ឋាន Email របស់អ្នកសិន!']);
    exit;
}

$mail = new PHPMailer(true);

try {
    // --- ការកំណត់ SMTP Server ---
    // បើចង់មើល Log ពេល Error សូមដូរ $mail->SMTPDebug ទៅលេខ 2
    $mail->SMTPDebug = 0;                      
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';             // ដាក់ SMTP Server (ឧ. Gmail)
    $mail->SMTPAuth   = true;
    $mail->Username   = 'your_email@gmail.com';       // ដាក់ Email របស់អ្នកផ្ញើ
    $mail->Password   = 'your_app_password';          // ដាក់ App Password (មិនមែន Password ដើមទេ)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // --- កំណត់អ្នកផ្ញើ និងអ្នកទទួល ---
    $mail->setFrom('your_email@gmail.com', 'G7 Social Networking');
    $mail->addAddress($userEmail);

    // --- ខ្លឹមសារ Email (Content) ---
    $mail->isHTML(true);
    $mail->Subject = 'Thanks for your interest in our Event!';
    $mail->Body    = "សួស្តី,<br><br>អរគុណដែលបានចាប់អារម្មណ៍ (Interest) លើ Event របស់យើង។ (Event ID: $eventId)<br><br>ជួបគ្នាឆាប់ៗនេះ!";

    // ដំណើរការផ្ញើ
    $mail->send();
    
    // ផ្ញើ Status 200 ទៅ Frontend វិញថាជោគជ័យ
    http_response_code(200);
    echo json_encode(['message' => 'Email sent successfully!']);
} catch (Exception $e) {
    // ផ្ញើ Status 500 ប្រាប់ Frontend ថាមានបញ្ហា និងបង្ហាញ Error
    http_response_code(500);
    echo json_encode(['error' => "មិនអាចផ្ញើ Email បានទេ។ បញ្ហា៖ {$mail->ErrorInfo}"]);
    
    // កត់ត្រា Error ទុកក្នុង Error Log របស់ Apache/PHP
    error_log("PHPMailer Error: " . $mail->ErrorInfo);
}