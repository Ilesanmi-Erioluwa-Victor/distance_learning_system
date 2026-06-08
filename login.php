<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    redirect('/' . $_SESSION['user_role'] . '/dashboard.php');
}

$pageTitle = 'Login';
$hideNav = true;
$redirect = $_GET['redirect'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — <?php echo APP_NAME; ?></title>
    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>/assets/images/favicon.svg">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/auth.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/responsive.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="logo-mark">W</div>
            <h1>DSPoly</h1>
            <p>Delta State Polytechnic Distance Learning</p>
        </div>
        <h2>Welcome Back</h2>
        <p class="subtitle">Sign in to continue learning</p>

        <?php renderFlash(); ?>

        <form method="post" action="<?php echo BASE_URL; ?>/actions/auth/login.php">
            <?php echo csrfField(); ?>
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" required autofocus value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="form-group password-toggle">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" required>
                <button type="button" class="toggle-btn">Show</button>
            </div>
            <div class="d-flex justify-between items-center mb-2">
                <a href="<?php echo BASE_URL; ?>/forgot_password.php" class="text-muted" style="font-size:.85rem;">Forgot Password?</a>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg">Login</button>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="<?php echo BASE_URL; ?>/register.php">Register</a>
        </div>
    </div>
</div>
<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
