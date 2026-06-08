<?php

function sendEmail(string $toEmail, string $toName, string $subject, string $htmlBody): string
{
    // Use Brevo (Sendinblue) HTTP API — works on Render (port 443)
    $brevoKey = defined('BREVO_API_KEY') ? BREVO_API_KEY : (getenv('BREVO_API_KEY') ?: '');
    if ($brevoKey !== '') {
        return sendViaBrevo($brevoKey, $toEmail, $toName, $subject, $htmlBody);
    }

    // Fallback: try PHPMailer SMTP
    $from = defined('MAIL_USER') ? MAIL_USER : (getenv('MAIL_USER') ?: '');
    $pass = defined('MAIL_APP_PASSWORD') ? MAIL_APP_PASSWORD : (getenv('MAIL_APP_PASSWORD') ?: '');
    if ($from === '' || $pass === '') {
        return 'No mail service configured. Set BREVO_API_KEY or MAIL_USER/MAIL_APP_PASSWORD.';
    }
    return sendViaPHPMailer($from, $pass, $toEmail, $toName, $subject, $htmlBody);
}

function sendViaPHPMailer(string $from, string $pass, string $toEmail, string $toName, string $subject, string $htmlBody): string
{
    usePHPMailer();
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->SMTPAuth   = true;
        $mail->Username   = $from;
        $mail->Password   = $pass;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ],
        ];
        $mail->setFrom($from, 'DSPoly e-Learning Portal');
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->send();
        return '';
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}

function sendViaBrevo(string $apiKey, string $toEmail, string $toName, string $subject, string $htmlBody): string
{
    $from = defined('MAIL_USER') ? MAIL_USER : (getenv('MAIL_USER') ?: 'ilesanmierioluwavictor@gmail.com');

    $payload = json_encode([
        'sender'     => ['email' => $from, 'name' => 'DSPoly e-Learning Portal'],
        'to'         => [['email' => $toEmail, 'name' => $toName]],
        'subject'    => $subject,
        'htmlContent' => $htmlBody,
    ]);

    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            'api-key: ' . $apiKey,
            'Content-Type: application/json',
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) return 'cURL error: ' . $error;
    if ($httpCode >= 200 && $httpCode < 300) return '';
    return "Brevo error (HTTP $httpCode): " . $response;
}

function usePHPMailer(): void
{
    static $loaded = false;
    if ($loaded) return;
    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($autoload)) require_once $autoload;
    $loaded = true;
}

function getOtpEmailHtml(string $firstName, string $otp): string
{
    return '
    <!DOCTYPE html>
    <html>
    <head>
      <style>
        body { font-family: Segoe UI, sans-serif; background: #f8fafc; margin:0; padding:20px; }
        .container { max-width: 500px; margin: auto; background: #fff;
                     border-radius: 10px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,.1); }
        .header { background: #1D4ED8; padding: 24px 32px; color: #fff; }
        .header h1 { margin:0; font-size:20px; }
        .header p { margin:4px 0 0; font-size:13px; opacity:.85; }
        .body { padding: 32px; }
        .otp-box { background: #dbeafe; border: 2px dashed #1D4ED8;
                   border-radius: 8px; text-align: center; padding: 20px; margin: 24px 0; }
        .otp-box span { font-size: 36px; font-weight: 700; letter-spacing: 10px; color: #1D4ED8; }
        .footer { background: #f1f5f9; padding: 16px 32px; font-size: 12px; color: #64748b;
                  text-align: center; }
      </style>
    </head>
    <body>
      <div class="container">
        <div class="header">
          <h1>Delta State Polytechnic</h1>
          <p>Web-Based Distance Learning System</p>
        </div>
        <div class="body">
          <p>Hello <strong>' . htmlspecialchars($firstName) . '</strong>,</p>
          <p>Use the code below to verify your email address. This code expires in <strong>10 minutes</strong>.</p>
          <div class="otp-box"><span>' . $otp . '</span></div>
          <p>If you did not create an account, please ignore this email.</p>
        </div>
        <div class="footer">
          &copy; ' . date('Y') . ' Delta State Polytechnic, Otefe-Oghara, Delta State, Nigeria
        </div>
      </div>
    </body>
    </html>';
}
