<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('instructor');

$assignmentId = (int)($_GET['assignment_id'] ?? 0);
$uid = (int) getCurrentUser()['id'];
$pdo = Database::getConnection();

$stmt = $pdo->prepare("
    SELECT a.*, m.title as module_title, c.title as course_title, c.instructor_id
    FROM assignments a
    JOIN modules m ON a.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    WHERE a.id = ?
");
$stmt->execute([$assignmentId]);
$assignment = $stmt->fetch();
if (!$assignment || (int)$assignment['instructor_id'] !== $uid) {
    setFlash('error', 'Not found.'); redirect('/instructor/dashboard.php');
}

$stmt = $pdo->prepare("
    SELECT s.*, u.first_name, u.last_name, u.email
    FROM submissions s JOIN users u ON s.student_id = u.id
    WHERE s.assignment_id = ?
    ORDER BY s.submitted_at DESC
");
$stmt->execute([$assignmentId]);
$submissions = $stmt->fetchAll();

$pageTitle = 'Grade Submissions';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>📝 <?php echo htmlspecialchars($assignment['title']); ?></h1>
    <a href="<?php echo BASE_URL; ?>/instructor/assignments.php" class="btn btn-ghost">← Back</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <p><strong>Course:</strong> <?php echo htmlspecialchars($assignment['course_title']); ?></p>
        <p><strong>Module:</strong> <?php echo htmlspecialchars($assignment['module_title']); ?></p>
        <p><strong>Due:</strong> <?php echo formatDate($assignment['due_date']); ?></p>
        <p><strong>Max Score:</strong> <?php echo (int)$assignment['max_score']; ?></p>
        <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($assignment['description'])); ?></p>
    </div>
</div>

<?php if (empty($submissions)): ?>
    <div class="empty-state">
        <div class="icon">📭</div>
        <h3>No submissions yet</h3>
    </div>
<?php else: ?>
    <?php foreach ($submissions as $s): ?>
        <div class="card mb-2">
            <div class="card-body">
                <div class="d-flex justify-between items-center mb-2">
                    <h4><?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?> <small class="text-muted"><?php echo htmlspecialchars($s['email']); ?></small></h4>
                    <span class="badge badge-<?php echo $s['status']==='graded'?'success':'warning'; ?>"><?php echo ucfirst($s['status']); ?></span>
                </div>
                <p class="text-muted" style="font-size:.85rem;">Submitted: <?php echo timeAgo($s['submitted_at']); ?></p>

                <?php if (!empty($s['text_content'])): ?>
                    <blockquote style="background:#f8fafc; padding: 10px 14px; border-left: 3px solid var(--color-primary); margin: 10px 0;">
                        <?php echo nl2br(htmlspecialchars($s['text_content'])); ?>
                    </blockquote>
                <?php endif; ?>

                <?php if (!empty($s['file_path'])): ?>
                    <p><a href="<?php echo BASE_URL; ?>/uploads/assignments/<?php echo htmlspecialchars(basename($s['file_path'])); ?>" target="_blank" class="btn btn-outline btn-sm">📎 Download Submission</a></p>
                <?php endif; ?>

                <form method="post" action="<?php echo BASE_URL; ?>/actions/instructor/grade_submission.php">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="submission_id" value="<?php echo (int)$s['id']; ?>">
                    <input type="hidden" name="assignment_id" value="<?php echo $assignmentId; ?>">
                    <div class="grid grid-2">
                        <div class="form-group">
                            <label class="form-label">Score (0 — <?php echo (int)$assignment['max_score']; ?>)</label>
                            <input type="number" name="score" min="0" max="<?php echo (int)$assignment['max_score']; ?>" class="form-input" value="<?php echo htmlspecialchars($s['score'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Feedback</label>
                            <input type="text" name="feedback" class="form-input" value="<?php echo htmlspecialchars($s['feedback'] ?? ''); ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Grade</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
