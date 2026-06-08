<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('student');

$courseId = (int)($_GET['course_id'] ?? 0);
$lessonId = (int)($_GET['lesson_id'] ?? 0);
if ($courseId <= 0) redirect('/student/dashboard.php');

$pdo = Database::getConnection();
$uid = (int) getCurrentUser()['id'];

// Verify enrollment
$stmt = $pdo->prepare("SELECT * FROM enrollments WHERE student_id = ? AND course_id = ?");
$stmt->execute([$uid, $courseId]);
$enrollment = $stmt->fetch();
if (!$enrollment) { setFlash('error', 'You are not enrolled in this course.'); redirect('/student/courses.php'); }

// Course
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$courseId]);
$course = $stmt->fetch();

// Modules + lessons
$stmt = $pdo->prepare("
    SELECT m.id as module_id, m.title as module_title, m.sort_order as module_sort,
           l.id as lesson_id, l.title as lesson_title, l.sort_order as lesson_sort
    FROM modules m
    LEFT JOIN lessons l ON l.module_id = m.id
    WHERE m.course_id = ?
    ORDER BY m.sort_order, l.sort_order
");
$stmt->execute([$courseId]);
$rows = $stmt->fetchAll();

$modules = [];
foreach ($rows as $r) {
    $mid = $r['module_id'];
    if (!isset($modules[$mid])) {
        $modules[$mid] = ['id'=>$mid, 'title'=>$r['module_title'], 'lessons'=>[]];
    }
    if ($r['lesson_id']) {
        $modules[$mid]['lessons'][] = ['id'=>$r['lesson_id'], 'title'=>$r['lesson_title']];
    }
}

// Completed lessons
$stmt = $pdo->prepare("SELECT lesson_id FROM lesson_progress WHERE student_id = ? AND completed");
$stmt->execute([$uid]);
$completedIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Find current lesson or fall back
$currentLesson = null;
if ($lessonId) {
    foreach ($modules as $m) {
        foreach ($m['lessons'] as $l) {
            if ($l['id'] == $lessonId) $currentLesson = $l;
        }
    }
}
if (!$currentLesson && !empty($modules)) {
    foreach ($modules as $m) {
        if (!empty($m['lessons'])) { $currentLesson = $m['lessons'][0]; break; }
    }
}
if (!$currentLesson) {
    setFlash('info', 'This course has no lessons yet.');
    redirect('/student/courses.php');
}

// Fetch full lesson
$stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = ?");
$stmt->execute([$currentLesson['id']]);
$currentLesson = $stmt->fetch();

// Determine prev/next
$allLessons = [];
foreach ($modules as $m) foreach ($m['lessons'] as $l) $allLessons[] = $l;
$currentIdx = -1;
foreach ($allLessons as $i => $l) if ($l['id'] == $currentLesson['id']) $currentIdx = $i;
$prevLesson = $allLessons[$currentIdx-1] ?? null;
$nextLesson = $allLessons[$currentIdx+1] ?? null;

$isCompleted = in_array($currentLesson['id'], $completedIds, true);
$progress = calculateCourseProgress($uid, $courseId);

$pageTitle = $currentLesson['title'];
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1 style="font-size:1.3rem;"><?php echo htmlspecialchars($course['title']); ?></h1>
</div>

<div class="learn-layout">
    <aside class="learn-sidebar">
        <h3>Course Content</h3>
        <?php foreach ($modules as $m): ?>
            <div class="module-item">
                <div class="module-title"><?php echo htmlspecialchars($m['title']); ?></div>
                <?php foreach ($m['lessons'] as $l): ?>
                    <a class="lesson-item <?php echo $l['id']==$currentLesson['id']?'active':''; ?>"
                       href="?course_id=<?php echo $courseId; ?>&lesson_id=<?php echo $l['id']; ?>">
                        <span><?php echo in_array($l['id'], $completedIds)?'✓':'○'; ?></span>
                        <span style="flex:1;"><?php echo htmlspecialchars($l['title']); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        <a href="<?php echo BASE_URL; ?>/student/courses.php" class="btn btn-ghost btn-sm mt-2">← My Courses</a>
    </aside>

    <div>
        <div class="lesson-content">
            <h2><?php echo htmlspecialchars($currentLesson['title']); ?></h2>
            <div class="progress mb-2"><div class="progress-bar" style="width: <?php echo $progress; ?>%"></div></div>
            <p class="text-muted" style="font-size:.85rem;">Course progress: <?php echo $progress; ?>%</p>

            <?php if (!empty($currentLesson['video_url'])): ?>
                <div class="video-wrapper">
                    <?php $embed = youtubeEmbedUrl($currentLesson['video_url']); ?>
                    <?php if ($embed): ?>
                        <iframe src="<?php echo $embed; ?>" allowfullscreen></iframe>
                    <?php else: ?>
                        <video controls><source src="<?php echo htmlspecialchars($currentLesson['video_url']); ?>"></video>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($currentLesson['content'])): ?>
                <div class="body">
                    <?php echo $currentLesson['content']; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($currentLesson['file_path'])): ?>
                <p class="mt-3">
                    <a href="<?php echo BASE_URL; ?>/uploads/resources/<?php echo htmlspecialchars(basename($currentLesson['file_path'])); ?>" class="btn btn-outline" target="_blank">📎 Download Resource</a>
                </p>
            <?php endif; ?>

            <div class="actions">
                <?php if ($prevLesson): ?>
                    <a href="?course_id=<?php echo $courseId; ?>&lesson_id=<?php echo $prevLesson['id']; ?>" class="btn btn-ghost">← Previous</a>
                <?php else: ?><span></span><?php endif; ?>

                <?php if ($isCompleted): ?>
                    <span class="badge badge-success">✓ Lesson Complete</span>
                <?php else: ?>
                    <form method="post" action="<?php echo BASE_URL; ?>/actions/student/complete_lesson.php">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="lesson_id" value="<?php echo (int)$currentLesson['id']; ?>">
                        <input type="hidden" name="course_id" value="<?php echo (int)$courseId; ?>">
                        <input type="hidden" name="next_lesson_id" value="<?php echo (int)($nextLesson['id'] ?? 0); ?>">
                        <button type="submit" class="btn btn-success">✓ Mark as Complete</button>
                    </form>
                <?php endif; ?>

                <?php if ($nextLesson): ?>
                    <a href="?course_id=<?php echo $courseId; ?>&lesson_id=<?php echo $nextLesson['id']; ?>" class="btn btn-primary">Next →</a>
                <?php else: ?><span></span><?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
