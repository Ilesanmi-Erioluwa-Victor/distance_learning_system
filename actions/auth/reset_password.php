<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('/login.php'); }
validateCsrf();

$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$otp   = trim($_POST['otp'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';

if (!$email || !$otp || !$password) {
    setFlash('error', 'All fields are required.');
    redirect('/reset_password.php?email=' . urlencode($email ?? ''));
}
if (strlen($password) < 6) {
    setFlash('error', 'Password must be at least 6 characters.');
    redirect('/reset_password.php?email=' . urlencode($email));
}
if ($password !== $confirm) {
    setFlash('error', 'Passwords do not match.');
    redirect('/reset_password.php?email=' . urlencode($email));
}

$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND otp_code = ? AND otp_expires_at > NOW()");
$stmt->execute([$email, $otp]);
$user = $stmt->fetch();

if (!$user) {
    setFlash('error', 'Invalid or expired code.');
    redirect('/reset_password.php?email=' . urlencode($email));
}

$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
$stmt = $pdo->prepare("UPDATE users SET password = ?, otp_code = NULL, otp_expires_at = NULL WHERE id = ?");
$stmt->execute([$hash, $user['id']]);

setFlash('success', 'Password reset! You can now log in.');
redirect('/login.php');
