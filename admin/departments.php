<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$pdo = Database::getConnection();
$departments = $pdo->query("
    SELECT d.*, f.name AS faculty_name
    FROM departments d
    JOIN faculties f ON d.faculty_id = f.id
    ORDER BY f.name, d.name
")->fetchAll();
$faculties = $pdo->query("SELECT * FROM faculties ORDER BY name")->fetchAll();

$pageTitle = 'Departments';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>📂 Departments</h1>
</div>

<div class="card mb-3" style="padding: 16px;">
    <form method="post" action="<?php echo BASE_URL; ?>/actions/admin/create_department.php">
        <?php echo csrfField(); ?>
        <div class="grid grid-3">
            <select name="faculty_id" class="form-select" required>
                <option value="">Select Faculty</option>
                <?php foreach ($faculties as $f): ?>
                    <option value="<?php echo (int)$f['id']; ?>"><?php echo htmlspecialchars($f['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="name" class="form-input" placeholder="Department name..." required>
            <button class="btn btn-primary">Add Department</button>
        </div>
    </form>
</div>

<div class="table-wrapper">
    <table class="table">
        <thead>
            <tr><th>Faculty</th><th>Department</th><th>Created At</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($departments as $d): ?>
            <tr>
                <td><?php echo htmlspecialchars($d['faculty_name']); ?></td>
                <td><?php echo htmlspecialchars($d['name']); ?></td>
                <td><?php echo formatDate($d['created_at']); ?></td>
                <td class="actions">
                    <form method="post" action="<?php echo BASE_URL; ?>/actions/admin/delete_department.php" style="display:inline;">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="id" value="<?php echo (int)$d['id']; ?>">
                        <button class="btn btn-sm btn-danger" data-confirm="Delete this department?">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
