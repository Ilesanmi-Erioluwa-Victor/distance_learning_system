<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
validateCsrf();

$uid = (int)currentUserId();
$lessonId = (int)($_POST['lesson_id'] ?? 0);
$courseId = (int)($_POST['course_id'] ?? 0);
$nextLessonId = (int)($_POST['next_lesson_id'] ?? 0);

if (!$lessonId || !$courseId) { redirect('/student/dashboard.php'); }

$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT 1 FROM enrollments WHERE student_id = ? AND course_id = ?");
$stmt->execute([$uid, $courseId]);
if (!$stmt->fetch()) { setFlash('error', 'Not enrolled.'); redirect('/student/dashboard.php'); }

$stmt = $pdo->prepare("
    INSERT INTO lesson_progress (student_id, lesson_id, completed, completed_at)
    VALUES (?, ?, TRUE, NOW())
    ON CONFLICT (student_id, lesson_id) DO UPDATE SET completed = TRUE, completed_at = NOW()
");
$stmt->execute([$uid, $lessonId]);

$progress = calculateCourseProgress($uid, $courseId);
$stmt = $pdo->prepare("UPDATE enrollments SET progress = ? WHERE student_id = ? AND course_id = ?");
$stmt->execute([$progress, $uid, $courseId]);

if ($progress >= 100) {
    $stmt = $pdo->prepare("UPDATE enrollments SET status = 'completed' WHERE student_id = ? AND course_id = ?");
    $stmt->execute([$uid, $courseId]);
    setFlash('success', '🎉 Congratulations! You completed the course!');
}

if ($nextLessonId) {
    redirect('/student/learn.php?course_id=' . $courseId . '&lesson_id=' . $nextLessonId);
} else {
    redirect('/student/learn.php?course_id=' . $courseId . '&lesson_id=' . $lessonId);
}
