<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('student');

$pdo = Database::getConnection();
$uid = (int) getCurrentUser()['id'];
$tab = $_GET['tab'] ?? 'all';

$where = "e.student_id = ?";
$params = [$uid];
if (in_array($tab, ['active', 'completed', 'dropped'], true)) {
    $where .= " AND e.status = ?";
    $params[] = $tab;
}

$stmt = $pdo->prepare("
    SELECT c.*, u.first_name, u.last_name, e.progress, e.status
    FROM enrollments e
    JOIN courses c ON c.id = e.course_id
    JOIN users u ON c.instructor_id = u.id
    WHERE $where
    ORDER BY e.enrolled_at DESC
");
$stmt->execute($params);
$courses = $stmt->fetchAll();

$pageTitle = 'My Courses';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>📚 My Courses</h1>
    <a href="<?php echo BASE_URL; ?>/courses.php" class="btn btn-primary">Browse Catalog</a>
</div>

<div class="tabs">
    <a class="tab-item <?php echo $tab === 'all' ? 'active' : ''; ?>" href="?tab=all">All</a>
    <a class="tab-item <?php echo $tab === 'active' ? 'active' : ''; ?>" href="?tab=active">Active</a>
    <a class="tab-item <?php echo $tab === 'completed' ? 'active' : ''; ?>" href="?tab=completed">Completed</a>
    <a class="tab-item <?php echo $tab === 'dropped' ? 'active' : ''; ?>" href="?tab=dropped">Dropped</a>
</div>

<?php if (empty($courses)): ?>
    <div class="empty-state">
        <div class="icon">📚</div>
        <h3>No courses in this category</h3>
        <p>Browse the catalog to find something for you.</p>
        <a href="<?php echo BASE_URL; ?>/courses.php" class="btn btn-primary">Browse Courses</a>
    </div>
<?php else: ?>
    <div class="grid grid-2">
        <?php foreach ($courses as $c):
            $stmt2 = $pdo->prepare("
                SELECT l.id FROM lessons l JOIN modules m ON l.module_id = m.id
                WHERE m.course_id = ?
                  AND l.id NOT IN (SELECT lesson_id FROM lesson_progress WHERE student_id = ? AND completed)
                ORDER BY m.sort_order, l.sort_order LIMIT 1
            ");
            $stmt2->execute([$c['id'], $uid]);
            $nextLesson = $stmt2->fetchColumn();
        ?>
            <div class="course-card">
                <div class="info">
                    <div class="d-flex justify-between items-center">
                        <span class="badge badge-info"><?php echo htmlspecialchars($c['level']); ?></span>
                        <span class="badge badge-<?php echo $c['status']==='completed' ? 'success' : ($c['status']==='dropped' ? 'danger' : 'primary'); ?>">
                            <?php echo ucfirst($c['status']); ?>
                        </span>
                    </div>
                    <h3 class="mt-1"><?php echo htmlspecialchars($c['title']); ?></h3>
                    <p><?php echo htmlspecialchars($c['first_name'] . ' ' . $c['last_name']); ?></p>
                    <div class="progress"><div class="progress-bar" style="width: <?php echo $c['progress']; ?>%"></div></div>
                    <p class="text-muted" style="font-size:.85rem;"><?php echo $c['progress']; ?>% complete</p>
                    <a href="<?php echo BASE_URL; ?>/student/learn.php?course_id=<?php echo (int)$c['id']; ?>&lesson_id=<?php echo (int)$nextLesson; ?>" class="btn btn-primary btn-sm mt-2">Continue</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
