<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/mail.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('/register.php'); }
validateCsrf();

$first = sanitize($_POST['first_name'] ?? '');
$last  = sanitize($_POST['last_name'] ?? '');
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';
$role = $_POST['role'] ?? 'student';

if ($role === 'student') {
    $facultyId    = $_POST['faculty_id'] ?? null;
    $departmentId = $_POST['department_id'] ?? null;
    $studentLevel = $_POST['student_level'] ?? null;
} else {
    $facultyId    = null;
    $departmentId = null;
    $studentLevel = null;
}

if (!$first || !$last || !$email || !$password || !$confirm) {
    setFlash('error', 'All fields are required.');
    redirect('/register.php');
}
if (strlen($password) < 6) {
    setFlash('error', 'Password must be at least 6 characters.');
    redirect('/register.php');
}
if ($password !== $confirm) {
    setFlash('error', 'Passwords do not match.');
    redirect('/register.php');
}
if (!in_array($role, ['student', 'instructor'], true)) {
    setFlash('error', 'Invalid role.');
    redirect('/register.php');
}

$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    setFlash('error', 'Email already registered.');
    redirect('/register.php');
}

$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
$otp  = generateOTP(6);
$otpExpires = (new DateTime('+10 minutes'))->format('Y-m-d H:i:s');

$stmt = $pdo->prepare("
    INSERT INTO users (first_name, last_name, email, password, role, is_active, is_verified, otp_code, otp_expires_at, faculty_id, department_id, student_level)
    VALUES (?, ?, ?, ?, ?, TRUE, FALSE, ?, ?, ?, ?, ?)
");
$stmt->execute([$first, $last, $email, $hash, $role, $otp, $otpExpires, $facultyId, $departmentId, $studentLevel]);

// Send email if configured, otherwise show OTP directly
if (defined('MAIL_USER') && MAIL_USER !== '') {
    $body = getOtpEmailHtml($first, $otp);
    $err = sendEmail($email, $first, 'Verify your DSPoly e-Learning account', $body);
    if ($err === '') {
        setFlash('success', 'Account created! Check your email for the verification code.');
        redirect('/verify_email.php?email=' . urlencode($email));
    }
    setFlash('warning', 'Mail error: ' . $err . ' — Your code is: ' . $otp);
    redirect('/verify_email.php?email=' . urlencode($email));
}
setFlash('info', 'Your verification code is: ' . $otp);
redirect('/verify_email.php?email=' . urlencode($email));
