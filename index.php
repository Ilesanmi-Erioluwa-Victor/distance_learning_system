<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = Database::getConnection();

// Stats
$studentCount    = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='student' AND is_active=1")->fetchColumn();
$courseCount     = (int) $pdo->query("SELECT COUNT(*) FROM courses WHERE is_published=1")->fetchColumn();
$instructorCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='instructor' AND is_active=1")->fetchColumn();

// Featured courses (top 6 by enrollment)
$stmt = $pdo->query("
    SELECT c.*, u.first_name, u.last_name,
           (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) as enroll_count
    FROM courses c
    JOIN users u ON c.instructor_id = u.id
    WHERE c.is_published = 1
    ORDER BY enroll_count DESC, c.created_at DESC
    LIMIT 6
");
$courses = $stmt->fetchAll();

$pageTitle = 'Home';
include __DIR__ . '/includes/header.php';
?>

<style>
.hero {
    background: linear-gradient(135deg, #1D4ED8, #0F172A);
    color: #fff; text-align: center;
    padding: 80px 20px;
    border-radius: var(--radius-lg);
    margin-bottom: 30px;
}
.hero h1 { color: #fff; font-size: 2.6rem; margin-bottom: 12px; }
.hero p { font-size: 1.1rem; opacity: .9; margin-bottom: 24px; }
.hero .hero-buttons { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
.hero .btn-outline { color: #fff; border-color: #fff; }
.hero .btn-outline:hover { background: #fff; color: var(--color-primary); }

.stats-bar { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; background: #fff; padding: 24px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); margin-bottom: 40px; }
.stat-item { text-align: center; }
.stat-item .num { font-size: 2rem; font-weight: 800; color: var(--color-primary); }
.stat-item .label { color: var(--color-text-muted); }

.how-it-works { background: #fff; padding: 40px 24px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); margin-bottom: 40px; }
.how-it-works h2 { text-align: center; margin-bottom: 30px; }
.how-steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
.how-step { text-align: center; }
.how-step .ico { font-size: 3rem; margin-bottom: 12px; }
.how-step h4 { margin-bottom: 6px; }
.how-step p { color: var(--color-text-muted); font-size: .9rem; }

@media (max-width: 768px) {
    .hero { padding: 50px 20px; }
    .hero h1 { font-size: 1.8rem; }
    .stats-bar, .how-steps { grid-template-columns: 1fr; }
}
</style>

<section class="hero">
    <div class="logo-mark" style="margin: 0 auto 16px; width: 64px; height: 64px; font-size: 1.8rem;">W</div>
    <h1>Learn From Anywhere, Anytime</h1>
    <p>Delta State Polytechnic's Official Distance Learning Portal</p>
    <div class="hero-buttons">
        <a href="<?php echo BASE_URL; ?>/courses.php" class="btn btn-primary btn-lg">Browse Courses</a>
        <a href="<?php echo BASE_URL; ?>/register.php" class="btn btn-outline btn-lg">Get Started Free</a>
    </div>
</section>

<section class="stats-bar">
    <div class="stat-item">
        <div class="num"><?php echo $studentCount; ?>+</div>
        <div class="label">Students</div>
    </div>
    <div class="stat-item">
        <div class="num"><?php echo $courseCount; ?>+</div>
        <div class="label">Courses</div>
    </div>
    <div class="stat-item">
        <div class="num"><?php echo $instructorCount; ?>+</div>
        <div class="label">Instructors</div>
    </div>
</section>

<section>
    <div class="section-header">
        <h2>Popular Courses</h2>
        <a href="<?php echo BASE_URL; ?>/courses.php" class="btn btn-outline btn-sm">View All</a>
    </div>
    <?php if (empty($courses)): ?>
        <div class="empty-state">
            <div class="icon">📚</div>
            <h3>No courses yet</h3>
            <p>Check back soon — new courses are being added!</p>
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
    <?php endif; ?>
</section>

<section class="how-it-works">
    <h2>How It Works</h2>
    <div class="how-steps">
        <div class="how-step">
            <div class="ico">📝</div>
            <h4>1. Register</h4>
            <p>Create your free account in under a minute.</p>
        </div>
        <div class="how-step">
            <div class="ico">📚</div>
            <h4>2. Enroll</h4>
            <p>Pick a course and enroll with one click.</p>
        </div>
        <div class="how-step">
            <div class="ico">🎓</div>
            <h4>3. Learn</h4>
            <p>Access content at your own pace, anywhere.</p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
