<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('instructor');
validateCsrf();

$uid = (int)currentUserId();
$title = sanitize($_POST['title'] ?? '');
$departmentId = (int)($_POST['department_id'] ?? 0);
$level = sanitize($_POST['level'] ?? '');
$semesterId = (int)($_POST['semester_id'] ?? 0);
$academicSessionId = (int)($_POST['academic_session_id'] ?? 0);
$duration = sanitize($_POST['duration'] ?? '');
$description = sanitize($_POST['description'] ?? '');

if (!$title || !$departmentId || !$level || !$semesterId || !$academicSessionId || !$description) {
    setFlash('error', 'All required fields must be filled.');
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
    INSERT INTO courses (instructor_id, title, description, thumbnail, level, department_id, semester_id, academic_session_id, duration, is_published)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, FALSE)
");
$stmt->execute([$uid, $title, $description, $thumbnail, $level, $departmentId, $semesterId, $academicSessionId, $duration]);
$cid = (int) $pdo->lastInsertId();

setFlash('success', 'Course created! Add modules and lessons next.');
redirect('/instructor/course_builder.php?course_id=' . $cid);
