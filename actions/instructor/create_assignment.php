<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('instructor');
validateCsrf();

$uid = (int)currentUserId();
$cid = (int)($_POST['course_id'] ?? 0);
$title = sanitize($_POST['title'] ?? '');
$description = sanitize($_POST['description'] ?? '');
$due = $_POST['due_date'] ?? '';
$max = (int)($_POST['max_score'] ?? 100);

if (!$cid || !$title || !$description || !$due) { setFlash('error', 'All fields required.'); redirect('/instructor/assignments.php?course_id=' . $cid); }
$mid = (int)($_POST['module_id'] ?? 0);
if (!$mid) { setFlash('error', 'Module is required.'); redirect('/instructor/assignments.php?course_id=' . $cid); }

$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT 1 FROM modules m JOIN courses c ON m.course_id = c.id WHERE m.id = ? AND c.instructor_id = ?");
$stmt->execute([$mid, $uid]);
if (!$stmt->fetch()) { setFlash('error', 'Not allowed.'); redirect('/instructor/dashboard.php'); }

$dueDt = (new DateTime($due))->format('Y-m-d H:i:s');
$stmt = $pdo->prepare("INSERT INTO assignments (module_id, title, description, due_date, max_score) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$mid, $title, $description, $dueDt, $max]);

setFlash('success', 'Assignment created.');
redirect('/instructor/assignments.php?course_id=' . $cid);
