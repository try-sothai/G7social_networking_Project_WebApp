<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

function lc_mailer_config(): array
{
    $host = trim((string) (getenv('SMTP_HOST') ?: ''));
    $username = trim((string) (getenv('SMTP_USERNAME') ?: ''));
    $password = trim((string) (getenv('SMTP_PASSWORD') ?: ''));
    $port = (int) (getenv('SMTP_PORT') ?: 0);
    $fromEmail = trim((string) (getenv('SMTP_FROM_EMAIL') ?: ''));
    $fromName = trim((string) (getenv('SMTP_FROM_NAME') ?: ''));

    if ($host === '') {
        $host = 'smtp.gmail.com';
    }

    if ($username === '') {
        $username = 'shy784162@gmail.com';
    }

    if ($password === '') {
        $password = 'obmnqzigmrupwhbq';
    }

    if ($port <= 0) {
        $port = 587;
    }

    if ($fromEmail === '') {
        $fromEmail = $username;
    }

    if ($fromName === '') {
        $fromName = 'LocalConnect';
    }

    return [
        'host' => $host,
        'username' => $username,
        'password' => $password,
        'port' => $port,
        'from_email' => $fromEmail,
        'from_name' => $fromName,
    ];
}

function lc_send_verification_email(string $toEmail, string $toName, string $verifyUrl): array
{
    $config = lc_mailer_config();
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $config['port'];
        $mail->CharSet = 'UTF-8';

        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        $mail->Subject = 'Verify your Social7 account';
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 520px; margin: 0 auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 16px; background: #ffffff;'>
                <h2 style='margin: 0 0 12px; color: #0f172a;'>Verify your Social7 account</h2>
                <p style='margin: 0 0 20px; color: #475569; line-height: 1.6;'>Hello <strong>" . htmlspecialchars($toName, ENT_QUOTES, 'UTF-8') . "</strong>, click the button below to activate your account.</p>
                <div style='text-align: center; margin: 24px 0;'>
                    <a href='" . htmlspecialchars($verifyUrl, ENT_QUOTES, 'UTF-8') . "' style='display: inline-block; padding: 14px 32px; background: #1d4ed8; color: #ffffff; text-decoration: none; border-radius: 10px; font-weight: 600; font-size: 16px;'>Verify Email</a>
                </div>
                <p style='margin: 0 0 8px; color: #94a3b8; font-size: 13px;'>This link will expire in 24 hours.</p>
                <p style='margin: 0; color: #94a3b8; font-size: 13px;'>If you did not create this account, you can ignore this email.</p>
            </div>
        ";
        $mail->AltBody = 'Verify your Social7 account by visiting: ' . $verifyUrl;
        $mail->send();

        return ['success' => true];
    } catch (Exception $e) {
        error_log('Verification email error: ' . $mail->ErrorInfo);
        return ['success' => false];
    }
}

function lc_send_otp_email(string $toEmail, string $toName, string $otp): array
{
    $config = lc_mailer_config();
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $config['port'];
        $mail->CharSet = 'UTF-8';

        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        $mail->Subject = 'Your Social7 verification code';
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 520px; margin: 0 auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 16px; background: #ffffff;'>
                <h2 style='margin: 0 0 12px; color: #0f172a;'>Verify your Social7 sign up</h2>
                <p style='margin: 0 0 16px; color: #475569; line-height: 1.6;'>Hello <strong>" . htmlspecialchars($toName, ENT_QUOTES, 'UTF-8') . "</strong>, use the code below to finish creating your account.</p>
                <div style='margin: 20px 0; padding: 18px; border-radius: 14px; background: #eff6ff; text-align: center;'>
                    <div style='font-size: 30px; font-weight: 700; letter-spacing: 8px; color: #1d4ed8;'>" . htmlspecialchars($otp, ENT_QUOTES, 'UTF-8') . "</div>
                </div>
                <p style='margin: 0 0 8px; color: #475569;'>This code will expire in 5 minutes.</p>
                <p style='margin: 0; color: #94a3b8; font-size: 13px;'>If you did not request this code, you can ignore this email.</p>
            </div>
        ";
        $mail->AltBody = 'Your Social7 verification code is ' . $otp . '. This code expires in 5 minutes.';
        $mail->send();

        return ['success' => true, 'message' => 'OTP email sent successfully.'];
    } catch (Exception $e) {
        error_log('OTP email error: ' . $mail->ErrorInfo);

        return ['success' => false, 'message' => 'Unable to send the OTP email right now. Please try again.'];
    }
}
