<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('instructor');
validateCsrf();

$qid = (int)($_POST['quiz_id'] ?? 0);
$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT is_published FROM quizzes WHERE id = ?");
$stmt->execute([$qid]);
$row = $stmt->fetch();
if ($row) {
    $new = $row['is_published'] ? 0 : 1;
    $pdo->prepare("UPDATE quizzes SET is_published = ? WHERE id = ?")->execute([$new, $qid]);
}
redirect($_SERVER['HTTP_REFERER'] ?? '/instructor/dashboard.php');
