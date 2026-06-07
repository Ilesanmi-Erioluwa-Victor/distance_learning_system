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
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND instructor_id = ?");
$stmt->execute([$cid, $uid]);
$course = $stmt->fetch();
if (!$course) { setFlash('error', 'Not found.'); redirect('/instructor/dashboard.php'); }

$title = sanitize($_POST['title'] ?? '');
$category = sanitize($_POST['category'] ?? '');
$level = sanitize($_POST['level'] ?? 'Beginner');
$duration = sanitize($_POST['duration'] ?? '');
$description = sanitize($_POST['description'] ?? '');

$thumbnail = $course['thumbnail'];
if (!empty($_FILES['thumbnail']['name'])) {
    $result = uploadFile($_FILES['thumbnail'], 'thumbnails', ['image/jpeg','image/png','image/webp'], 3 * 1024 * 1024);
    if ($result['success']) $thumbnail = $result['path'];
}

$stmt = $pdo->prepare("UPDATE courses SET title=?, description=?, thumbnail=?, level=?, category=?, duration=? WHERE id=?");
$stmt->execute([$title, $description, $thumbnail, $level, $category, $duration, $cid]);

setFlash('success', 'Course updated.');
redirect('/instructor/edit_course.php?course_id=' . $cid);
