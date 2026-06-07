<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('instructor');
validateCsrf();

$uid = (int)currentUserId();
$lid = (int)($_POST['lesson_id'] ?? 0);
$cid = (int)($_POST['course_id'] ?? 0);

$pdo = Database::getConnection();
$stmt = $pdo->prepare("
    SELECT 1 FROM lessons l
    JOIN modules m ON l.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    WHERE l.id = ? AND c.instructor_id = ?
");
$stmt->execute([$lid, $uid]);
if (!$stmt->fetch()) { setFlash('error', 'Not allowed.'); redirect('/instructor/dashboard.php'); }

$pdo->prepare("DELETE FROM lessons WHERE id = ?")->execute([$lid]);
setFlash('success', 'Lesson deleted.');
redirect('/instructor/course_builder.php?course_id=' . $cid);
