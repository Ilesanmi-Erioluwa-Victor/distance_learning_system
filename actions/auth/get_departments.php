<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

$facultyId = $_GET['faculty_id'] ?? null;

if (!$facultyId || !is_numeric($facultyId)) {
    echo json_encode([]);
    exit;
}

$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT id, name FROM departments WHERE faculty_id = ? ORDER BY name");
$stmt->execute([(int)$facultyId]);

echo json_encode($stmt->fetchAll());
