<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$pdo = Database::getConnection();
$stmt = $pdo->query("
    SELECT c.*, u.first_name, u.last_name,
           (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) as enroll_count
    FROM courses c JOIN users u ON c.instructor_id = u.id
    ORDER BY c.created_at DESC
");
$courses = $stmt->fetchAll();

$pageTitle = 'All Courses';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>📚 All Courses</h1>
</div>

<div class="table-wrapper">
    <table class="table">
        <thead>
            <tr><th>Title</th><th>Instructor</th><th>Category</th><th>Enrollments</th><th>Published</th><th>Created</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($courses as $c): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($c['title']); ?></strong></td>
                <td><?php echo htmlspecialchars($c['first_name'] . ' ' . $c['last_name']); ?></td>
                <td><?php echo htmlspecialchars($c['category']); ?></td>
                <td><?php echo (int)$c['enroll_count']; ?></td>
                <td>
                    <form method="post" action="<?php echo BASE_URL; ?>/actions/admin/toggle_course.php" style="display:inline;">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="course_id" value="<?php echo (int)$c['id']; ?>">
                        <button class="btn btn-sm btn-<?php echo $c['is_published']?'warning':'success'; ?>" data-confirm="Toggle publish?">
                            <?php echo $c['is_published']?'Unpublish':'Publish'; ?>
                        </button>
                    </form>
                </td>
                <td><?php echo formatDate($c['created_at']); ?></td>
                <td class="actions">
                    <form method="post" action="<?php echo BASE_URL; ?>/actions/admin/delete_course.php" style="display:inline;">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="course_id" value="<?php echo (int)$c['id']; ?>">
                        <button class="btn btn-sm btn-danger" data-confirm="Delete this course?">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
