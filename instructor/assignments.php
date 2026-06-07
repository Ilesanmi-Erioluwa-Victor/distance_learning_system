<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('instructor');

$pdo = Database::getConnection();
$uid = (int) getCurrentUser()['id'];

// My courses for filter
$stmt = $pdo->prepare("SELECT * FROM courses WHERE instructor_id = ? ORDER BY title");
$stmt->execute([$uid]);
$myCourses = $stmt->fetchAll();

$courseFilter = (int)($_GET['course_id'] ?? 0);
$moduleFilter = (int)($_GET['module_id'] ?? 0);

$where = "c.instructor_id = ?";
$params = [$uid];
if ($courseFilter) { $where .= " AND c.id = ?"; $params[] = $courseFilter; }
if ($moduleFilter) { $where .= " AND a.module_id = ?"; $params[] = $moduleFilter; }

$stmt = $pdo->prepare("
    SELECT a.*, m.title as module_title, c.title as course_title, c.id as course_id,
           (SELECT COUNT(*) FROM submissions s WHERE s.assignment_id = a.id) as submission_count
    FROM assignments a
    JOIN modules m ON a.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    WHERE $where
    ORDER BY a.due_date DESC
");
$stmt->execute($params);
$assignments = $stmt->fetchAll();

$pageTitle = 'Assignments';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>📝 Assignments</h1>
    <button class="btn btn-primary" onclick="openModal('addAssignment')">+ New Assignment</button>
</div>

<form method="get" class="card mb-3" style="padding: 16px;">
    <div class="grid grid-2">
        <select name="course_id" class="form-select" onchange="this.form.submit()">
            <option value="0">All Courses</option>
            <?php foreach ($myCourses as $c): ?>
                <option value="<?php echo $c['id']; ?>" <?php echo $courseFilter===(int)$c['id']?'selected':''; ?>>
                    <?php echo htmlspecialchars($c['title']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if ($courseFilter):
            $stmt = $pdo->prepare("SELECT * FROM modules WHERE course_id = ? ORDER BY sort_order");
            $stmt->execute([$courseFilter]);
            $mods = $stmt->fetchAll();
        ?>
            <select name="module_id" class="form-select" onchange="this.form.submit()">
                <option value="0">All Modules</option>
                <?php foreach ($mods as $m): ?>
                    <option value="<?php echo $m['id']; ?>" <?php echo $moduleFilter===(int)$m['id']?'selected':''; ?>>
                        <?php echo htmlspecialchars($m['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
    </div>
</form>

<?php if (empty($assignments)): ?>
    <div class="empty-state">
        <div class="icon">📝</div>
        <h3>No assignments yet</h3>
    </div>
<?php else: ?>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr><th>Title</th><th>Course</th><th>Module</th><th>Due</th><th>Submissions</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($assignments as $a): ?>
                <tr>
                    <td><?php echo htmlspecialchars($a['title']); ?></td>
                    <td><?php echo htmlspecialchars($a['course_title']); ?></td>
                    <td><?php echo htmlspecialchars($a['module_title']); ?></td>
                    <td><?php echo formatDate($a['due_date']); ?></td>
                    <td><?php echo (int)$a['submission_count']; ?></td>
                    <td class="actions">
                        <a href="<?php echo BASE_URL; ?>/instructor/grade_submissions.php?assignment_id=<?php echo (int)$a['id']; ?>" class="btn btn-sm btn-primary">Grade</a>
                        <form method="post" action="<?php echo BASE_URL; ?>/actions/instructor/delete_assignment.php" style="display:inline;">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="assignment_id" value="<?php echo (int)$a['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger" data-confirm="Delete this assignment?">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<div id="addAssignment" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <strong>New Assignment</strong>
            <button class="modal-close">&times;</button>
        </div>
        <form method="post" enctype="multipart/form-data" action="<?php echo BASE_URL; ?>/actions/instructor/create_assignment.php">
            <?php echo csrfField(); ?>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Course</label>
                    <select name="course_id" class="form-select" required>
                        <option value="">Select...</option>
                        <?php foreach ($myCourses as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Module</label>
                    <select name="module_id" class="form-select" required id="moduleSelect">
                        <option value="">Select a course first</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description *</label>
                    <textarea name="description" class="form-textarea" required></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Due Date *</label>
                    <input type="datetime-local" name="due_date" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Max Score</label>
                    <input type="number" name="max_score" class="form-input" value="100">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('addAssignment')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelector('select[name="course_id"]').addEventListener('change', async function() {
    const cid = this.value;
    const sel = document.getElementById('moduleSelect');
    sel.innerHTML = '<option>Loading...</option>';
    if (!cid) { sel.innerHTML = '<option value="">Select a course first</option>'; return; }
    try {
        const r = await fetch('<?php echo BASE_URL; ?>/actions/instructor/get_modules.php?course_id=' + cid);
        const data = await r.json();
        sel.innerHTML = '<option value="">Select...</option>' + data.map(m => '<option value="' + m.id + '">' + m.title + '</option>').join('');
    } catch (e) {
        sel.innerHTML = '<option>Error loading</option>';
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
