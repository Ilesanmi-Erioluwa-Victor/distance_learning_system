<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('student');

$postId = (int)($_GET['post_id'] ?? 0);
$uid = (int) getCurrentUser()['id'];

$pdo = Database::getConnection();
$stmt = $pdo->prepare("
    SELECT p.*, c.title as course_title, c.id as course_id,
           u.first_name, u.last_name, u.profile_photo
    FROM forum_posts p
    JOIN courses c ON p.course_id = c.id
    JOIN users u ON p.author_id = u.id
    WHERE p.id = ?
");
$stmt->execute([$postId]);
$post = $stmt->fetch();
if (!$post) { setFlash('error', 'Post not found.'); redirect('/student/dashboard.php'); }

// Verify enrollment
$stmt = $pdo->prepare("SELECT 1 FROM enrollments WHERE student_id = ? AND course_id = ?");
$stmt->execute([$uid, $post['course_id']]);
if (!$stmt->fetch()) { setFlash('error', 'Not authorized.'); redirect('/student/dashboard.php'); }

// Replies
$stmt = $pdo->prepare("
    SELECT r.*, u.first_name, u.last_name, u.profile_photo
    FROM forum_replies r JOIN users u ON r.author_id = u.id
    WHERE r.post_id = ?
    ORDER BY r.created_at ASC
");
$stmt->execute([$postId]);
$replies = $stmt->fetchAll();

$pageTitle = $post['title'];
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <a href="<?php echo BASE_URL; ?>/student/forum.php?course_id=<?php echo (int)$post['course_id']; ?>" class="btn btn-ghost">← Back to Forum</a>
</div>

<div class="forum-post-card">
    <h2><?php if ($post['is_pinned']): ?>📌 <?php endif; ?><?php echo htmlspecialchars($post['title']); ?></h2>
    <p class="text-muted" style="font-size:.85rem;">
        By <?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?> · <?php echo timeAgo($post['created_at']); ?>
    </p>
    <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
</div>

<h3 class="mt-3 mb-2">Replies (<?php echo count($replies); ?>)</h3>
<?php foreach ($replies as $r): ?>
    <div class="forum-reply">
        <p class="text-muted" style="font-size:.85rem;">
            <strong><?php echo htmlspecialchars($r['first_name'] . ' ' . $r['last_name']); ?></strong> · <?php echo timeAgo($r['created_at']); ?>
        </p>
        <p><?php echo nl2br(htmlspecialchars($r['content'])); ?></p>
    </div>
<?php endforeach; ?>

<div class="card mt-3">
    <div class="card-body">
        <h4>Add a Reply</h4>
        <form method="post" action="<?php echo BASE_URL; ?>/actions/forum/create_reply.php">
            <?php echo csrfField(); ?>
            <input type="hidden" name="post_id" value="<?php echo $postId; ?>">
            <div class="form-group">
                <textarea name="content" class="form-textarea" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Reply</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
