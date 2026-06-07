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
if (!$course) redirect('/instructor/dashboard.php');

$pageTitle = 'Edit Course';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>✎ Edit Course</h1>
    <a href="<?php echo BASE_URL; ?>/instructor/dashboard.php" class="btn btn-ghost">← Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" action="<?php echo BASE_URL; ?>/actions/instructor/update_course.php">
            <?php echo csrfField(); ?>
            <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">
            <div class="form-group">
                <label class="form-label">Title *</label>
                <input type="text" name="title" class="form-input" value="<?php echo htmlspecialchars($course['title']); ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <option <?php echo $course['category']==='Computer Science'?'selected':''; ?>>Computer Science</option>
                        <option <?php echo $course['category']==='Web Development'?'selected':''; ?>>Web Development</option>
                        <option <?php echo $course['category']==='Database'?'selected':''; ?>>Database</option>
                        <option <?php echo $course['category']==='Networking'?'selected':''; ?>>Networking</option>
                        <option <?php echo $course['category']==='Software Engineering'?'selected':''; ?>>Software Engineering</option>
                        <option <?php echo $course['category']==='Mathematics'?'selected':''; ?>>Mathematics</option>
                        <option <?php echo $course['category']==='General Studies'?'selected':''; ?>>General Studies</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Level</label>
                    <select name="level" class="form-select">
                        <option <?php echo $course['level']==='Beginner'?'selected':''; ?>>Beginner</option>
                        <option <?php echo $course['level']==='Intermediate'?'selected':''; ?>>Intermediate</option>
                        <option <?php echo $course['level']==='Advanced'?'selected':''; ?>>Advanced</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Duration</label>
                <input type="text" name="duration" class="form-input" value="<?php echo htmlspecialchars($course['duration'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-textarea" required><?php echo htmlspecialchars($course['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Replace Thumbnail (optional)</label>
                <input type="file" name="thumbnail" class="form-input" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
