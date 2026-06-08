<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Forgot Password';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — <?php echo APP_NAME; ?></title>
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
            <h1>Reset Password</h1>
            <p>Enter your email to receive a reset code</p>
        </div>

        <?php renderFlash(); ?>

        <form method="post" action="<?php echo BASE_URL; ?>/actions/auth/forgot_password.php">
            <?php echo csrfField(); ?>
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-input" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg">Send Reset Code</button>
        </form>

        <div class="auth-footer">
            Remembered it? <a href="<?php echo BASE_URL; ?>/login.php">Login</a>
        </div>
    </div>
</div>
<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
