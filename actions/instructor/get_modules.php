<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('instructor');
header('Content-Type: application/json');

$uid = (int)currentUserId();
$cid = (int)($_GET['course_id'] ?? 0);
$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT id, title FROM modules WHERE course_id = ? AND course_id IN (SELECT id FROM courses WHERE instructor_id = ?) ORDER BY sort_order");
$stmt->execute([$cid, $uid]);
echo json_encode($stmt->fetchAll());
