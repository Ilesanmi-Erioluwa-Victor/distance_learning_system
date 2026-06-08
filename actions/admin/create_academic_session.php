<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
validateCsrf();

$name = trim($_POST['name'] ?? '');
if ($name === '') {
    setFlash('error', 'Session name is required.');
    redirect('/admin/academic_sessions.php');
}

$pdo = Database::getConnection();

// If this is the first session, make it current automatically
$count = $pdo->query("SELECT COUNT(*) FROM academic_sessions")->fetchColumn();
$isCurrent = ($count == 0) ? 'TRUE' : 'FALSE';

$stmt = $pdo->prepare("INSERT INTO academic_sessions (name, is_current) VALUES (?, $isCurrent)");
$stmt->execute([$name]);

setFlash('success', 'Academic session created.');
redirect('/admin/academic_sessions.php');
