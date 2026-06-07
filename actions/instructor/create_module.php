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
$sort = (int)($_POST['sort_order'] ?? 0);

if (!$cid || !$title) { setFlash('error', 'Title required.'); redirect('/instructor/course_builder.php?course_id=' . $cid); }

$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT 1 FROM courses WHERE id = ? AND instructor_id = ?");
$stmt->execute([$cid, $uid]);
if (!$stmt->fetch()) { setFlash('error', 'Not allowed.'); redirect('/instructor/dashboard.php'); }

$stmt = $pdo->prepare("INSERT INTO modules (course_id, title, description, sort_order) VALUES (?, ?, ?, ?)");
$stmt->execute([$cid, $title, $description, $sort]);
setFlash('success', 'Module added.');
redirect('/instructor/course_builder.php?course_id=' . $cid);
