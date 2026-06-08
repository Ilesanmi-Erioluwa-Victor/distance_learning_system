<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
validateCsrf();

$uid = (int)currentUserId();
$courseId = (int)($_POST['course_id'] ?? 0);
if (!$courseId) { setFlash('error', 'Invalid course.'); redirect('/courses.php'); }

$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND is_published");
$stmt->execute([$courseId]);
$course = $stmt->fetch();
if (!$course) { setFlash('error', 'Course not available.'); redirect('/courses.php'); }

$stmt = $pdo->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
$stmt->execute([$uid, $courseId]);
if ($stmt->fetch()) {
    setFlash('info', 'You are already enrolled.');
    redirect('/student/courses.php');
}

$stmt = $pdo->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
$stmt->execute([$uid, $courseId]);

createNotification((int)$course['instructor_id'], $uid . ' enrolled in your course: ' . $course['title'], 'enrollment', '/instructor/students.php');

setFlash('success', 'Enrolled successfully!');

// Find first lesson
$stmt = $pdo->prepare("
    SELECT l.id FROM lessons l JOIN modules m ON l.module_id = m.id
    WHERE m.course_id = ? ORDER BY m.sort_order, l.sort_order LIMIT 1
");
$stmt->execute([$courseId]);
$firstLesson = $stmt->fetchColumn();

if ($firstLesson) {
    redirect('/student/learn.php?course_id=' . $courseId . '&lesson_id=' . (int)$firstLesson);
} else {
    redirect('/student/courses.php');
}
