<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('student');
validateCsrf();

$uid = (int)currentUserId();
$courseId = (int)($_POST['course_id'] ?? 0);
$title = sanitize($_POST['title'] ?? '');
$content = sanitize($_POST['content'] ?? '');

if (!$courseId || !$title || !$content) {
    setFlash('error', 'Title and content are required.');
    redirect('/student/forum.php?course_id=' . $courseId);
}

$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT 1 FROM enrollments WHERE student_id = ? AND course_id = ?");
$stmt->execute([$uid, $courseId]);
if (!$stmt->fetch()) { setFlash('error', 'Not enrolled.'); redirect('/student/courses.php'); }

$stmt = $pdo->prepare("INSERT INTO forum_posts (course_id, author_id, title, content) VALUES (?, ?, ?, ?)");
$stmt->execute([$courseId, $uid, $title, $content]);

setFlash('success', 'Post created.');
redirect('/student/forum.php?course_id=' . $courseId);
