<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
validateCsrf();

$uid = (int)currentUserId();

if (empty($_FILES['profile_photo']['name'])) {
    setFlash('error', 'Please choose a file.');
    redirect('/' . currentUserRole() . '/profile.php');
}

$result = uploadFile($_FILES['profile_photo'], 'profiles', ['image/jpeg','image/png','image/webp'], 2 * 1024 * 1024);
if (!$result['success']) {
    setFlash('error', $result['error']);
    redirect('/' . currentUserRole() . '/profile.php');
}

$pdo = Database::getConnection();
$stmt = $pdo->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
$stmt->execute([$result['path'], $uid]);
$_SESSION['user_photo'] = $result['path'];

setFlash('success', 'Photo updated.');
redirect('/' . currentUserRole() . '/profile.php');
