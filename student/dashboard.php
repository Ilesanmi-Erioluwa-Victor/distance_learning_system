<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('student');

$pdo = Database::getConnection();
$user = getCurrentUser();
$uid = (int)$user['id'];

// Stats
$enrolledCount = (int) $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE student_id = ?")->execute([$uid]) ? 0 : 0;
$stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE student_id = ?");
$stmt->execute([$uid]);
$enrolledCount = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM lesson_progress WHERE student_id = ? AND completed = 1");
$stmt->execute([$uid]);
$completedLessons = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM assignments a
    JOIN modules m ON a.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    JOIN enrollments e ON e.course_id = c.id
    WHERE e.student_id = ? AND a.due_date > NOW()
      AND a.id NOT IN (SELECT assignment_id FROM submissions WHERE student_id = ?)
");
$stmt->execute([$uid, $uid]);
$pendingAssignments = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COALESCE(AVG(percentage), 0) FROM quiz_attempts WHERE student_id = ?");
$stmt->execute([$uid]);
$avgQuiz = round((float)$stmt->fetchColumn(), 1);

// My courses
$stmt = $pdo->prepare("
    SELECT c.*, u.first_name, u.last_name, e.progress, e.status
    FROM enrollments e
    JOIN courses c ON c.id = e.course_id
    JOIN users u ON c.instructor_id = u.id
    WHERE e.student_id = ?
    ORDER BY e.enrolled_at DESC
    LIMIT 4
");
$stmt->execute([$uid]);
$myCourses = $stmt->fetchAll();

// Upcoming assignments
$stmt = $pdo->prepare("
    SELECT a.*, c.title as course_title, m.title as module_title,
           s.status as submission_status
    FROM assignments a
    JOIN modules m ON a.module_id = m.id
    JOIN courses c ON c.id = m.course_id
    JOIN enrollments e ON e.course_id = c.id
    LEFT JOIN submissions s ON s.assignment_id = a.id AND s.student_id = e.student_id
    WHERE e.student_id = ? AND a.due_date > NOW()
    ORDER BY a.due_date ASC
    LIMIT 5
");
$stmt->execute([$uid]);
$upcoming = $stmt->fetchAll();

// Announcements
$stmt = $pdo->prepare("
    SELECT a.*, u.first_name, u.last_name
    FROM announcements a
    JOIN users u ON a.author_id = u.id
    WHERE a.target IN ('all', 'students')
    ORDER BY a.created_at DESC
    LIMIT 3
");
$stmt->execute();
$announcements = $stmt->fetchAll();

$pageTitle = 'Student Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>! 👋</h1>
    <a href="<?php echo BASE_URL; ?>/courses.php" class="btn btn-primary">Browse More Courses</a>
</div>

<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-icon">📚</div>
        <div>
            <div class="stat-number"><?php echo $enrolledCount; ?></div>
            <div class="stat-label">Enrolled Courses</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div>
            <div class="stat-number"><?php echo $completedLessons; ?></div>
            <div class="stat-label">Completed Lessons</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📝</div>
        <div>
            <div class="stat-number"><?php echo $pendingAssignments; ?></div>
            <div class="stat-label">Pending Assignments</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🏆</div>
        <div>
            <div class="stat-number"><?php echo $avgQuiz; ?>%</div>
            <div class="stat-label">Avg Quiz Score</div>
        </div>
    </div>
</div>

<div class="dashboard-section">
    <h2>📚 My Courses</h2>
    <?php if (empty($myCourses)): ?>
        <div class="empty-state">
            <div class="icon">📚</div>
            <h3>No courses yet</h3>
            <p>Browse the catalog and enroll in your first course.</p>
            <a href="<?php echo BASE_URL; ?>/courses.php" class="btn btn-primary">Browse Courses</a>
        </div>
    <?php else: ?>
        <div class="grid grid-2">
            <?php foreach ($myCourses as $c):
                $nextLesson = null;
                $stmt2 = $pdo->prepare("
                    SELECT l.id FROM lessons l JOIN modules m ON l.module_id = m.id
                    WHERE m.course_id = ?
                      AND l.id NOT IN (SELECT lesson_id FROM lesson_progress WHERE student_id = ? AND completed = 1)
                    ORDER BY m.sort_order, l.sort_order LIMIT 1
                ");
                $stmt2->execute([$c['id'], $uid]);
                $nextLesson = $stmt2->fetchColumn() ?: null;
            ?>
                <div class="course-card">
                    <div class="info">
                        <span class="badge badge-info"><?php echo htmlspecialchars($c['level']); ?></span>
                        <h3 class="mt-1"><?php echo htmlspecialchars($c['title']); ?></h3>
                        <p>Instructor: <?php echo htmlspecialchars($c['first_name'] . ' ' . $c['last_name']); ?></p>
                        <div class="progress mt-1"><div class="progress-bar" style="width: <?php echo $c['progress']; ?>%"></div></div>
                        <p class="text-muted mt-1" style="font-size:.85rem;"><?php echo $c['progress']; ?>% complete</p>
                        <a href="<?php echo BASE_URL; ?>/student/learn.php?course_id=<?php echo (int)$c['id']; ?>&lesson_id=<?php echo (int)$nextLesson; ?>" class="btn btn-primary btn-sm mt-2">Continue</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="dashboard-section">
    <h2>📝 Upcoming Assignments</h2>
    <?php if (empty($upcoming)): ?>
        <p class="text-muted">No upcoming assignments. Great job staying on track! 🎉</p>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Assignment</th>
                        <th>Course</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($upcoming as $a): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($a['title']); ?></td>
                        <td><?php echo htmlspecialchars($a['course_title']); ?></td>
                        <td><?php echo formatDate($a['due_date'], 'M j, Y g:i A'); ?></td>
                        <td>
                            <?php if ($a['submission_status'] === 'graded'): ?>
                                <span class="badge badge-success">Graded</span>
                            <?php elseif ($a['submission_status'] === 'submitted'): ?>
                                <span class="badge badge-info">Submitted</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="dashboard-section">
    <h2>📢 Recent Announcements</h2>
    <?php if (empty($announcements)): ?>
        <p class="text-muted">No announcements.</p>
    <?php else: ?>
        <?php foreach ($announcements as $a): ?>
            <div class="card mb-2">
                <div class="card-body">
                    <h4><?php echo htmlspecialchars($a['title']); ?></h4>
                    <p class="text-muted" style="font-size:.85rem;">
                        By <?php echo htmlspecialchars($a['first_name'] . ' ' . $a['last_name']); ?>
                        · <?php echo timeAgo($a['created_at']); ?>
                    </p>
                    <p><?php echo nl2br(htmlspecialchars(substr($a['content'], 0, 200))); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
