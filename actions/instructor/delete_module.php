<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('instructor');
validateCsrf();

$uid = (int)currentUserId();
$mid = (int)($_POST['module_id'] ?? 0);
$cid = (int)($_POST['course_id'] ?? 0);

$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT 1 FROM modules m JOIN courses c ON m.course_id = c.id WHERE m.id = ? AND c.instructor_id = ?");
$stmt->execute([$mid, $uid]);
if (!$stmt->fetch()) { setFlash('error', 'Not allowed.'); redirect('/instructor/dashboard.php'); }

$stmt = $pdo->prepare("DELETE FROM modules WHERE id = ?");
$stmt->execute([$mid]);
setFlash('success', 'Module deleted.');
redirect('/instructor/course_builder.php?course_id=' . $cid);
