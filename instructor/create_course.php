<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('instructor');

$pdo = Database::getConnection();
$departments = $pdo->query("
    SELECT d.id, d.name, f.name AS faculty_name
    FROM departments d
    JOIN faculties f ON d.faculty_id = f.id
    ORDER BY f.name, d.name
")->fetchAll();
$levels = $pdo->query("SELECT name FROM levels ORDER BY id")->fetchAll();
$semesters = $pdo->query("SELECT * FROM semesters ORDER BY sort_order, name")->fetchAll();
$sessions = $pdo->query("SELECT * FROM academic_sessions ORDER BY created_at DESC")->fetchAll();
$currentSession = $pdo->query("SELECT id FROM academic_sessions WHERE is_current = TRUE")->fetchColumn();

$pageTitle = 'Create Course';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>➕ Create New Course</h1>
    <a href="<?php echo BASE_URL; ?>/instructor/dashboard.php" class="btn btn-ghost">← Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" action="<?php echo BASE_URL; ?>/actions/instructor/create_course.php">
            <?php echo csrfField(); ?>
            <div class="form-group">
                <label class="form-label">Course Title *</label>
                <input type="text" name="title" class="form-input" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Department *</label>
                    <select name="department_id" class="form-select" required>
                        <option value="">Select...</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?php echo (int)$d['id']; ?>">
                                <?php echo htmlspecialchars($d['faculty_name'] . ' - ' . $d['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Level *</label>
                    <select name="level" class="form-select" required>
                        <option value="">Select...</option>
                        <?php foreach ($levels as $l): ?>
                            <option value="<?php echo htmlspecialchars($l['name']); ?>">
                                <?php echo htmlspecialchars($l['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Semester *</label>
                    <select name="semester_id" class="form-select" required>
                        <option value="">Select...</option>
                        <?php foreach ($semesters as $sem): ?>
                            <option value="<?php echo (int)$sem['id']; ?>">
                                <?php echo htmlspecialchars($sem['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Academic Session *</label>
                    <select name="academic_session_id" class="form-select" required>
                        <option value="">Select...</option>
                        <?php foreach ($sessions as $s): ?>
                            <option value="<?php echo (int)$s['id']; ?>" <?php echo (int)$s['id'] === (int)$currentSession ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($s['name']); ?>
                                <?php if ($s['is_current']): ?> (Current)<?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Duration</label>
                    <input type="text" name="duration" class="form-input" placeholder="e.g., 6 weeks">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Description *</label>
                <textarea name="description" class="form-textarea" required></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Thumbnail (optional, max 3MB)</label>
                <input type="file" name="thumbnail" class="form-input" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary btn-lg">Create Course & Start Building</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
