<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('instructor');

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
                    <label class="form-label">Category *</label>
                    <select name="category" class="form-select" required>
                        <option value="">Select...</option>
                        <option>Computer Science</option>
                        <option>Web Development</option>
                        <option>Database</option>
                        <option>Networking</option>
                        <option>Software Engineering</option>
                        <option>Mathematics</option>
                        <option>General Studies</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Level *</label>
                    <select name="level" class="form-select" required>
                        <option>Beginner</option>
                        <option>Intermediate</option>
                        <option>Advanced</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Duration</label>
                <input type="text" name="duration" class="form-input" placeholder="e.g., 6 weeks">
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
