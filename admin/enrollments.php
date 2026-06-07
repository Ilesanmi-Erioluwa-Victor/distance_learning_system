<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$pdo = Database::getConnection();
$stmt = $pdo->query("
    SELECT e.*, u.first_name, u.last_name, u.email, c.title as course_title
    FROM enrollments e
    JOIN users u ON e.student_id = u.id
    JOIN courses c ON e.course_id = c.id
    ORDER BY e.enrolled_at DESC
");
$rows = $stmt->fetchAll();

$pageTitle = 'Enrollments';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>📋 Enrollments</h1>
</div>

<div class="table-wrapper">
    <table class="table">
        <thead>
            <tr><th>Student</th><th>Email</th><th>Course</th><th>Enrolled</th><th>Progress</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?php echo htmlspecialchars($r['first_name'] . ' ' . $r['last_name']); ?></td>
                <td><?php echo htmlspecialchars($r['email']); ?></td>
                <td><?php echo htmlspecialchars($r['course_title']); ?></td>
                <td><?php echo formatDate($r['enrolled_at']); ?></td>
                <td>
                    <div class="progress" style="width: 100px;"><div class="progress-bar" style="width: <?php echo $r['progress']; ?>%"></div></div>
                </td>
                <td>
                    <span class="badge badge-<?php echo $r['status']==='completed'?'success':($r['status']==='dropped'?'danger':'primary'); ?>">
                        <?php echo ucfirst($r['status']); ?>
                    </span>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
