<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('instructor');
validateCsrf();

$uid = (int)currentUserId();
$title = sanitize($_POST['title'] ?? '');
$content = sanitize($_POST['content'] ?? '');
$target = $_POST['target'] ?? 'all';
$courseId = (int)($_POST['course_id'] ?? 0) ?: null;

if (!in_array($target, ['all','students','instructors'], true)) $target = 'all';
if (!$title || !$content) { setFlash('error', 'Title and content required.'); redirect('/instructor/announcements.php'); }

$pdo = Database::getConnection();
$stmt = $pdo->prepare("INSERT INTO announcements (author_id, course_id, title, content, target) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$uid, $courseId, $title, $content, $target]);

setFlash('success', 'Announcement posted.');
redirect('/instructor/announcements.php');
