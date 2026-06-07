<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('/login.php'); }
validateCsrf();

$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$password = $_POST['password'] ?? '';
$redirectTo = $_POST['redirect'] ?? '';

if (!$email || !$password) {
    setFlash('error', 'Please provide email and password.');
    redirect('/login.php');
}

$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    setFlash('error', 'Invalid email or password.');
    redirect('/login.php?redirect=' . urlencode($redirectTo));
}
if (!$user['is_verified']) {
    setFlash('warning', 'Please verify your email first.');
    redirect('/verify_email.php?email=' . urlencode($email));
}
if (!$user['is_active']) {
    setFlash('error', 'Your account has been disabled. Contact admin.');
    redirect('/login.php');
}
if (!password_verify($password, $user['password'])) {
    setFlash('error', 'Invalid email or password.');
    redirect('/login.php?redirect=' . urlencode($redirectTo));
}

setUserSession($user);
setFlash('success', 'Welcome back, ' . $user['first_name'] . '!');

if ($redirectTo && strpos($redirectTo, '/') === 0) {
    header('Location: ' . BASE_URL . $redirectTo);
    exit;
}
redirect('/' . $user['role'] . '/dashboard.php');
