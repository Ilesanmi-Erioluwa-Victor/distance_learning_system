<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('student');
validateCsrf();

$uid = (int)currentUserId();
$postId = (int)($_POST['post_id'] ?? 0);
$content = sanitize($_POST['content'] ?? '');

if (!$postId || !$content) {
    setFlash('error', 'Reply is required.');
    redirect('/student/forum_post.php?post_id=' . $postId);
}

$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT course_id FROM forum_posts WHERE id = ?");
$stmt->execute([$postId]);
$post = $stmt->fetch();
if (!$post) { setFlash('error', 'Post not found.'); redirect('/student/dashboard.php'); }

$stmt = $pdo->prepare("INSERT INTO forum_replies (post_id, author_id, content) VALUES (?, ?, ?)");
$stmt->execute([$postId, $uid, $content]);

setFlash('success', 'Reply added.');
redirect('/student/forum_post.php?post_id=' . $postId);
