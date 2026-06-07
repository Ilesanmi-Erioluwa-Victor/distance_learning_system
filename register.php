<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    redirect('/' . $_SESSION['user_role'] . '/dashboard.php');
}

$pageTitle = 'Register';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/auth.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/responsive.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="logo-mark">W</div>
            <h1>Create Account</h1>
            <p>Join the WBDLS learning community</p>
        </div>

        <?php renderFlash(); ?>

        <form method="post" action="<?php echo BASE_URL; ?>/actions/auth/register.php">
            <?php echo csrfField(); ?>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-input" required value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-input" required value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="form-group password-toggle">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" required minlength="6">
                <button type="button" class="toggle-btn">Show</button>
            </div>
            <div class="form-group password-toggle">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-input" required minlength="6">
                <button type="button" class="toggle-btn">Show</button>
            </div>
            <div class="form-group">
                <label class="form-label">I am a:</label>
                <div class="role-selector">
                    <label>
                        <input type="radio" name="role" value="student" checked>
                        <span>🎓 Student</span>
                    </label>
                    <label>
                        <input type="radio" name="role" value="instructor">
                        <span>👨‍🏫 Instructor</span>
                    </label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg">Create Account</button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="<?php echo BASE_URL; ?>/login.php">Login</a>
        </div>
    </div>
</div>
<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
