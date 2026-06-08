<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmail(string $toEmail, string $toName, string $subject, string $htmlBody): string
{
    if (MAIL_USER === '' || MAIL_APP_PASSWORD === '') {
        return 'MAIL_USER or MAIL_APP_PASSWORD is empty';
    }
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USER;
        $mail->Password   = MAIL_APP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom(MAIL_USER, 'WBDLS — Delta State Polytechnic');
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
