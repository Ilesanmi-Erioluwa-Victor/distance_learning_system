<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$pdo = Database::getConnection();
$faculties = $pdo->query("SELECT * FROM faculties ORDER BY name")->fetchAll();

$pageTitle = 'Faculties';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>🏛️ Faculties</h1>
</div>

<div class="card mb-3" style="padding: 16px;">
    <form method="post" action="<?php echo BASE_URL; ?>/actions/admin/create_faculty.php">
        <?php echo csrfField(); ?>
        <div class="grid grid-3">
            <input type="text" name="name" class="form-input" placeholder="Faculty name..." required>
            <button class="btn btn-primary">Add Faculty</button>
        </div>
    </form>
</div>

<div class="table-wrapper">
    <table class="table">
        <thead>
            <tr><th>Name</th><th>Created At</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($faculties as $f): ?>
            <tr>
                <td><?php echo htmlspecialchars($f['name']); ?></td>
                <td><?php echo formatDate($f['created_at']); ?></td>
                <td class="actions">
                    <form method="post" action="<?php echo BASE_URL; ?>/actions/admin/delete_faculty.php" style="display:inline;">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="id" value="<?php echo (int)$f['id']; ?>">
                        <button class="btn btn-sm btn-danger" data-confirm="Delete this faculty and all its departments?">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
