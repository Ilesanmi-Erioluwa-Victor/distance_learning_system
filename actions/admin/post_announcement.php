<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
validateCsrf();

$uid = (int)currentUserId();
$title = sanitize($_POST['title'] ?? '');
$content = sanitize($_POST['content'] ?? '');
$target = $_POST['target'] ?? 'all';
if (!in_array($target, ['all','students','instructors'], true)) $target = 'all';
if (!$title || !$content) { setFlash('error', 'Title and content required.'); redirect('/admin/announcements.php'); }

$pdo = Database::getConnection();
$stmt = $pdo->prepare("INSERT INTO announcements (author_id, course_id, title, content, target) VALUES (?, NULL, ?, ?, ?)");
$stmt->execute([$uid, $title, $content, $target]);
setFlash('success', 'Announcement posted.');
redirect('/admin/announcements.php');
