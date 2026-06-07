<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('instructor');
validateCsrf();

$qid = (int)($_POST['quiz_id'] ?? 0);
$qText = sanitize($_POST['question_text'] ?? '');
$oA = sanitize($_POST['option_a'] ?? '');
$oB = sanitize($_POST['option_b'] ?? '');
$oC = sanitize($_POST['option_c'] ?? '');
$oD = sanitize($_POST['option_d'] ?? '');
$correct = $_POST['correct_answer'] ?? 'A';
$points = (int)($_POST['points'] ?? 1);

if (!$qid || !$qText || !$oA || !$oB || !$oC || !$oD) {
    setFlash('error', 'All fields are required.');
    redirect($_SERVER['HTTP_REFERER'] ?? '/instructor/dashboard.php');
}

$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = ?");
$stmt->execute([$qid]);
$sort = (int)$stmt->fetchColumn() + 1;

$stmt = $pdo->prepare("INSERT INTO quiz_questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_answer, points, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$qid, $qText, $oA, $oB, $oC, $oD, $correct, $points, $sort]);

setFlash('success', 'Question added.');
redirect($_SERVER['HTTP_REFERER'] ?? '/instructor/dashboard.php');
