<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('/login.php'); }
validateCsrf();

$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$otp   = trim($_POST['otp'] ?? '');

if (!$email || !$otp) {
    setFlash('error', 'Email and code are required.');
    redirect('/verify_email.php?email=' . urlencode($email ?? ''));
}

$pdo = Database::getConnection();

// Debug: fetch user and log OTP info
$checkStmt = $pdo->prepare("SELECT id, otp_code, otp_expires_at, NOW() as db_now FROM users WHERE email = ?");
$checkStmt->execute([$email]);
$check = $checkStmt->fetch();
if ($check) {
    error_log("OTP verify: email=$email otp_submitted=$otp otp_db={$check['otp_code']} expires={$check['otp_expires_at']} db_now={$check['db_now']}");
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND otp_code = ? AND otp_expires_at > NOW()");
$stmt->execute([$email, $otp]);
$user = $stmt->fetch();

if (!$user) {
    $debug = '';
    if ($check) {
        $debug = ' | DB has OTP=' . var_export($check['otp_code'], true) . ' Submitted=' . var_export($otp, true);
    }
    setFlash('error', 'Invalid or expired code.' . $debug);
    redirect('/verify_email.php?email=' . urlencode($email));
}

$stmt = $pdo->prepare("UPDATE users SET is_verified = TRUE, otp_code = NULL, otp_expires_at = NULL WHERE id = ?");
$stmt->execute([$user['id']]);

setFlash('success', 'Email verified! You can now log in.');
redirect('/login.php');
