<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
validateCsrf();

$id = (int)($_POST['id'] ?? 0);
if ($id < 1) {
    setFlash('error', 'Invalid session.');
    redirect('/admin/academic_sessions.php');
}

$pdo = Database::getConnection();

// Unset all, then set the chosen one
$pdo->exec("UPDATE academic_sessions SET is_current = FALSE");
$pdo->prepare("UPDATE academic_sessions SET is_current = TRUE WHERE id = ?")->execute([$id]);

setFlash('success', 'Current session updated.');
redirect('/admin/academic_sessions.php');
