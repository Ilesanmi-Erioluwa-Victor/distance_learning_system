<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
validateCsrf();

$uid = (int)$_POST['user_id'] ?? 0;
$me = (int)currentUserId();
if ($uid === $me) { setFlash('error', 'You cannot delete yourself.'); redirect('/admin/users.php'); }

$pdo = Database::getConnection();
$pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$uid]);
setFlash('success', 'User deleted.');
redirect('/admin/users.php');
