<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('student');
validateCsrf();

$uid = (int)currentUserId();
$assignmentId = (int)($_POST['assignment_id'] ?? 0);
$text = trim($_POST['text_content'] ?? '');

if (!$assignmentId) { setFlash('error', 'Invalid assignment.'); redirect('/student/assignments.php'); }

$pdo = Database::getConnection();
$stmt = $pdo->prepare("
    SELECT a.*, c.title as course_title FROM assignments a
    JOIN modules m ON a.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    WHERE a.id = ?
");
$stmt->execute([$assignmentId]);
$assignment = $stmt->fetch();
if (!$assignment) { setFlash('error', 'Assignment not found.'); redirect('/student/assignments.php'); }

$stmt = $pdo->prepare("SELECT 1 FROM enrollments WHERE student_id = ? AND course_id = (SELECT m.course_id FROM modules m JOIN assignments a ON a.module_id = m.id WHERE a.id = ?)");
$stmt->execute([$uid, $assignmentId]);
if (!$stmt->fetch()) { setFlash('error', 'Not enrolled.'); redirect('/student/assignments.php'); }

$filePath = null;
if (!empty($_FILES['submission_file']['name'])) {
    $result = uploadFile($_FILES['submission_file'], 'assignments',
        ['application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/zip','application/x-zip-compressed'],
        10 * 1024 * 1024);
    if ($result['success']) $filePath = $result['path'];
    else { setFlash('error', $result['error']); redirect('/student/assignments.php'); }
}

if (!$text && !$filePath) {
    setFlash('error', 'Please provide a text submission or upload a file.');
    redirect('/student/assignments.php');
}

$isLate = strtotime($assignment['due_date']) < time();
$status = $isLate ? 'late' : 'submitted';

$stmt = $pdo->prepare("
    INSERT INTO submissions (assignment_id, student_id, file_path, text_content, status, submitted_at)
    VALUES (?, ?, ?, ?, ?, NOW())
    ON CONFLICT (assignment_id, student_id) DO UPDATE SET
        file_path = EXCLUDED.file_path,
        text_content = EXCLUDED.text_content,
        status = EXCLUDED.status,
        submitted_at = NOW()
");
$stmt->execute([$assignmentId, $uid, $filePath, $text, $status]);

setFlash('success', 'Assignment submitted successfully!');
redirect('/student/assignments.php');
