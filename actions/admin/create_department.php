<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
validateCsrf();

$facultyId = (int)($_POST['faculty_id'] ?? 0);
$name      = trim($_POST['name'] ?? '');

if ($facultyId < 1 || $name === '') {
    setFlash('error', 'Faculty and department name are required.');
    redirect('/admin/departments.php');
}

$pdo = Database::getConnection();
$stmt = $pdo->prepare("INSERT INTO departments (faculty_id, name) VALUES (?, ?)");
$stmt->execute([$facultyId, $name]);

setFlash('success', 'Department created.');
redirect('/admin/departments.php');
