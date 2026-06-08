<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
$email = trim($_GET['email'] ?? $_POST['email'] ?? '');
$pageTitle = 'Reset Password';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — <?php echo APP_NAME; ?></title>
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
            <h1>Set New Password</h1>
            <p>For: <strong><?php echo htmlspecialchars($email); ?></strong></p>
        </div>

        <?php renderFlash(); ?>

        <form method="post" action="<?php echo BASE_URL; ?>/actions/auth/reset_password.php">
            <?php echo csrfField(); ?>
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
            <div class="form-group">
                <label class="form-label">6-digit Code</label>
                <input type="text" name="otp" class="form-input" required maxlength="6" pattern="[0-9]{6}" inputmode="numeric">
            </div>
            <div class="form-group password-toggle">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-input" required minlength="6">
                <button type="button" class="toggle-btn">Show</button>
            </div>
            <div class="form-group password-toggle">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-input" required minlength="6">
                <button type="button" class="toggle-btn">Show</button>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg">Reset Password</button>
        </form>

        <div class="auth-footer">
            <a href="<?php echo BASE_URL; ?>/login.php">Back to Login</a>
        </div>
    </div>
</div>
<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
