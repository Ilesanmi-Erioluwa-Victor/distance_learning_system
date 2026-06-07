<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
validateCsrf();

$uid = (int)currentUserId();
$first = sanitize($_POST['first_name'] ?? '');
$last  = sanitize($_POST['last_name'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$bio   = sanitize($_POST['bio'] ?? '');

if (!$first || !$last) { setFlash('error', 'Name is required.'); redirect('/' . currentUserRole() . '/profile.php'); }

$pdo = Database::getConnection();
$stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ?, bio = ? WHERE id = ?");
$stmt->execute([$first, $last, $phone, $bio, $uid]);

$_SESSION['user_first_name'] = $first;
$_SESSION['user_last_name']  = $last;

setFlash('success', 'Profile updated.');
redirect('/' . currentUserRole() . '/profile.php');
