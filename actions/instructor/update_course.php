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
$departmentId = (int)($_POST['department_id'] ?? 0);
$level = sanitize($_POST['level'] ?? '');
$semesterId = (int)($_POST['semester_id'] ?? 0);
$academicSessionId = (int)($_POST['academic_session_id'] ?? 0);
$duration = sanitize($_POST['duration'] ?? '');
$description = sanitize($_POST['description'] ?? '');

$thumbnail = $course['thumbnail'];
if (!empty($_FILES['thumbnail']['name'])) {
    $result = uploadFile($_FILES['thumbnail'], 'thumbnails', ['image/jpeg','image/png','image/webp'], 3 * 1024 * 1024);
    if ($result['success']) $thumbnail = $result['path'];
}

$stmt = $pdo->prepare("UPDATE courses SET title=?, description=?, thumbnail=?, level=?, department_id=?, semester_id=?, academic_session_id=?, duration=? WHERE id=?");
$stmt->execute([$title, $description, $thumbnail, $level, $departmentId, $semesterId, $academicSessionId, $duration, $cid]);

setFlash('success', 'Course updated.');
redirect('/instructor/edit_course.php?course_id=' . $cid);
