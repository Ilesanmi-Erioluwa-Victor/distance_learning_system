<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');
validateCsrf();

$name = trim($_POST['name'] ?? '');
if ($name === '') {
    setFlash('error', 'Level name is required.');
    redirect('/admin/levels.php');
}

$pdo = Database::getConnection();
$stmt = $pdo->prepare("INSERT INTO levels (name) VALUES (?)");
$stmt->execute([$name]);

setFlash('success', 'Level created.');
redirect('/admin/levels.php');
