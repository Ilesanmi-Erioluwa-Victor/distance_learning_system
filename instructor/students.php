<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('instructor');

$uid = (int) getCurrentUser()['id'];
$pdo = Database::getConnection();
$courseFilter = (int)($_GET['course_id'] ?? 0);

$where = "c.instructor_id = ?";
$params = [$uid];
if ($courseFilter) { $where .= " AND c.id = ?"; $params[] = $courseFilter; }

$stmt = $pdo->prepare("
    SELECT u.id, u.first_name, u.last_name, u.email, c.title as course_title, c.id as course_id,
           e.enrolled_at, e.progress
    FROM enrollments e
    JOIN users u ON e.student_id = u.id
    JOIN courses c ON e.course_id = c.id
    WHERE $where
    ORDER BY e.enrolled_at DESC
");
$stmt->execute($params);
$students = $stmt->fetchAll();

$myCourses = $pdo->prepare("SELECT * FROM courses WHERE instructor_id = ? ORDER BY title");
$myCourses->execute([$uid]);
$courses = $myCourses->fetchAll();

$pageTitle = 'Students';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>🎓 Students</h1>
</div>

<form method="get" class="card mb-3" style="padding: 16px;">
    <select name="course_id" class="form-select" onchange="this.form.submit()">
        <option value="0">All Courses</option>
        <?php foreach ($courses as $c): ?>
            <option value="<?php echo $c['id']; ?>" <?php echo $courseFilter===(int)$c['id']?'selected':''; ?>>
                <?php echo htmlspecialchars($c['title']); ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<?php if (empty($students)): ?>
    <div class="empty-state">
        <div class="icon">🎓</div>
        <h3>No students yet</h3>
    </div>
<?php else: ?>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Course</th><th>Enrolled</th><th>Progress</th></tr>
            </thead>
            <tbody>
            <?php foreach ($students as $s): ?>
                <tr>
                    <td><?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($s['email']); ?></td>
                    <td><?php echo htmlspecialchars($s['course_title']); ?></td>
                    <td><?php echo formatDate($s['enrolled_at']); ?></td>
                    <td>
                        <div class="progress" style="width: 120px;"><div class="progress-bar" style="width: <?php echo $s['progress']; ?>%"></div></div>
                        <small><?php echo $s['progress']; ?>%</small>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
