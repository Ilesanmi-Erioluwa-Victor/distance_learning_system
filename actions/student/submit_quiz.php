<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('student');
validateCsrf();

$uid = (int)currentUserId();
$quizId = (int)($_POST['quiz_id'] ?? 0);
$answersJson = $_POST['answers'] ?? '{}';
$answers = json_decode($answersJson, true) ?: [];

if (!$quizId) { redirect('/student/dashboard.php'); }

$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$quizId]);
$quiz = $stmt->fetch();
if (!$quiz) { setFlash('error', 'Quiz not found.'); redirect('/student/dashboard.php'); }

// Verify enrollment
$stmt = $pdo->prepare("SELECT 1 FROM enrollments WHERE student_id = ? AND course_id = ?");
$stmt->execute([$uid, $quiz['course_id']]);
if (!$stmt->fetch()) { setFlash('error', 'Not enrolled.'); redirect('/student/dashboard.php'); }

// Check attempts
$stmt = $pdo->prepare("SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = ? AND student_id = ?");
$stmt->execute([$quizId, $uid]);
$attempts = (int) $stmt->fetchColumn();
if ($attempts >= $quiz['max_attempts']) {
    setFlash('error', 'No more attempts allowed.');
    redirect('/student/dashboard.php');
}

// Get questions
$stmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ?");
$stmt->execute([$quizId]);
$questions = $stmt->fetchAll();

$totalPoints = 0;
$score = 0;
foreach ($questions as $q) {
    $totalPoints += (int)$q['points'];
    if (isset($answers[$q['id']]) && $answers[$q['id']] === $q['correct_answer']) {
        $score += (int)$q['points'];
    }
}
$percentage = $totalPoints > 0 ? round(($score / $totalPoints) * 100, 2) : 0;
$passed = $percentage >= (int)$quiz['pass_mark'] ? 1 : 0;

$stmt = $pdo->prepare("
    INSERT INTO quiz_attempts (quiz_id, student_id, answers, score, percentage, passed, completed_at)
    VALUES (?, ?, ?, ?, ?, ?, NOW())
");
$stmt->execute([$quizId, $uid, json_encode($answers), $score, $percentage, $passed]);
$attemptId = (int) $pdo->lastInsertId();

createNotification($uid, 'You scored ' . $percentage . '% on "' . $quiz['title'] . '"', 'grade', '/student/quiz_result.php?attempt_id=' . $attemptId);

redirect('/student/quiz_result.php?attempt_id=' . $attemptId);
