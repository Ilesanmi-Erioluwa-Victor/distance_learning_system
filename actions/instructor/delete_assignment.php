<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('instructor');
validateCsrf();

$aid = (int)($_POST['assignment_id'] ?? 0);
$pdo = Database::getConnection();
$pdo->prepare("DELETE FROM assignments WHERE id = ?")->execute([$aid]);
setFlash('success', 'Assignment deleted.');
redirect('/instructor/assignments.php');
