<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
validateCsrf();

$uid = (int)currentUserId();
$current = $_POST['current_password'] ?? '';
$new = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if (!$current || !$new || strlen($new) < 6) {
    setFlash('error', 'Password must be at least 6 characters.');
    redirect('/' . currentUserRole() . '/profile.php');
}
if ($new !== $confirm) {
    setFlash('error', 'New passwords do not match.');
    redirect('/' . currentUserRole() . '/profile.php');
}

$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$uid]);
$row = $stmt->fetch();

if (!password_verify($current, $row['password'])) {
    setFlash('error', 'Current password is incorrect.');
    redirect('/' . currentUserRole() . '/profile.php');
}

$hash = password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]);
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->execute([$hash, $uid]);

setFlash('success', 'Password updated.');
redirect('/' . currentUserRole() . '/profile.php');
