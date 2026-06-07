<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('instructor');
validateCsrf();

$uid = (int)currentUserId();
$cid = (int)($_POST['course_id'] ?? 0);
$pdo = Database::getConnection();
$stmt = $pdo->prepare("DELETE FROM courses WHERE id = ? AND instructor_id = ?");
$stmt->execute([$cid, $uid]);
setFlash('success', 'Course deleted.');
redirect('/instructor/dashboard.php');
