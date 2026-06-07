<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
validateCsrf();

$cid = (int)($_POST['course_id'] ?? 0);
$pdo = Database::getConnection();
$pdo->prepare("DELETE FROM courses WHERE id = ?")->execute([$cid]);
setFlash('success', 'Course deleted.');
redirect('/admin/courses.php');
