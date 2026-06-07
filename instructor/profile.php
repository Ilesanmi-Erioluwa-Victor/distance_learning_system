<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('instructor');

$uid = (int) getCurrentUser()['id'];
$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$uid]);
$me = $stmt->fetch();

$pageTitle = 'My Profile';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>👤 My Profile</h1>
</div>

<div class="grid grid-2">
    <div class="card">
        <div class="card-header">Profile Photo</div>
        <div class="card-body text-center">
            <div class="avatar avatar-lg" style="margin: 0 auto 16px;">
                <?php if (!empty($me['profile_photo'])): ?>
                    <img src="<?php echo BASE_URL; ?>/uploads/profiles/<?php echo htmlspecialchars(basename($me['profile_photo'])); ?>" alt="">
                <?php else: ?>
                    <?php echo getInitials($me['first_name'], $me['last_name']); ?>
                <?php endif; ?>
            </div>
            <form method="post" enctype="multipart/form-data" action="<?php echo BASE_URL; ?>/actions/user/update_photo.php">
                <?php echo csrfField(); ?>
                <div class="form-group">
                    <input type="file" name="profile_photo" accept="image/*" class="form-input">
                </div>
                <button class="btn btn-primary btn-sm">Upload</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Edit Information</div>
        <div class="card-body">
            <form method="post" action="<?php echo BASE_URL; ?>/actions/user/update_profile.php">
                <?php echo csrfField(); ?>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-input" value="<?php echo htmlspecialchars($me['first_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-input" value="<?php echo htmlspecialchars($me['last_name']); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-input" value="<?php echo htmlspecialchars($me['phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Bio</label>
                    <textarea name="bio" class="form-textarea"><?php echo htmlspecialchars($me['bio'] ?? ''); ?></textarea>
                </div>
                <button class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">Change Password</div>
    <div class="card-body">
        <form method="post" action="<?php echo BASE_URL; ?>/actions/user/change_password.php">
            <?php echo csrfField(); ?>
            <div class="form-group">
                <label class="form-label">Current Password</label>
                <input type="password" name="current_password" class="form-input" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-input" required minlength="6">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm</label>
                    <input type="password" name="confirm_password" class="form-input" required minlength="6">
                </div>
            </div>
            <button class="btn btn-primary">Update Password</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
