<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('instructor');

$pdo = Database::getConnection();
$uid = (int) getCurrentUser()['id'];

// Stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE instructor_id = ?");
$stmt->execute([$uid]);
$myCourses = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(DISTINCT e.student_id) FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE c.instructor_id = ?");
$stmt->execute([$uid]);
$myStudents = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM submissions s
    JOIN assignments a ON s.assignment_id = a.id
    JOIN modules m ON a.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    WHERE c.instructor_id = ? AND s.status IN ('submitted', 'late')
");
$stmt->execute([$uid]);
$pendingSubmissions = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM quizzes q JOIN courses c ON q.course_id = c.id WHERE c.instructor_id = ? AND q.is_published = 1");
$stmt->execute([$uid]);
$quizzesPublished = (int) $stmt->fetchColumn();

// My courses
$stmt = $pdo->prepare("
    SELECT c.*, (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) as enroll_count,
           (SELECT COUNT(*) FROM lessons l JOIN modules m ON l.module_id = m.id WHERE m.course_id = c.id) as lesson_count
    FROM courses c WHERE c.instructor_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$uid]);
$courses = $stmt->fetchAll();

// Recent submissions
$stmt = $pdo->prepare("
    SELECT s.*, a.title as assignment_title, c.title as course_title,
           u.first_name, u.last_name
    FROM submissions s
    JOIN assignments a ON s.assignment_id = a.id
    JOIN modules m ON a.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    JOIN users u ON s.student_id = u.id
    WHERE c.instructor_id = ?
    ORDER BY s.submitted_at DESC
    LIMIT 10
");
$stmt->execute([$uid]);
$submissions = $stmt->fetchAll();

$pageTitle = 'Instructor Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>👨‍🏫 Welcome, <?php echo htmlspecialchars(getCurrentUser()['first_name']); ?>!</h1>
    <a href="<?php echo BASE_URL; ?>/instructor/create_course.php" class="btn btn-primary">+ New Course</a>
</div>

<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-icon">📚</div>
        <div><div class="stat-number"><?php echo $myCourses; ?></div><div class="stat-label">My Courses</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🎓</div>
        <div><div class="stat-number"><?php echo $myStudents; ?></div><div class="stat-label">Total Students</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📝</div>
        <div><div class="stat-number"><?php echo $pendingSubmissions; ?></div><div class="stat-label">Pending Submissions</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📋</div>
        <div><div class="stat-number"><?php echo $quizzesPublished; ?></div><div class="stat-label">Quizzes Published</div></div>
    </div>
</div>

<div class="dashboard-section">
    <h2>📚 My Courses</h2>
    <?php if (empty($courses)): ?>
        <div class="empty-state">
            <div class="icon">📚</div>
            <h3>No courses yet</h3>
            <p>Create your first course to get started.</p>
            <a href="<?php echo BASE_URL; ?>/instructor/create_course.php" class="btn btn-primary">Create Course</a>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Enrollments</th>
                        <th>Lessons</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($courses as $c): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($c['title']); ?></strong></td>
                        <td><?php echo htmlspecialchars($c['category']); ?></td>
                        <td><?php echo (int)$c['enroll_count']; ?></td>
                        <td><?php echo (int)$c['lesson_count']; ?></td>
                        <td>
                            <?php if ($c['is_published']): ?>
                                <span class="badge badge-success">Published</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Draft</span>
                            <?php endif; ?>
                        </td>
                        <td class="actions">
                            <a href="<?php echo BASE_URL; ?>/course_detail.php?id=<?php echo (int)$c['id']; ?>" class="btn btn-sm btn-ghost" target="_blank">View</a>
                            <a href="<?php echo BASE_URL; ?>/instructor/course_builder.php?course_id=<?php echo (int)$c['id']; ?>" class="btn btn-sm btn-primary">Build</a>
                            <a href="<?php echo BASE_URL; ?>/instructor/edit_course.php?course_id=<?php echo (int)$c['id']; ?>" class="btn btn-sm btn-outline">Edit</a>
                            <form method="post" action="<?php echo BASE_URL; ?>/actions/instructor/delete_course.php" style="display:inline;">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="course_id" value="<?php echo (int)$c['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" data-confirm="Delete this course? This cannot be undone.">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="dashboard-section">
    <h2>📝 Recent Submissions</h2>
    <?php if (empty($submissions)): ?>
        <p class="text-muted">No submissions yet.</p>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr><th>Student</th><th>Assignment</th><th>Course</th><th>Submitted</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                <?php foreach ($submissions as $s): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($s['assignment_title']); ?></td>
                        <td><?php echo htmlspecialchars($s['course_title']); ?></td>
                        <td><?php echo timeAgo($s['submitted_at']); ?></td>
                        <td>
                            <?php if ($s['status'] === 'graded'): ?>
                                <span class="badge badge-success">Graded</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td><a href="<?php echo BASE_URL; ?>/instructor/grade_submissions.php?assignment_id=<?php echo (int)$s['assignment_id']; ?>" class="btn btn-sm btn-primary">Grade</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
