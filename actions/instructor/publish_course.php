<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('instructor');
validateCsrf();

$uid = (int)currentUserId();
$cid = (int)($_POST['course_id'] ?? 0);
$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT is_published FROM courses WHERE id = ? AND instructor_id = ?");
$stmt->execute([$cid, $uid]);
$row = $stmt->fetch();
if ($row) {
    $new = $row['is_published'] ? 0 : 1;
    $pdo->prepare("UPDATE courses SET is_published = ? WHERE id = ?")->execute([$new, $cid]);
}
redirect('/instructor/course_builder.php?course_id=' . $cid);
