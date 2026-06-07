<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('instructor');
validateCsrf();

$uid = (int)currentUserId();
$cid = (int)($_POST['course_id'] ?? 0);
$qid = (int)($_POST['quiz_id'] ?? 0);
$title = sanitize($_POST['title'] ?? '');
$description = sanitize($_POST['description'] ?? '');
$timeLimit = $_POST['time_limit'] !== '' ? (int)$_POST['time_limit'] : null;
$maxAttempts = (int)($_POST['max_attempts'] ?? 1);
$passMark = (int)($_POST['pass_mark'] ?? 50);

if (!$title) { setFlash('error', 'Title required.'); redirect('/instructor/quiz_builder.php?course_id=' . $cid); }

$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT 1 FROM courses WHERE id = ? AND instructor_id = ?");
$stmt->execute([$cid, $uid]);
if (!$stmt->fetch()) { setFlash('error', 'Not allowed.'); redirect('/instructor/dashboard.php'); }

if ($qid) {
    $stmt = $pdo->prepare("UPDATE quizzes SET title=?, description=?, time_limit=?, max_attempts=?, pass_mark=? WHERE id=?");
    $stmt->execute([$title, $description, $timeLimit, $maxAttempts, $passMark, $qid]);
} else {
    $stmt = $pdo->prepare("INSERT INTO quizzes (course_id, title, description, time_limit, max_attempts, pass_mark) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$cid, $title, $description, $timeLimit, $maxAttempts, $passMark]);
    $qid = (int) $pdo->lastInsertId();
}

setFlash('success', 'Quiz saved.');
redirect('/instructor/quiz_builder.php?course_id=' . $cid . '&quiz_id=' . $qid);
