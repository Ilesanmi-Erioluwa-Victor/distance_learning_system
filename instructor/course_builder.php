<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('instructor');

$courseId = (int)($_GET['course_id'] ?? 0);
$uid = (int) getCurrentUser()['id'];
$pdo = Database::getConnection();

$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND instructor_id = ?");
$stmt->execute([$courseId, $uid]);
$course = $stmt->fetch();
if (!$course) { setFlash('error', 'Course not found.'); redirect('/instructor/dashboard.php'); }

// Modules + lessons
$stmt = $pdo->prepare("
    SELECT m.id as module_id, m.title as module_title, m.sort_order as module_sort, m.description as module_desc,
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
        $modules[$mid] = ['id'=>$mid, 'title'=>$r['module_title'], 'description'=>$r['module_desc'], 'lessons'=>[]];
    }
    if ($r['lesson_id']) $modules[$mid]['lessons'][] = ['id'=>$r['lesson_id'], 'title'=>$r['lesson_title']];
}

$editModule = null;
$editLesson = null;
if (isset($_GET['edit_module'])) {
    $stmt = $pdo->prepare("SELECT * FROM modules WHERE id = ? AND course_id = ?");
    $stmt->execute([(int)$_GET['edit_module'], $courseId]);
    $editModule = $stmt->fetch();
}
if (isset($_GET['edit_lesson'])) {
    $stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = ? AND module_id IN (SELECT id FROM modules WHERE course_id = ?)");
    $stmt->execute([(int)$_GET['edit_lesson'], $courseId]);
    $editLesson = $stmt->fetch();
}

$pageTitle = 'Build Course';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>🛠 <?php echo htmlspecialchars($course['title']); ?></h1>
    <a href="<?php echo BASE_URL; ?>/instructor/dashboard.php" class="btn btn-ghost">← Back</a>
</div>

<div style="display: grid; grid-template-columns: 30% 70%; gap: 20px;">
    <div>
        <div class="card">
            <div class="card-header">📚 Modules & Lessons</div>
            <div class="card-body">
                <?php if (empty($modules)): ?>
                    <p class="text-muted">No modules yet. Add one to start.</p>
                <?php else: ?>
                    <?php foreach ($modules as $m): ?>
                        <div class="accordion-item">
                            <div class="accordion-header">
                                <span><?php echo htmlspecialchars($m['title']); ?></span>
                                <span>
                                    <a href="?course_id=<?php echo $courseId; ?>&edit_module=<?php echo $m['id']; ?>" class="btn btn-sm btn-ghost">✎</a>
                                    <form method="post" action="<?php echo BASE_URL; ?>/actions/instructor/delete_module.php" style="display:inline;">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="module_id" value="<?php echo $m['id']; ?>">
                                        <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">
                                        <button type="submit" class="btn btn-sm btn-ghost" data-confirm="Delete this module and all its lessons?">🗑</button>
                                    </form>
                                </span>
                            </div>
                            <div class="accordion-body">
                                <?php foreach ($m['lessons'] as $l): ?>
                                    <div style="display:flex; justify-content:space-between; padding: 6px 0; border-bottom: 1px solid #f1f5f9;">
                                        <span>📄 <?php echo htmlspecialchars($l['title']); ?></span>
                                        <span>
                                            <a href="?course_id=<?php echo $courseId; ?>&edit_lesson=<?php echo $l['id']; ?>" class="btn btn-sm btn-ghost">✎</a>
                                            <form method="post" action="<?php echo BASE_URL; ?>/actions/instructor/delete_lesson.php" style="display:inline;">
                                                <?php echo csrfField(); ?>
                                                <input type="hidden" name="lesson_id" value="<?php echo $l['id']; ?>">
                                                <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">
                                                <button type="submit" class="btn btn-sm btn-ghost" data-confirm="Delete this lesson?">🗑</button>
                                            </form>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                                <a href="?course_id=<?php echo $courseId; ?>&add_lesson_to=<?php echo $m['id']; ?>" class="btn btn-sm btn-outline mt-1">+ Add Lesson</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <hr class="mt-2 mb-2">
                <button class="btn btn-primary btn-sm btn-block" onclick="openModal('addModule')">+ Add Module</button>
            </div>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-header">Editor</div>
            <div class="card-body">
                <?php if ($editLesson): ?>
                    <h3>Edit Lesson</h3>
                    <form method="post" enctype="multipart/form-data" action="<?php echo BASE_URL; ?>/actions/instructor/update_lesson.php">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="lesson_id" value="<?php echo (int)$editLesson['id']; ?>">
                        <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">
                        <div class="form-group">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-input" value="<?php echo htmlspecialchars($editLesson['title']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Content (HTML allowed)</label>
                            <textarea name="content" class="form-textarea rich-text" rows="10"><?php echo htmlspecialchars($editLesson['content'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Video URL (YouTube or direct mp4)</label>
                            <input type="text" name="video_url" class="form-input" value="<?php echo htmlspecialchars($editLesson['video_url'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Replace Resource File (optional)</label>
                            <input type="file" name="file" class="form-input" accept=".pdf,.doc,.docx,.ppt,.pptx">
                            <?php if (!empty($editLesson['file_path'])): ?>
                                <small class="form-help">Current: <?php echo htmlspecialchars($editLesson['file_path']); ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Duration (min)</label>
                                <input type="number" name="duration" class="form-input" value="<?php echo htmlspecialchars($editLesson['duration'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" class="form-input" value="<?php echo (int)$editLesson['sort_order']; ?>">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Lesson</button>
                        <a href="?course_id=<?php echo $courseId; ?>" class="btn btn-ghost">Cancel</a>
                    </form>
                <?php elseif (isset($_GET['add_lesson_to'])): ?>
                    <h3>Add Lesson</h3>
                    <?php
                    $mid = (int)$_GET['add_lesson_to'];
                    $stmt = $pdo->prepare("SELECT * FROM modules WHERE id = ? AND course_id = ?");
                    $stmt->execute([$mid, $courseId]);
                    $module = $stmt->fetch();
                    ?>
                    <?php if ($module): ?>
                        <form method="post" enctype="multipart/form-data" action="<?php echo BASE_URL; ?>/actions/instructor/create_lesson.php">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="module_id" value="<?php echo $mid; ?>">
                            <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">
                            <div class="form-group">
                                <label class="form-label">Title *</label>
                                <input type="text" name="title" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Content (HTML allowed)</label>
                                <textarea name="content" class="form-textarea rich-text" rows="10"></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Video URL</label>
                                <input type="text" name="video_url" class="form-input" placeholder="YouTube or mp4 URL">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Resource File (optional, max 20MB)</label>
                                <input type="file" name="file" class="form-input" accept=".pdf,.doc,.docx,.ppt,.pptx">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Duration (min)</label>
                                    <input type="number" name="duration" class="form-input" value="10">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Sort Order</label>
                                    <input type="number" name="sort_order" class="form-input" value="1">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Lesson</button>
                            <a href="?course_id=<?php echo $courseId; ?>" class="btn btn-ghost">Cancel</a>
                        </form>
                    <?php endif; ?>
                <?php elseif ($editModule): ?>
                    <h3>Edit Module</h3>
                    <form method="post" action="<?php echo BASE_URL; ?>/actions/instructor/update_module.php">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="module_id" value="<?php echo (int)$editModule['id']; ?>">
                        <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">
                        <div class="form-group">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-input" value="<?php echo htmlspecialchars($editModule['title']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-textarea"><?php echo htmlspecialchars($editModule['description'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-input" value="<?php echo (int)$editModule['sort_order']; ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Save</button>
                        <a href="?course_id=<?php echo $courseId; ?>" class="btn btn-ghost">Cancel</a>
                    </form>
                <?php else: ?>
                    <div class="text-center text-muted" style="padding: 40px;">
                        <p>Select a module or lesson to edit, or add a new one.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h4>Course Settings</h4>
                <form method="post" action="<?php echo BASE_URL; ?>/actions/instructor/publish_course.php">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">
                    <p>Status: <strong><?php echo $course['is_published'] ? 'Published' : 'Draft'; ?></strong></p>
                    <button type="submit" class="btn btn-<?php echo $course['is_published'] ? 'warning' : 'success'; ?>">
                        <?php echo $course['is_published'] ? 'Unpublish' : 'Publish'; ?>
                    </button>
                    <a href="<?php echo BASE_URL; ?>/instructor/quiz_builder.php?course_id=<?php echo $courseId; ?>" class="btn btn-outline">Build Quiz</a>
                    <a href="<?php echo BASE_URL; ?>/instructor/assignments.php?course_id=<?php echo $courseId; ?>" class="btn btn-outline">Manage Assignments</a>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="addModule" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <strong>Add Module</strong>
            <button class="modal-close">&times;</button>
        </div>
        <form method="post" action="<?php echo BASE_URL; ?>/actions/instructor/create_module.php">
            <?php echo csrfField(); ?>
            <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-input" value="<?php echo count($modules)+1; ?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('addModule')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
