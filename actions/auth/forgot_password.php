<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/mail.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('/forgot_password.php'); }
validateCsrf();

$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
if (!$email) { setFlash('error', 'Invalid email.'); redirect('/forgot_password.php'); }

$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    $otp = generateOTP(6);
    $otpExpires = (new DateTime('+10 minutes'))->format('Y-m-d H:i:s');
    $stmt = $pdo->prepare("UPDATE users SET otp_code = ?, otp_expires_at = ? WHERE id = ?");
    $stmt->execute([$otp, $otpExpires, $user['id']]);
    $body = '<p>Hello ' . htmlspecialchars($user['first_name']) . ',</p>' .
            '<p>Use the code below to reset your password. It expires in 10 minutes.</p>' .
            '<div style="background:#dbeafe;border:2px dashed #1D4ED8;border-radius:8px;text-align:center;padding:20px;margin:20px 0;">' .
            '<span style="font-size:32px;font-weight:700;letter-spacing:8px;color:#1D4ED8;">' . $otp . '</span></div>' .
            '<p>If you did not request this, please ignore.</p>';
    $sent = sendEmail($email, $user['first_name'], 'Password Reset Code', $body);
    if ($sent) {
        setFlash('success', 'A reset code has been sent to your email.');
    } else {
        setFlash('warning', 'Email could not be sent. Your reset code is: ' . $otp);
    }
} else {
    setFlash('info', 'If the email exists, a reset code was sent.');
}
redirect('/reset_password.php?email=' . urlencode($email));
