<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('instructor');
validateCsrf();

$uid = (int)currentUserId();
$sid = (int)($_POST['submission_id'] ?? 0);
$aid = (int)($_POST['assignment_id'] ?? 0);
$score = (int)($_POST['score'] ?? 0);
$feedback = sanitize($_POST['feedback'] ?? '');

$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT a.max_score, s.student_id, a.title FROM submissions s JOIN assignments a ON s.assignment_id = a.id WHERE s.id = ?");
$stmt->execute([$sid]);
$row = $stmt->fetch();
if (!$row) { setFlash('error', 'Not found.'); redirect('/instructor/assignments.php'); }

if ($score < 0 || $score > (int)$row['max_score']) {
    setFlash('error', 'Invalid score.');
    redirect('/instructor/grade_submissions.php?assignment_id=' . $aid);
}

$stmt = $pdo->prepare("UPDATE submissions SET score=?, feedback=?, status='graded', graded_at=NOW() WHERE id=?");
$stmt->execute([$score, $feedback, $sid]);

createNotification((int)$row['student_id'], 'Your assignment "' . $row['title'] . '" has been graded: ' . $score . '/' . $row['max_score'], 'grade', '/student/assignments.php');

setFlash('success', 'Grade saved.');
redirect('/instructor/grade_submissions.php?assignment_id=' . $aid);
