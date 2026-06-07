<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = Database::getConnection();

$search  = trim($_GET['search'] ?? '');
$cat     = trim($_GET['category'] ?? '');
$level   = trim($_GET['level'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;

$where = ["c.is_published = 1"];
$params = [];

if ($search !== '') {
    $where[] = "(c.title LIKE ? OR c.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($cat !== '') {
    $where[] = "c.category = ?";
    $params[] = $cat;
}
if ($level !== '') {
    $where[] = "c.level = ?";
    $params[] = $level;
}
$whereSql = implode(' AND ', $where);

// Count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM courses c WHERE $whereSql");
$stmt->execute($params);
$total = (int) $stmt->fetchColumn();
$pages = max(1, (int) ceil($total / $perPage));
$offset = ($page - 1) * $perPage;

// Data
$stmt = $pdo->prepare("
    SELECT c.*, u.first_name, u.last_name,
           (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) as enroll_count
    FROM courses c
    JOIN users u ON c.instructor_id = u.id
    WHERE $whereSql
    ORDER BY c.created_at DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$courses = $stmt->fetchAll();

$categories = $pdo->query("SELECT DISTINCT category FROM courses ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'Courses';
include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>Browse Courses</h1>
</div>

<form method="get" class="card mb-3" style="padding: 20px;">
    <div class="grid grid-3">
        <div class="form-group mb-0">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-input" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search courses...">
        </div>
        <div class="form-group mb-0">
            <label class="form-label">Category</label>
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                <?php foreach ($categories as $catOpt): ?>
                    <option value="<?php echo htmlspecialchars($catOpt); ?>" <?php echo $cat === $catOpt ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($catOpt); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group mb-0">
            <label class="form-label">Level</label>
            <select name="level" class="form-select">
                <option value="">All Levels</option>
                <option value="Beginner" <?php echo $level==='Beginner'?'selected':''; ?>>Beginner</option>
                <option value="Intermediate" <?php echo $level==='Intermediate'?'selected':''; ?>>Intermediate</option>
                <option value="Advanced" <?php echo $level==='Advanced'?'selected':''; ?>>Advanced</option>
            </select>
        </div>
    </div>
    <div class="mt-2">
        <button class="btn btn-primary" type="submit">Apply Filters</button>
        <a class="btn btn-ghost" href="<?php echo BASE_URL; ?>/courses.php">Reset</a>
    </div>
</form>

<?php if (empty($courses)): ?>
    <div class="empty-state">
        <div class="icon">🔎</div>
        <h3>No courses found</h3>
        <p>Try adjusting your filters.</p>
    </div>
<?php else: ?>
    <div class="grid grid-3">
        <?php foreach ($courses as $c): ?>
            <a href="<?php echo BASE_URL; ?>/course_detail.php?id=<?php echo (int)$c['id']; ?>" class="course-card">
                <div class="thumbnail">
                    <?php if (!empty($c['thumbnail']) && file_exists(__DIR__ . '/uploads/thumbnails/' . basename($c['thumbnail']))): ?>
                        <img src="<?php echo BASE_URL; ?>/uploads/thumbnails/<?php echo htmlspecialchars(basename($c['thumbnail'])); ?>" alt="">
                    <?php else: ?>
                        <span>📘</span>
                    <?php endif; ?>
                </div>
                <div class="info">
                    <span class="badge badge-info"><?php echo htmlspecialchars($c['level']); ?></span>
                    <h3 class="mt-1"><?php echo htmlspecialchars($c['title']); ?></h3>
                    <p><?php echo htmlspecialchars(substr($c['description'], 0, 100)) . '...'; ?></p>
                    <div class="meta">
                        <span><?php echo htmlspecialchars($c['first_name'] . ' ' . $c['last_name']); ?></span>
                        <span><?php echo (int)$c['enroll_count']; ?> enrolled</span>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($pages > 1): ?>
        <div class="pagination">
            <?php for ($p = 1; $p <= $pages; $p++): ?>
                <a class="page-item <?php echo $p === $page ? 'active' : ''; ?>"
                   href="?<?php echo http_build_query(array_merge($_GET, ['page' => $p])); ?>"><?php echo $p; ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
