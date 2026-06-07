<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('student');

$uid = (int) getCurrentUser()['id'];
$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$uid]);
$rows = $stmt->fetchAll();

// Mark all read
$pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$uid]);

$pageTitle = 'Notifications';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>🔔 Notifications</h1>
</div>

<?php if (empty($rows)): ?>
    <div class="empty-state">
        <div class="icon">🔔</div>
        <h3>No notifications</h3>
        <p>You're all caught up!</p>
    </div>
<?php else: ?>
    <?php foreach ($rows as $n):
        $icon = match($n['type']) {
            'enrollment' => '📚',
            'grade' => '🏆',
            'announcement' => '📢',
            'assignment' => '📝',
            default => '🔔'
        };
    ?>
        <div class="card mb-2">
            <div class="card-body d-flex items-center gap-2">
                <div style="font-size:1.5rem;"><?php echo $icon; ?></div>
                <div style="flex:1;">
                    <p style="margin:0;"><?php echo htmlspecialchars($n['message']); ?></p>
                    <small class="text-muted"><?php echo timeAgo($n['created_at']); ?></small>
                </div>
                <?php if (!empty($n['link'])): ?>
                    <a href="<?php echo htmlspecialchars($n['link']); ?>" class="btn btn-sm btn-ghost">View</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
