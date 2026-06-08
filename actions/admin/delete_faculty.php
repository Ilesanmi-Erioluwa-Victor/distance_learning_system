<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
validateCsrf();

$id = (int)($_POST['id'] ?? 0);
if ($id < 1) {
    setFlash('error', 'Invalid faculty.');
    redirect('/admin/faculties.php');
}

$pdo = Database::getConnection();
$pdo->prepare("DELETE FROM faculties WHERE id = ?")->execute([$id]);

setFlash('success', 'Faculty deleted.');
redirect('/admin/faculties.php');
