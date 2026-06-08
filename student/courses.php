<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('student');

$pdo = Database::getConnection();
$uid = (int) getCurrentUser()['id'];
$tab = $_GET['tab'] ?? 'all';
$filterSessionId = (int)($_GET['session_id'] ?? 0);
$filterSemesterId = (int)($_GET['semester_id'] ?? 0);

$where = "e.student_id = ?";
$params = [$uid];
if (in_array($tab, ['active', 'completed', 'dropped'], true)) {
    $where .= " AND e.status = ?";
    $params[] = $tab;
}
if ($filterSessionId) {
    $where .= " AND e.academic_session_id = ?";
    $params[] = $filterSessionId;
}
if ($filterSemesterId) {
    $where .= " AND c.semester_id = ?";
    $params[] = $filterSemesterId;
}

$stmt = $pdo->prepare("
    SELECT c.*, u.first_name, u.last_name, e.progress, e.status,
           sem.name AS semester_name, s.name AS session_name
    FROM enrollments e
    JOIN courses c ON c.id = e.course_id
    JOIN users u ON c.instructor_id = u.id
    LEFT JOIN semesters sem ON c.semester_id = sem.id
    LEFT JOIN academic_sessions s ON e.academic_session_id = s.id
    WHERE $where
    ORDER BY e.enrolled_at DESC
");
$stmt->execute($params);
$courses = $stmt->fetchAll();

$sessions = $pdo->query("SELECT * FROM academic_sessions ORDER BY created_at DESC")->fetchAll();
$semesters = $pdo->query("SELECT * FROM semesters ORDER BY sort_order, name")->fetchAll();
$currentSession = $pdo->query("SELECT id FROM academic_sessions WHERE is_current = TRUE")->fetchColumn();

$pageTitle = 'My Courses';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>📚 My Courses</h1>
    <a href="<?php echo BASE_URL; ?>/courses.php" class="btn btn-primary">Browse Catalog</a>
</div>

<div class="tabs">
    <a class="tab-item <?php echo $tab === 'all' ? 'active' : ''; ?>" href="?tab=all&session_id=<?php echo $filterSessionId; ?>&semester_id=<?php echo $filterSemesterId; ?>">All</a>
    <a class="tab-item <?php echo $tab === 'active' ? 'active' : ''; ?>" href="?tab=active&session_id=<?php echo $filterSessionId; ?>&semester_id=<?php echo $filterSemesterId; ?>">Active</a>
    <a class="tab-item <?php echo $tab === 'completed' ? 'active' : ''; ?>" href="?tab=completed&session_id=<?php echo $filterSessionId; ?>&semester_id=<?php echo $filterSemesterId; ?>">Completed</a>
    <a class="tab-item <?php echo $tab === 'dropped' ? 'active' : ''; ?>" href="?tab=dropped&session_id=<?php echo $filterSessionId; ?>&semester_id=<?php echo $filterSemesterId; ?>">Dropped</a>
</div>

<div class="card mb-3" style="padding: 12px;">
    <form method="get" class="grid grid-3" style="align-items: end;">
        <input type="hidden" name="tab" value="<?php echo htmlspecialchars($tab); ?>">
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Session</label>
            <select name="session_id" class="form-select" onchange="this.form.submit()">
                <option value="">All Sessions</option>
                <?php foreach ($sessions as $s): ?>
                    <option value="<?php echo (int)$s['id']; ?>" <?php echo $filterSessionId === (int)$s['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($s['name']); ?>
                        <?php if ($s['is_current']): ?> (Current)<?php endif; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Semester</label>
            <select name="semester_id" class="form-select" onchange="this.form.submit()">
                <option value="">All Semesters</option>
                <?php foreach ($semesters as $sem): ?>
                    <option value="<?php echo (int)$sem['id']; ?>" <?php echo $filterSemesterId === (int)$sem['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($sem['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <a href="?tab=<?php echo htmlspecialchars($tab); ?>" class="btn btn-ghost btn-sm">Clear Filters</a>
        </div>
    </form>
</div>

<?php if (empty($courses)): ?>
    <div class="empty-state">
        <div class="icon">📚</div>
        <h3>No courses found</h3>
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
                    <?php if ($c['semester_name'] || $c['session_name']): ?>
                        <p class="text-muted" style="font-size:.85rem;">
                            <?php echo htmlspecialchars($c['semester_name'] ?? ''); ?>
                            <?php if ($c['session_name']): ?> — <?php echo htmlspecialchars($c['session_name']); ?><?php endif; ?>
                        </p>
                    <?php endif; ?>
                    <div class="progress"><div class="progress-bar" style="width: <?php echo $c['progress']; ?>%"></div></div>
                    <p class="text-muted" style="font-size:.85rem;"><?php echo $c['progress']; ?>% complete</p>
                    <a href="<?php echo BASE_URL; ?>/student/learn.php?course_id=<?php echo (int)$c['id']; ?>&lesson_id=<?php echo (int)$nextLesson; ?>" class="btn btn-primary btn-sm mt-2">Continue</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
