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
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND otp_code = ? AND otp_expires_at > NOW()");
$stmt->execute([$email, $otp]);
$user = $stmt->fetch();

if (!$user) {
    setFlash('error', 'Invalid or expired code.');
    redirect('/verify_email.php?email=' . urlencode($email));
}

$stmt = $pdo->prepare("UPDATE users SET is_verified = 1, otp_code = NULL, otp_expires_at = NULL WHERE id = ?");
$stmt->execute([$user['id']]);

setFlash('success', 'Email verified! You can now log in.');
redirect('/login.php');
