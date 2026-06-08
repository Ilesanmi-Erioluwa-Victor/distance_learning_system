<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$pdo = Database::getConnection();
$sessions = $pdo->query("SELECT * FROM academic_sessions ORDER BY created_at DESC")->fetchAll();

$pageTitle = 'Academic Sessions';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>📅 Academic Sessions</h1>
</div>

<div class="card mb-3" style="padding: 16px;">
    <form method="post" action="<?php echo BASE_URL; ?>/actions/admin/create_academic_session.php">
        <?php echo csrfField(); ?>
        <div class="grid grid-3">
            <input type="text" name="name" class="form-input" placeholder="e.g. 2024/2025..." required>
            <button class="btn btn-primary">Add Session</button>
        </div>
    </form>
</div>

<div class="table-wrapper">
    <table class="table">
        <thead>
            <tr><th>Name</th><th>Current</th><th>Created</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($sessions as $s): ?>
            <tr>
                <td><?php echo htmlspecialchars($s['name']); ?></td>
                <td>
                    <?php if ($s['is_current']): ?>
                        <span class="badge badge-success">✓ Current</span>
                    <?php else: ?>
                        <form method="post" action="<?php echo BASE_URL; ?>/actions/admin/set_current_session.php" style="display:inline;">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>">
                            <button class="btn btn-sm btn-ghost">Set as Current</button>
                        </form>
                    <?php endif; ?>
                </td>
                <td><?php echo formatDate($s['created_at']); ?></td>
                <td class="actions">
                    <form method="post" action="<?php echo BASE_URL; ?>/actions/admin/delete_academic_session.php" style="display:inline;">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>">
                        <button class="btn btn-sm btn-danger" data-confirm="Delete this session?">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
