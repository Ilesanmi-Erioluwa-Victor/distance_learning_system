<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
validateCsrf();

$name = trim($_POST['name'] ?? '');
$sortOrder = (int)($_POST['sort_order'] ?? 0);
if ($name === '') {
    setFlash('error', 'Semester name is required.');
    redirect('/admin/semesters.php');
}

$pdo = Database::getConnection();
$stmt = $pdo->prepare("INSERT INTO semesters (name, sort_order) VALUES (?, ?)");
$stmt->execute([$name, $sortOrder]);

setFlash('success', 'Semester created.');
redirect('/admin/semesters.php');
