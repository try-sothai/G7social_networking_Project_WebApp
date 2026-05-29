<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

function lc_env(string $key): string
{
    $v = getenv($key);
    if ($v === false || $v === '') {
        $v = $_ENV[$key] ?? ($_SERVER[$key] ?? '');
    }
    return trim((string) $v);
}

// Send via Brevo API (free, no domain needed — just verify sender email)
function lc_send_via_brevo(string $toEmail, string $toName, string $subject, string $htmlBody, string $altBody): array
{
    $apiKey = lc_env('BREVO_API_KEY');
    if ($apiKey === '') {
        return ['success' => false, 'error' => 'BREVO_API_KEY not set'];
    }

    $fromName  = lc_env('SMTP_FROM_NAME') ?: 'Social7';
    $fromEmail = lc_env('SMTP_FROM_EMAIL') ?: lc_env('SMTP_USERNAME');

    $payload = json_encode([
        'sender'      => ['name' => $fromName, 'email' => $fromEmail],
        'to'          => [['email' => $toEmail, 'name' => $toName]],
        'subject'     => $subject,
        'htmlContent' => $htmlBody,
        'textContent' => $altBody,
    ]);

    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'api-key: ' . $apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_TIMEOUT => 15,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($httpCode === 201) {
        return ['success' => true];
    }

    $decoded = json_decode($response, true);
    $errMsg  = $decoded['message'] ?? ($curlErr ?: $response);
    error_log('Brevo API error [' . $httpCode . ']: ' . $errMsg);
    return ['success' => false, 'error' => 'Brevo error: ' . $errMsg];
}

// Send via Resend HTTP API (works on Render - uses HTTPS port 443)
function lc_send_via_resend(string $toEmail, string $toName, string $subject, string $htmlBody, string $altBody): array
{
    $apiKey = lc_env('RESEND_API_KEY');
    if ($apiKey === '') {
        return ['success' => false, 'error' => 'RESEND_API_KEY not set'];
    }

    $fromName  = lc_env('SMTP_FROM_NAME') ?: 'Social7';
    $fromEmail = lc_env('SMTP_FROM_EMAIL') ?: 'onboarding@resend.dev';

    $payload = json_encode([
        'from'    => $fromName . ' <' . $fromEmail . '>',
        'to'      => [$toEmail],
        'subject' => $subject,
        'html'    => $htmlBody,
        'text'    => $altBody,
    ]);

    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT        => 15,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($httpCode === 200 || $httpCode === 201) {
        return ['success' => true];
    }

    $decoded = json_decode($response, true);
    $errMsg  = $decoded['message'] ?? ($curlErr ?: $response);
    error_log('Resend API error [' . $httpCode . ']: ' . $errMsg);
    return ['success' => false, 'error' => 'Resend error: ' . $errMsg];
}

// Send via SMTP (for local development on WAMP)
function lc_send_via_smtp(string $toEmail, string $toName, string $subject, string $htmlBody, string $altBody): array
{
    $host      = lc_env('SMTP_HOST') ?: 'smtp.gmail.com';
    $username  = lc_env('SMTP_USERNAME');
    $password  = lc_env('SMTP_PASSWORD');
    $port      = (int) (lc_env('SMTP_PORT') ?: 587);
    $fromEmail = lc_env('SMTP_FROM_EMAIL') ?: $username;
    $fromName  = lc_env('SMTP_FROM_NAME') ?: 'Social7';

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $username;
        $mail->Password   = $password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $port;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $altBody;
        $mail->send();

        return ['success' => true];
    } catch (Exception $e) {
        error_log('SMTP error [host=' . $host . ' port=' . $port . ' user=' . $username . ']: ' . $mail->ErrorInfo);
        return ['success' => false, 'error' => $mail->ErrorInfo];
    }
}

function lc_send_verification_email(string $toEmail, string $toName, string $verifyUrl): array
{
    $subject  = 'Verify your Social7 account';
    $htmlBody = "
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
    $altBody = 'Verify your Social7 account by visiting: ' . $verifyUrl;

    if (lc_env('BREVO_API_KEY') !== '') {
        return lc_send_via_brevo($toEmail, $toName, $subject, $htmlBody, $altBody);
    }
    if (lc_env('BREVO_API_KEY') !== '') {
        return lc_send_via_brevo($toEmail, $toName, $subject, $htmlBody, $altBody);
    }
    if (lc_env('RESEND_API_KEY') !== '') {
        return lc_send_via_resend($toEmail, $toName, $subject, $htmlBody, $altBody);
    }
    return lc_send_via_smtp($toEmail, $toName, $subject, $htmlBody, $altBody);
}

function lc_send_otp_email(string $toEmail, string $toName, string $otp): array
{
    $subject  = 'Your Social7 verification code';
    $htmlBody = "
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
    $altBody = 'Your Social7 verification code is ' . $otp . '. This code expires in 5 minutes.';

    if (lc_env('BREVO_API_KEY') !== '') {
        return lc_send_via_brevo($toEmail, $toName, $subject, $htmlBody, $altBody);
    }
    if (lc_env('RESEND_API_KEY') !== '') {
        return lc_send_via_resend($toEmail, $toName, $subject, $htmlBody, $altBody);
    }
    return lc_send_via_smtp($toEmail, $toName, $subject, $htmlBody, $altBody);
}
