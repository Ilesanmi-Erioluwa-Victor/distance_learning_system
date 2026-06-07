<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$pdo = Database::getConnection();
$search = trim($_GET['search'] ?? '');
$role   = $_GET['role'] ?? '';
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$where = "1=1"; $params = [];
if ($search !== '') { $where .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($role !== '' && in_array($role, ['admin','instructor','student'], true)) { $where .= " AND role = ?"; $params[] = $role; }

$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE $where");
$stmt->execute($params);
$total = (int) $stmt->fetchColumn();
$pages = max(1, (int) ceil($total / $perPage));
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare("SELECT * FROM users WHERE $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$users = $stmt->fetchAll();

$pageTitle = 'Users';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>👥 User Management</h1>
</div>

<form method="get" class="card mb-3" style="padding: 16px;">
    <div class="grid grid-3">
        <input type="text" name="search" class="form-input" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
        <select name="role" class="form-select">
            <option value="">All Roles</option>
            <option value="admin" <?php echo $role==='admin'?'selected':''; ?>>Admin</option>
            <option value="instructor" <?php echo $role==='instructor'?'selected':''; ?>>Instructor</option>
            <option value="student" <?php echo $role==='student'?'selected':''; ?>>Student</option>
        </select>
        <button class="btn btn-primary">Filter</button>
    </div>
</form>

<div class="table-wrapper">
    <table class="table">
        <thead>
            <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td>
                    <div class="d-flex items-center gap-1">
                        <div class="avatar avatar-sm"><?php echo getInitials($u['first_name'], $u['last_name']); ?></div>
                        <?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?>
                    </div>
                </td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td><span class="badge badge-info"><?php echo $u['role']; ?></span></td>
                <td>
                    <?php if ($u['is_active']): ?>
                        <span class="badge badge-success">Active</span>
                    <?php else: ?>
                        <span class="badge badge-danger">Disabled</span>
                    <?php endif; ?>
                </td>
                <td><?php echo formatDate($u['created_at']); ?></td>
                <td class="actions">
                    <form method="post" action="<?php echo BASE_URL; ?>/actions/admin/toggle_user.php" style="display:inline;">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                        <button class="btn btn-sm btn-<?php echo $u['is_active']?'warning':'success'; ?>" data-confirm="Toggle this user's status?">
                            <?php echo $u['is_active']?'Disable':'Enable'; ?>
                        </button>
                    </form>
                    <?php if ((int)$u['id'] !== (int)getCurrentUser()['id']): ?>
                        <form method="post" action="<?php echo BASE_URL; ?>/actions/admin/delete_user.php" style="display:inline;">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                            <button class="btn btn-sm btn-danger" data-confirm="Delete this user permanently?">Delete</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($pages > 1): ?>
    <div class="pagination">
        <?php for ($p = 1; $p <= $pages; $p++): ?>
            <a class="page-item <?php echo $p === $page ? 'active' : ''; ?>" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $p])); ?>"><?php echo $p; ?></a>
        <?php endfor; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
