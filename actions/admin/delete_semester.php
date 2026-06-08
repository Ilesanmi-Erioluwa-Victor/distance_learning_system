<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
validateCsrf();

$id = (int)($_POST['id'] ?? 0);
if ($id < 1) {
    setFlash('error', 'Invalid semester.');
    redirect('/admin/semesters.php');
}

$pdo = Database::getConnection();
$pdo->prepare("DELETE FROM semesters WHERE id = ?")->execute([$id]);

setFlash('success', 'Semester deleted.');
redirect('/admin/semesters.php');
