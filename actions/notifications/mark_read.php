<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
header('Content-Type: application/json');

$uid = (int)currentUserId();
$pdo = Database::getConnection();
$stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ?");
$stmt->execute([$uid]);
echo json_encode(['ok' => true]);
