<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$email = trim($_GET['email'] ?? $_POST['email'] ?? '');

$storedOtp = '';
if ($email !== '') {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT otp_code, is_verified FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    if ($u && !$u['is_verified'] && $u['otp_code']) {
        $storedOtp = $u['otp_code'];
    }
}

$pageTitle = 'Verify Email';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email — <?php echo APP_NAME; ?></title>
    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>/assets/images/favicon.svg">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/auth.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/responsive.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="logo-mark">D</div>
            <h1>Verify Your Email</h1>
            <p>Enter the 6-digit code sent to <strong><?php echo htmlspecialchars($email); ?></strong></p>
        </div>

        <?php if ($storedOtp): ?>
            <div class="alert alert-info">
                Your verification code is: <strong style="font-size:1.4rem;letter-spacing:4px;"><?php echo htmlspecialchars($storedOtp); ?></strong>
            </div>
        <?php endif; ?>

        <?php renderFlash(); ?>

        <?php if ($email): ?>
            <form method="post" action="<?php echo BASE_URL; ?>/actions/auth/verify_otp.php">
                <?php echo csrfField(); ?>
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                <input type="hidden" name="otp" id="otpFull">
                <div class="otp-input-group">
                    <input type="text" maxlength="1" required>
                    <input type="text" maxlength="1" required>
                    <input type="text" maxlength="1" required>
                    <input type="text" maxlength="1" required>
                    <input type="text" maxlength="1" required>
                    <input type="text" maxlength="1" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Verify</button>
            </form>

            <form method="post" action="<?php echo BASE_URL; ?>/actions/auth/resend_otp.php" class="mt-2 text-center">
                <?php echo csrfField(); ?>
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                <button type="submit" class="btn btn-ghost btn-sm">Resend Code</button>
            </form>
        <?php else: ?>
            <p class="text-center text-muted">No email provided. <a href="<?php echo BASE_URL; ?>/register.php">Register</a></p>
        <?php endif; ?>

        <div class="auth-footer">
            <a href="<?php echo BASE_URL; ?>/login.php">Back to Login</a>
        </div>
    </div>
</div>
<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
