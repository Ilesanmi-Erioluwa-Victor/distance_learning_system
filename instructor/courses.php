<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('instructor');

$pdo = Database::getConnection();
$uid = (int) getCurrentUser()['id'];
$stmt = $pdo->prepare("
    SELECT c.*, (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) as enroll_count
    FROM courses c WHERE c.instructor_id = ? ORDER BY c.created_at DESC
");
$stmt->execute([$uid]);
$courses = $stmt->fetchAll();

$pageTitle = 'My Courses';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>📚 My Courses</h1>
    <a href="<?php echo BASE_URL; ?>/instructor/create_course.php" class="btn btn-primary">+ New Course</a>
</div>

<?php if (empty($courses)): ?>
    <div class="empty-state">
        <div class="icon">📚</div>
        <h3>No courses yet</h3>
        <a href="<?php echo BASE_URL; ?>/instructor/create_course.php" class="btn btn-primary">Create Course</a>
    </div>
<?php else: ?>
    <div class="grid grid-2">
        <?php foreach ($courses as $c): ?>
            <div class="course-card">
                <div class="info">
                    <div class="d-flex justify-between items-center">
                        <span class="badge badge-info"><?php echo htmlspecialchars($c['level']); ?></span>
                        <?php if ($c['is_published']): ?>
                            <span class="badge badge-success">Published</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Draft</span>
                        <?php endif; ?>
                    </div>
                    <h3 class="mt-1"><?php echo htmlspecialchars($c['title']); ?></h3>
                    <p><?php echo htmlspecialchars($c['category']); ?> · <?php echo (int)$c['enroll_count']; ?> enrolled</p>
                    <div class="d-flex gap-1 mt-2">
                        <a href="<?php echo BASE_URL; ?>/instructor/course_builder.php?course_id=<?php echo (int)$c['id']; ?>" class="btn btn-primary btn-sm">Build</a>
                        <a href="<?php echo BASE_URL; ?>/instructor/edit_course.php?course_id=<?php echo (int)$c['id']; ?>" class="btn btn-outline btn-sm">Edit</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
