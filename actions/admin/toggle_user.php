<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
validateCsrf();

$uid = (int)$_POST['user_id'] ?? 0;
$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT is_active FROM users WHERE id = ?");
$stmt->execute([$uid]);
$row = $stmt->fetch();
if ($row) {
    $new = $row['is_active'] ? 0 : 1;
    $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?")->execute([$new, $uid]);
}
setFlash('success', 'User status updated.');
redirect('/admin/users.php');
