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

// Prevent deleting the current session
$stmt = $pdo->prepare("SELECT is_current FROM academic_sessions WHERE id = ?");
$stmt->execute([$id]);
$s = $stmt->fetch();
if (!$s) {
    setFlash('error', 'Session not found.');
    redirect('/admin/academic_sessions.php');
}
if ($s['is_current']) {
    setFlash('error', 'Cannot delete the current session. Set another session as current first.');
    redirect('/admin/academic_sessions.php');
}

$pdo->prepare("DELETE FROM academic_sessions WHERE id = ?")->execute([$id]);

setFlash('success', 'Academic session deleted.');
redirect('/admin/academic_sessions.php');
