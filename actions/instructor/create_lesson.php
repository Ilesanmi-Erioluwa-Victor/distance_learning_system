<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('instructor');
validateCsrf();

$uid = (int)currentUserId();
$mid = (int)($_POST['module_id'] ?? 0);
$cid = (int)($_POST['course_id'] ?? 0);
$title = sanitize($_POST['title'] ?? '');
$content = $_POST['content'] ?? '';
$videoUrl = sanitize($_POST['video_url'] ?? '');
$duration = (int)($_POST['duration'] ?? 0);
$sort = (int)($_POST['sort_order'] ?? 0);

if (!$title) { setFlash('error', 'Title required.'); redirect('/instructor/course_builder.php?course_id=' . $cid . '&add_lesson_to=' . $mid); }

$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT 1 FROM modules m JOIN courses c ON m.course_id = c.id WHERE m.id = ? AND c.instructor_id = ?");
$stmt->execute([$mid, $uid]);
if (!$stmt->fetch()) { setFlash('error', 'Not allowed.'); redirect('/instructor/dashboard.php'); }

$filePath = null;
if (!empty($_FILES['file']['name'])) {
    $result = uploadFile($_FILES['file'], 'resources',
        ['application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document',
         'application/vnd.ms-powerpoint','application/vnd.openxmlformats-officedocument.presentationml.presentation'],
        20 * 1024 * 1024);
    if ($result['success']) $filePath = $result['path'];
    else { setFlash('error', $result['error']); redirect('/instructor/course_builder.php?course_id=' . $cid . '&add_lesson_to=' . $mid); }
}

$stmt = $pdo->prepare("INSERT INTO lessons (module_id, title, content, video_url, file_path, duration, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$mid, $title, $content, $videoUrl, $filePath, $duration, $sort]);

setFlash('success', 'Lesson added.');
redirect('/instructor/course_builder.php?course_id=' . $cid);
