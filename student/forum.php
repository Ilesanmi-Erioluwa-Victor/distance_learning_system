<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('student');

$courseId = (int)($_GET['course_id'] ?? 0);
$uid = (int) getCurrentUser()['id'];

$pdo = Database::getConnection();

// Verify enrollment
$stmt = $pdo->prepare("SELECT c.* FROM enrollments e JOIN courses c ON c.id = e.course_id WHERE e.student_id = ? AND e.course_id = ?");
$stmt->execute([$uid, $courseId]);
$course = $stmt->fetch();
if (!$course) { setFlash('error', 'Course not found.'); redirect('/student/courses.php'); }

// Posts
$stmt = $pdo->prepare("
    SELECT p.*, u.first_name, u.last_name, u.profile_photo,
           (SELECT COUNT(*) FROM forum_replies r WHERE r.post_id = p.id) as reply_count
    FROM forum_posts p
    JOIN users u ON p.author_id = u.id
    WHERE p.course_id = ?
    ORDER BY p.is_pinned DESC, p.created_at DESC
");
$stmt->execute([$courseId]);
$posts = $stmt->fetchAll();

$pageTitle = 'Discussion Forum';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>💬 <?php echo htmlspecialchars($course['title']); ?> — Forum</h1>
    <a href="<?php echo BASE_URL; ?>/student/courses.php" class="btn btn-ghost">← Back</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h3>Start a New Discussion</h3>
        <form method="post" action="<?php echo BASE_URL; ?>/actions/forum/create_post.php">
            <?php echo csrfField(); ?>
            <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">
            <div class="form-group">
                <input type="text" name="title" class="form-input" placeholder="Title" required>
            </div>
            <div class="form-group">
                <textarea name="content" class="form-textarea" placeholder="What's on your mind?" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Post</button>
        </form>
    </div>
</div>

<?php if (empty($posts)): ?>
    <div class="empty-state">
        <div class="icon">💬</div>
        <h3>No discussions yet</h3>
        <p>Be the first to start a conversation.</p>
    </div>
<?php else: ?>
    <?php foreach ($posts as $p): ?>
        <div class="forum-post-card <?php echo $p['is_pinned'] ? 'pinned' : ''; ?>">
            <div class="d-flex justify-between items-center mb-1">
                <h4>
                    <?php if ($p['is_pinned']): ?>📌 <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>/student/forum_post.php?post_id=<?php echo (int)$p['id']; ?>">
                        <?php echo htmlspecialchars($p['title']); ?>
                    </a>
                </h4>
                <span class="text-muted" style="font-size:.85rem;"><?php echo (int)$p['reply_count']; ?> replies</span>
            </div>
            <p class="text-muted" style="font-size:.85rem;">
                By <?php echo htmlspecialchars($p['first_name'] . ' ' . $p['last_name']); ?> · <?php echo timeAgo($p['created_at']); ?>
            </p>
            <p><?php echo htmlspecialchars(substr($p['content'], 0, 200)); ?><?php echo strlen($p['content']) > 200 ? '...' : ''; ?></p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
