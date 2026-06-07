<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('instructor');
validateCsrf();

$qid = (int)($_POST['question_id'] ?? 0);
$cid = (int)($_POST['course_id'] ?? 0);
$pdo = Database::getConnection();
$pdo->prepare("DELETE FROM quiz_questions WHERE id = ?")->execute([$qid]);
setFlash('success', 'Question deleted.');
redirect('/instructor/quiz_builder.php?course_id=' . $cid);
