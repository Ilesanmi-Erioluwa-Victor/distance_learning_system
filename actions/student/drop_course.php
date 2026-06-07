<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('student');
validateCsrf();

$uid = (int)currentUserId();
$courseId = (int)($_POST['course_id'] ?? 0);
if (!$courseId) redirect('/student/courses.php');

$pdo = Database::getConnection();
$stmt = $pdo->prepare("UPDATE enrollments SET status = 'dropped' WHERE student_id = ? AND course_id = ?");
$stmt->execute([$uid, $courseId]);

setFlash('success', 'Course dropped.');
redirect('/student/courses.php');
