<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('instructor');

$uid = (int) getCurrentUser()['id'];
$pdo = Database::getConnection();
$myCourses = $pdo->prepare("SELECT * FROM courses WHERE instructor_id = ? ORDER BY title");
$myCourses->execute([$uid]);
$courses = $myCourses->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM announcements WHERE author_id = ? ORDER BY created_at DESC");
$stmt->execute([$uid]);
$rows = $stmt->fetchAll();

$pageTitle = 'Announcements';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>📢 Announcements</h1>
</div>

<div class="card mb-3">
    <div class="card-header">Create New</div>
    <div class="card-body">
        <form method="post" action="<?php echo BASE_URL; ?>/actions/instructor/post_announcement.php">
            <?php echo csrfField(); ?>
            <div class="form-group">
                <label class="form-label">Title *</label>
                <input type="text" name="title" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Content *</label>
                <textarea name="content" class="form-textarea" required></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Target</label>
                    <select name="target" class="form-select">
                        <option value="all">All</option>
                        <option value="students">Students</option>
                        <option value="instructors">Instructors</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Course (optional)</label>
                    <select name="course_id" class="form-select">
                        <option value="">Platform-wide</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button class="btn btn-primary">Post</button>
        </form>
    </div>
</div>

<?php if (empty($rows)): ?>
    <p class="text-muted">No announcements yet.</p>
<?php else: ?>
    <?php foreach ($rows as $a): ?>
        <div class="card mb-2">
            <div class="card-body">
                <h4><?php echo htmlspecialchars($a['title']); ?></h4>
                <p class="text-muted" style="font-size:.85rem;">Target: <?php echo $a['target']; ?> · <?php echo timeAgo($a['created_at']); ?></p>
                <p><?php echo nl2br(htmlspecialchars($a['content'])); ?></p>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
