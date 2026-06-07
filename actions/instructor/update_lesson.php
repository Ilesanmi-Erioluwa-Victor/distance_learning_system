<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('instructor');
validateCsrf();

$uid = (int)currentUserId();
$lid = (int)($_POST['lesson_id'] ?? 0);
$cid = (int)($_POST['course_id'] ?? 0);
$title = sanitize($_POST['title'] ?? '');
$content = $_POST['content'] ?? '';
$videoUrl = sanitize($_POST['video_url'] ?? '');
$duration = (int)($_POST['duration'] ?? 0);
$sort = (int)($_POST['sort_order'] ?? 0);

$pdo = Database::getConnection();
$stmt = $pdo->prepare("
    SELECT 1 FROM lessons l
    JOIN modules m ON l.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    WHERE l.id = ? AND c.instructor_id = ?
");
$stmt->execute([$lid, $uid]);
if (!$stmt->fetch()) { setFlash('error', 'Not allowed.'); redirect('/instructor/dashboard.php'); }

$stmt = $pdo->prepare("SELECT file_path FROM lessons WHERE id = ?");
$stmt->execute([$lid]);
$current = $stmt->fetch();

$filePath = $current['file_path'] ?? null;
if (!empty($_FILES['file']['name'])) {
    $result = uploadFile($_FILES['file'], 'resources',
        ['application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document',
         'application/vnd.ms-powerpoint','application/vnd.openxmlformats-officedocument.presentationml.presentation'],
        20 * 1024 * 1024);
    if ($result['success']) $filePath = $result['path'];
}

$stmt = $pdo->prepare("UPDATE lessons SET title=?, content=?, video_url=?, file_path=?, duration=?, sort_order=? WHERE id=?");
$stmt->execute([$title, $content, $videoUrl, $filePath, $duration, $sort, $lid]);

setFlash('success', 'Lesson updated.');
redirect('/instructor/course_builder.php?course_id=' . $cid);
