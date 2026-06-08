<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/mail.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('/login.php'); }
validateCsrf();

$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
if (!$email) { setFlash('error', 'Invalid email.'); redirect('/login.php'); }

$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && !$user['is_verified']) {
    $otp = generateOTP(6);
    $otpExpires = (new DateTime('+10 minutes'))->format('Y-m-d H:i:s');
    $stmt = $pdo->prepare("UPDATE users SET otp_code = ?, otp_expires_at = ? WHERE id = ?");
    $stmt->execute([$otp, $otpExpires, $user['id']]);
    if (defined('MAIL_USER') && MAIL_USER !== '') {
        $body = getOtpEmailHtml($user['first_name'], $otp);
        $err = sendEmail($email, $user['first_name'], 'Verify your DSPoly e-Learning account', $body);
        if ($err === '') {
            setFlash('success', 'A new code has been sent to your email.');
            redirect('/verify_email.php?email=' . urlencode($email));
        }
        setFlash('error', 'Could not send email. Mail error: ' . $err);
    } else {
        setFlash('error', 'Mail is not configured. Set MAIL_USER and MAIL_APP_PASSWORD on Render.');
    }
} else {
    setFlash('info', 'If the email exists and is unverified, a code was sent.');
}
redirect('/verify_email.php?email=' . urlencode($email));
