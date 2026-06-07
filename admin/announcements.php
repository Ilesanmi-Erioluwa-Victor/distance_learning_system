<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$uid = (int) getCurrentUser()['id'];
$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT * FROM announcements ORDER BY created_at DESC LIMIT 50");
$stmt->execute();
$rows = $stmt->fetchAll();

$pageTitle = 'Announcements';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>📢 Platform Announcements</h1>
</div>

<div class="card mb-3">
    <div class="card-header">Post New</div>
    <div class="card-body">
        <form method="post" action="<?php echo BASE_URL; ?>/actions/admin/post_announcement.php">
            <?php echo csrfField(); ?>
            <div class="form-group">
                <label class="form-label">Title *</label>
                <input type="text" name="title" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Content *</label>
                <textarea name="content" class="form-textarea" required></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Target</label>
                <select name="target" class="form-select">
                    <option value="all">All</option>
                    <option value="students">Students</option>
                    <option value="instructors">Instructors</option>
                </select>
            </div>
            <button class="btn btn-primary">Post</button>
        </form>
    </div>
</div>

<?php foreach ($rows as $a): ?>
    <div class="card mb-2">
        <div class="card-body">
            <h4><?php echo htmlspecialchars($a['title']); ?></h4>
            <p class="text-muted" style="font-size:.85rem;">Target: <?php echo $a['target']; ?> · <?php echo timeAgo($a['created_at']); ?></p>
            <p><?php echo nl2br(htmlspecialchars($a['content'])); ?></p>
        </div>
    </div>
<?php endforeach; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
