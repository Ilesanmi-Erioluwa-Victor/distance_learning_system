<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$pdo = Database::getConnection();
$totalUsers    = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalCourses  = (int) $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$totalEnrolls  = (int) $pdo->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();
$totalRevenue  = 0; // no payments

// Last 7 days registrations
$labels = [];
$data   = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('M j', strtotime($d));
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE DATE(created_at) = ?");
    $stmt->execute([$d]);
    $data[] = (int) $stmt->fetchColumn();
}

// Latest users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
$latestUsers = $stmt->fetchAll();

// Latest courses
$stmt = $pdo->query("
    SELECT c.*, u.first_name, u.last_name, d.name as dept_name,
           (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) as enroll_count
    FROM courses c
    JOIN users u ON c.instructor_id = u.id
    LEFT JOIN departments d ON c.department_id = d.id
    ORDER BY c.created_at DESC LIMIT 5
");
$latestCourses = $stmt->fetchAll();

$pageTitle = 'Admin Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>⚙️ Admin Dashboard</h1>
</div>

<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div><div class="stat-number"><?php echo $totalUsers; ?></div><div class="stat-label">Total Users</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📚</div>
        <div><div class="stat-number"><?php echo $totalCourses; ?></div><div class="stat-label">Total Courses</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📋</div>
        <div><div class="stat-number"><?php echo $totalEnrolls; ?></div><div class="stat-label">Enrollments</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div><div class="stat-number">₦0</div><div class="stat-label">Revenue</div></div>
    </div>
</div>

<div class="dashboard-section">
    <h2>📈 New Users (Last 7 Days)</h2>
    <canvas id="registrationChart" style="max-height: 300px;"></canvas>
</div>

<div class="dashboard-section">
    <h2>👥 Latest Users</h2>
    <div class="table-wrapper">
        <table class="table">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th></tr></thead>
            <tbody>
            <?php foreach ($latestUsers as $u): ?>
                <tr>
                    <td><?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><span class="badge badge-info"><?php echo $u['role']; ?></span></td>
                    <td>
                        <?php if ($u['is_active']): ?>
                            <span class="badge badge-success">Active</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Disabled</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo timeAgo($u['created_at']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="dashboard-section">
    <h2>📚 Latest Courses</h2>
    <div class="table-wrapper">
        <table class="table">
            <thead><tr><th>Title</th><th>Instructor</th><th>Department</th><th>Status</th><th>Enrolled</th></tr></thead>
            <tbody>
            <?php foreach ($latestCourses as $c): ?>
                <tr>
                    <td><?php echo htmlspecialchars($c['title']); ?></td>
                    <td><?php echo htmlspecialchars($c['first_name'] . ' ' . $c['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($c['dept_name'] ?? 'N/A'); ?></td>
                    <td>
                        <?php if ($c['is_published']): ?>
                            <span class="badge badge-success">Published</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Draft</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo (int)$c['enroll_count']; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const labels = <?php echo json_encode($labels); ?>;
const data   = <?php echo json_encode($data); ?>;
new Chart(document.getElementById('registrationChart'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'New Users',
            data: data,
            borderColor: '#1D4ED8',
            backgroundColor: 'rgba(29,78,216,.15)',
            fill: true,
            tension: 0.3
        }]
    },
    options: { responsive: true, maintainAspectRatio: false }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
