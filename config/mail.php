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

    $textContent = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</div>', '</h1>', '</h2>', '</h3>', '</li>'], "\n", $htmlBody));
    $textContent = preg_replace('/\n\s*\n\s*\n/', "\n\n", $textContent);

    $payload = json_encode([
        'sender'      => ['email' => $from, 'name' => 'DSPoly e-Learning Portal'],
        'to'          => [['email' => $toEmail, 'name' => $toName]],
        'subject'     => $subject,
        'htmlContent' => $htmlBody,
        'textContent' => trim($textContent),
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
    $name = htmlspecialchars($firstName);
    $year = date('Y');
    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body { margin:0; padding:0; background:#f4f6f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }
    .wrapper { padding: 32px 16px; }
    .container { max-width:520px; margin:0 auto; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,0.08); }
    .header { background:linear-gradient(135deg, #1D4ED8, #3B82F6); padding:28px 36px; text-align:center; }
    .header h1 { margin:0; color:#ffffff; font-size:22px; font-weight:700; letter-spacing:0.3px; }
    .header p { margin:6px 0 0; color:rgba(255,255,255,0.85); font-size:13px; }
    .body { padding:36px; }
    .body p { color:#334155; font-size:15px; line-height:1.6; margin:0 0 16px; }
    .otp-label { text-align:center; color:#64748b; font-size:13px; text-transform:uppercase; letter-spacing:1.5px; margin-bottom:8px; }
    .otp-box { background:#EEF2FF; border:2px dashed #6366F1; border-radius:12px; text-align:center; padding:16px; margin:16px 0 24px; }
    .otp-box span { font-size:40px; font-weight:800; letter-spacing:12px; color:#1D4ED8; font-family: 'Courier New', Courier, monospace; }
    .divider { height:1px; background:#e2e8f0; margin:24px 0; }
    .footer { background:#f8fafc; padding:20px 36px; text-align:center; }
    .footer p { color:#94a3b8; font-size:12px; line-height:1.5; margin:0; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="container">
      <div class="header">
        <h1>Delta State Polytechnic</h1>
        <p>Web-Based Distance Learning System</p>
      </div>
      <div class="body">
        <p>Hello <strong>$name</strong>,</p>
        <p>Thank you for creating an account. Use the verification code below to complete your registration. This code expires in <strong>10 minutes</strong>.</p>
        <div class="otp-label">Verification Code</div>
        <div class="otp-box"><span>$otp</span></div>
        <p style="font-size:13px; color:#94a3b8;">If you did not create this account, please ignore this email.</p>
      </div>
      <div class="divider"></div>
      <div class="footer">
        <p>&copy; $year Delta State Polytechnic, Otefe-Oghara, Delta State, Nigeria</p>
      </div>
    </div>
  </div>
</body>
</html>
HTML;
}
