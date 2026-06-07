<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('instructor');
validateCsrf();

$qid = (int)($_POST['quiz_id'] ?? 0);
$cid = (int)($_POST['course_id'] ?? 0);
$pdo = Database::getConnection();
$pdo->prepare("DELETE FROM quizzes WHERE id = ?")->execute([$qid]);
setFlash('success', 'Quiz deleted.');
redirect('/instructor/quiz_builder.php?course_id=' . $cid);
