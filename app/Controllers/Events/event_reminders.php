<?php
// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader (ផ្លាស់ប្តូរទីតាំងទៅតាមជាក់ស្តែង)
require_once __DIR__ . '/../../../vendor/autoload.php';
// ទីតាំង File config ភ្ជាប់ទៅកាន់ Database
require_once __DIR__ . '/../../../config/db_config.php';

// កំណត់ Timezone ទៅជាម៉ោងនៅកម្ពុជា
date_default_timezone_set('Asia/Phnom_Penh');

try {
    // ធានាថាការទាញទិន្នន័យអាចអានអក្សរខ្មែរ (UTF-8) បានត្រឹមត្រូវ
    $conn->exec("SET NAMES 'utf8mb4'");

    // ទាញយក Event ដែលនឹងចាប់ផ្តើមនៅ "ថ្ងៃស្អែក" ព្រមទាំងអ្នកដែលបានចុចចូលរួម (Interested)
    $query = "
        SELECT e.EventID, e.Title, e.Description, e.EventDateTime, e.Location,
               u.Email, u.Username
        FROM events e
        JOIN event_participants ep ON e.EventID = ep.EventID
        JOIN users u ON ep.UserID = u.UserID
        WHERE DATE(e.EventDateTime) = DATE(DATE_ADD(NOW(), INTERVAL 1 DAY))
          AND ep.Status IN ('Going', 'Interested')
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $reminders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // បង្កើត Object PHPMailer ថ្មី
    $mail = new PHPMailer(true);
    
    // កំណត់រចនាសម្ព័ន្ធ SMTP (ឧទាហរណ៍៖ ប្រើ Gmail)
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'shy784162@gmail.com'; // ដាក់ Email របស់អ្នកទីនេះ
    $mail->Password   = 'obmnqzigmrupwhbq';    // ដាក់ App Password របស់អ្នកទីនេះ
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    
    $mail->CharSet    = 'UTF-8'; // កំណត់ CharSet ជា UTF-8 ដើម្បីផ្ញើអក្សរខ្មែរមិនឲ្យ Error
    
    $mail->setFrom('shy784162@gmail.com', 'LocalConnect');
    $mail->isHTML(true); // កំណត់ថាជាទម្រង់ HTML

    $count = 0;
    foreach ($reminders as $row) {
        try {
            $mail->clearAddresses(); // លុប Email ចាស់ចេញមុននឹងផ្ញើទៅអ្នកថ្មី
            $mail->addAddress($row['Email'], $row['Username']);
            
            $mail->Subject = "Reminder: Upcoming Event - " . $row['Title'];
            
            $time = date('g:i A', strtotime($row['EventDateTime']));
            
            // ទម្រង់ HTML ស្អាតៗ
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);'>
                    <img src='https://images.unsplash.com/photo-1505373877841-8d25f7d46678?auto=format&fit=crop&w=600&q=80' alt='Event Banner' style='width: 100%; height: 180px; object-fit: cover; border-radius: 8px; margin-bottom: 20px;'>
                    <h2 style='color: #6366f1; text-align: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px;'>Upcoming Event Reminder</h2>
                    <p style='font-size: 16px; color: #334155;'>Hello <strong>{$row['Username']}</strong>,</p>
                    <p style='font-size: 15px; color: #475569; line-height: 1.6;'>This is a friendly reminder that the event <strong>'{$row['Title']}'</strong> is happening tomorrow!</p>
                    <div style='background: #f8fafc; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #6366f1;'>
                        <p style='margin: 8px 0; font-size: 15px; color: #1e293b;'>📍 <strong>Location:</strong> {$row['Location']}</p>
                        <p style='margin: 8px 0; font-size: 15px; color: #1e293b;'>⏰ <strong>Time:</strong> {$time}</p>
                        <p style='margin: 8px 0; font-size: 15px; color: #1e293b;'>📝 <strong>Details:</strong> {$row['Description']}</p>
                    </div>
                    <div style='text-align: center; margin: 25px 0;'>
                        <a href='http://localhost/G7social_networking_Project_WebApp/app/Views/Events/events.php' style='background: #6366f1; color: #ffffff; text-decoration: none; padding: 12px 25px; border-radius: 8px; font-weight: bold; display: inline-block; box-shadow: 0 4px 6px rgba(99, 102, 241, 0.25);'>View Event</a>
                    </div>
                    <p style='font-size: 15px; color: #475569;'>We hope to see you there!</p>
                    <p style='color: #94a3b8; font-size: 13px; margin-top: 30px; text-align: center;'>Best regards,<br><strong>LocalConnect Team</strong></p>
                </div>
            ";
            
            $mail->send();
            $count++;
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }
    
    echo "Successfully sent {$count} reminder emails.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>