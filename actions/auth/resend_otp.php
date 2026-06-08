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
    $sent = false;
    if (defined('MAIL_USER') && MAIL_USER !== '') {
        $body = getOtpEmailHtml($user['first_name'], $otp);
        $err = sendEmail($email, $user['first_name'], 'Verify your WBDLS account', $body);
        $sent = ($err === '');
        if (!$sent) error_log('Mail error (resend): ' . $err);
    }
    if ($sent) {
        setFlash('success', 'A new code has been sent to your email.');
    } else {
        setFlash('info', 'Your new verification code is: ' . $otp);
    }
} else {
    setFlash('info', 'If the email exists and is unverified, a code was sent.');
}
redirect('/verify_email.php?email=' . urlencode($email));
