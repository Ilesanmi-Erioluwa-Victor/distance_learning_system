<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$pdo = Database::getConnection();
$semesters = $pdo->query("SELECT * FROM semesters ORDER BY sort_order, name")->fetchAll();

$pageTitle = 'Semesters';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>🗓️ Semesters</h1>
</div>

<div class="card mb-3" style="padding: 16px;">
    <form method="post" action="<?php echo BASE_URL; ?>/actions/admin/create_semester.php">
        <?php echo csrfField(); ?>
        <div class="grid grid-3">
            <input type="text" name="name" class="form-input" placeholder="e.g. First Semester..." required>
            <input type="number" name="sort_order" class="form-input" placeholder="Sort order (e.g. 1)" value="0">
            <button class="btn btn-primary">Add Semester</button>
        </div>
    </form>
</div>

<div class="table-wrapper">
    <table class="table">
        <thead>
            <tr><th>Name</th><th>Sort Order</th><th>Created</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($semesters as $s): ?>
            <tr>
                <td><?php echo htmlspecialchars($s['name']); ?></td>
                <td><?php echo (int)$s['sort_order']; ?></td>
                <td><?php echo formatDate($s['created_at']); ?></td>
                <td class="actions">
                    <form method="post" action="<?php echo BASE_URL; ?>/actions/admin/delete_semester.php" style="display:inline;">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>">
                        <button class="btn btn-sm btn-danger" data-confirm="Delete this semester?">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
