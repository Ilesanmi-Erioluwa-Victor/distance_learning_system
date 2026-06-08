<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { redirect('/courses.php'); }

$pdo = Database::getConnection();

// Course
$stmt = $pdo->prepare("
    SELECT c.*, u.first_name, u.last_name, u.bio, u.profile_photo,
           d.name AS department_name,
           sem.name AS semester_name, s.name AS session_name
    FROM courses c
    JOIN users u ON c.instructor_id = u.id
    LEFT JOIN departments d ON c.department_id = d.id
    LEFT JOIN semesters sem ON c.semester_id = sem.id
    LEFT JOIN academic_sessions s ON c.academic_session_id = s.id
    WHERE c.id = ? AND c.is_published
");
$stmt->execute([$id]);
$course = $stmt->fetch();
if (!$course) {
    setFlash('error', 'Course not found.');
    redirect('/courses.php');
}

// Modules + lessons
$stmt = $pdo->prepare("
    SELECT m.id as module_id, m.title as module_title, m.sort_order as module_sort, m.description as module_desc,
           l.id as lesson_id, l.title as lesson_title, l.duration, l.sort_order as lesson_sort
    FROM modules m
    LEFT JOIN lessons l ON l.module_id = m.id
    WHERE m.course_id = ?
    ORDER BY m.sort_order, l.sort_order
");
$stmt->execute([$id]);
$rows = $stmt->fetchAll();

$modules = [];
foreach ($rows as $r) {
    $mid = $r['module_id'];
    if (!isset($modules[$mid])) {
        $modules[$mid] = [
            'id' => $mid,
            'title' => $r['module_title'],
            'description' => $r['module_desc'],
            'lessons' => []
        ];
    }
    if ($r['lesson_id']) {
        $modules[$mid]['lessons'][] = [
            'id' => $r['lesson_id'],
            'title' => $r['lesson_title'],
            'duration' => $r['duration'],
        ];
    }
}

// Counts
$enrollCount = (int) $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE course_id = ?")->execute([$id]) ? 0 : 0;
$stmt2 = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE course_id = ?");
$stmt2->execute([$id]);
$enrollCount = (int) $stmt2->fetchColumn();

$lessonTotal = 0;
foreach ($modules as $m) $lessonTotal += count($m['lessons']);

// Enrollment state
$currentUser = isLoggedIn() ? getCurrentUser() : null;
$enrollment = null;
$progress = 0;
if ($currentUser && $currentUser['role'] === 'student') {
    $stmt = $pdo->prepare("SELECT * FROM enrollments WHERE student_id = ? AND course_id = ?");
    $stmt->execute([$currentUser['id'], $id]);
    $enrollment = $stmt->fetch();
    if ($enrollment) {
        $progress = calculateCourseProgress((int)$currentUser['id'], $id);
    }
}
$isInstructorOwner = $currentUser && $currentUser['role'] === 'instructor' && (int)$currentUser['id'] === (int)$course['instructor_id'];

$pageTitle = $course['title'];
include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <a href="<?php echo BASE_URL; ?>/courses.php" class="btn btn-ghost">← Back</a>
</div>

<div class="course-detail">
    <div>
        <div class="card mb-3">
            <div style="aspect-ratio: 16/9; background: var(--color-primary-light); display: flex; align-items: center; justify-content: center; color: var(--color-primary); font-size: 4rem; overflow: hidden;">
                <?php if (!empty($course['thumbnail']) && file_exists(__DIR__ . '/uploads/thumbnails/' . basename($course['thumbnail']))): ?>
                    <img src="<?php echo BASE_URL; ?>/uploads/thumbnails/<?php echo htmlspecialchars(basename($course['thumbnail'])); ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                <?php else: ?>📘<?php endif; ?>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <span class="badge badge-info"><?php echo htmlspecialchars($course['level']); ?></span>
                    <span class="badge badge-muted"><?php echo htmlspecialchars($course['department_name'] ?? ''); ?></span>
                    <span class="badge badge-muted"><?php echo htmlspecialchars($course['semester_name'] ?? ''); ?> Semester</span>
                    <span class="badge badge-muted"><?php echo htmlspecialchars($course['session_name'] ?? ''); ?></span>
                    <?php if (!empty($course['duration'])): ?>
                        <span class="badge badge-muted">⏱ <?php echo htmlspecialchars($course['duration']); ?></span>
                    <?php endif; ?>
                </div>
                <h1><?php echo htmlspecialchars($course['title']); ?></h1>
                <p class="mt-2"><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">📚 Course Content</div>
            <div class="card-body">
                <?php if (empty($modules)): ?>
                    <p class="text-muted">No content published yet.</p>
                <?php else: ?>
                    <?php foreach ($modules as $m): ?>
                        <div class="accordion-item">
                            <div class="accordion-header">
                                <span><?php echo htmlspecialchars($m['title']); ?></span>
                                <span class="chevron">▶</span>
                            </div>
                            <div class="accordion-body">
                                <?php if (!empty($m['description'])): ?>
                                    <p class="text-muted"><?php echo htmlspecialchars($m['description']); ?></p>
                                <?php endif; ?>
                                <?php if (empty($m['lessons'])): ?>
                                    <p class="text-muted">No lessons yet.</p>
                                <?php else: ?>
                                    <?php foreach ($m['lessons'] as $l): ?>
                                        <div style="padding: 8px 0; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between;">
                                            <span>
                                                <?php if ($enrollment || $isInstructorOwner): ?>
                                                    📄 <?php echo htmlspecialchars($l['title']); ?>
                                                <?php else: ?>
                                                    🔒 <?php echo htmlspecialchars($l['title']); ?>
                                                <?php endif; ?>
                                            </span>
                                            <?php if (!empty($l['duration'])): ?>
                                                <span class="text-muted" style="font-size:.85rem;"><?php echo (int)$l['duration']; ?> min</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div>
        <div class="card course-sidebar-card">
            <div class="card-body">
                <?php if ($enrollment): ?>
                    <h3 class="mb-2">Your Progress</h3>
                    <div class="progress mb-2"><div class="progress-bar" style="width: <?php echo $progress; ?>%"></div></div>
                    <p class="text-muted mb-3"><?php echo $progress; ?>% complete</p>
                    <?php
                    // Find first incomplete lesson
                    $stmt = $pdo->prepare("
                        SELECT l.id FROM lessons l
                        JOIN modules m ON l.module_id = m.id
                        WHERE m.course_id = ?
                          AND l.id NOT IN (SELECT lesson_id FROM lesson_progress WHERE student_id = ? AND completed)
                        ORDER BY m.sort_order, l.sort_order
                        LIMIT 1
                    ");
                    $stmt->execute([$id, $currentUser['id']]);
                    $nextLesson = $stmt->fetchColumn();
                    if (!$nextLesson) {
                        $stmt = $pdo->prepare("SELECT l.id FROM lessons l JOIN modules m ON l.module_id = m.id WHERE m.course_id = ? ORDER BY m.sort_order, l.sort_order LIMIT 1");
                        $stmt->execute([$id]);
                        $nextLesson = $stmt->fetchColumn();
                    }
                    ?>
                    <a href="<?php echo BASE_URL; ?>/student/learn.php?course_id=<?php echo $id; ?>&lesson_id=<?php echo (int)$nextLesson; ?>" class="btn btn-primary btn-block">Continue Learning</a>
                <?php elseif ($isInstructorOwner): ?>
                    <h3>You own this course</h3>
                    <a href="<?php echo BASE_URL; ?>/instructor/course_builder.php?course_id=<?php echo $id; ?>" class="btn btn-primary btn-block">Manage Course</a>
                <?php elseif ($currentUser): ?>
                    <form method="post" action="<?php echo BASE_URL; ?>/actions/student/enroll.php">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="course_id" value="<?php echo $id; ?>">
                        <button type="submit" class="btn btn-primary btn-block btn-lg">Enroll Now</button>
                    </form>
                <?php else: ?>
                    <h3 class="mb-2">Ready to start?</h3>
                    <a href="<?php echo BASE_URL; ?>/login.php?redirect=<?php echo urlencode('/course_detail.php?id='.$id); ?>" class="btn btn-primary btn-block">Login to Enroll</a>
                    <p class="text-muted text-center mt-2" style="font-size:.85rem;">Don't have an account? <a href="<?php echo BASE_URL; ?>/register.php">Register</a></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">👨‍🏫 Instructor</div>
            <div class="card-body">
                <div class="d-flex items-center gap-2">
                    <div class="avatar avatar-md">
                        <?php if (!empty($course['profile_photo'])): ?>
                            <img src="<?php echo BASE_URL; ?>/uploads/profiles/<?php echo htmlspecialchars(basename($course['profile_photo'])); ?>" alt="">
                        <?php else: ?>
                            <?php echo getInitials($course['first_name'], $course['last_name']); ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <strong><?php echo htmlspecialchars($course['first_name'] . ' ' . $course['last_name']); ?></strong>
                        <p class="text-muted" style="font-size:.85rem; margin:0;"><?php echo htmlspecialchars($course['bio'] ?? 'Instructor at Delta State Polytechnic'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body text-center">
                <p><strong><?php echo $enrollCount; ?></strong> students enrolled</p>
                <p><strong><?php echo $lessonTotal; ?></strong> lessons</p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
