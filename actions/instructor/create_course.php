<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('instructor');
validateCsrf();

$uid = (int)currentUserId();
$title = sanitize($_POST['title'] ?? '');
$category = sanitize($_POST['category'] ?? '');
$level = sanitize($_POST['level'] ?? 'Beginner');
$duration = sanitize($_POST['duration'] ?? '');
$description = sanitize($_POST['description'] ?? '');

if (!$title || !$category || !$description) {
    setFlash('error', 'Title, category and description are required.');
    redirect('/instructor/create_course.php');
}

$thumbnail = null;
if (!empty($_FILES['thumbnail']['name'])) {
    $result = uploadFile($_FILES['thumbnail'], 'thumbnails', ['image/jpeg','image/png','image/webp'], 3 * 1024 * 1024);
    if ($result['success']) $thumbnail = $result['path'];
    else { setFlash('error', $result['error']); redirect('/instructor/create_course.php'); }
}

$pdo = Database::getConnection();
$stmt = $pdo->prepare("
    INSERT INTO courses (instructor_id, title, description, thumbnail, level, category, duration, is_published)
    VALUES (?, ?, ?, ?, ?, ?, ?, 0)
");
$stmt->execute([$uid, $title, $description, $thumbnail, $level, $category, $duration]);
$cid = (int) $pdo->lastInsertId();

setFlash('success', 'Course created! Add modules and lessons next.');
redirect('/instructor/course_builder.php?course_id=' . $cid);
