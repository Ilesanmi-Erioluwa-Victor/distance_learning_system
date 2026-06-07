<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('student');

$uid = (int) getCurrentUser()['id'];
$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT * FROM announcements WHERE target IN ('all','students') ORDER BY created_at DESC");
$stmt->execute();
$rows = $stmt->fetchAll();

$pageTitle = 'Announcements';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>📢 Announcements</h1>
</div>

<?php if (empty($rows)): ?>
    <div class="empty-state">
        <div class="icon">📢</div>
        <h3>No announcements yet</h3>
    </div>
<?php else: ?>
    <?php foreach ($rows as $a): ?>
        <div class="card mb-2">
            <div class="card-body">
                <h3><?php echo htmlspecialchars($a['title']); ?></h3>
                <p class="text-muted" style="font-size:.85rem;"><?php echo timeAgo($a['created_at']); ?></p>
                <p><?php echo nl2br(htmlspecialchars($a['content'])); ?></p>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
