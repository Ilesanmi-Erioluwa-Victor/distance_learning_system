<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('student');

$pdo = Database::getConnection();
$uid = (int) getCurrentUser()['id'];

$stmt = $pdo->prepare("
    SELECT a.*, c.title as course_title, m.title as module_title, s.id as submission_id, s.status as submission_status, s.score
    FROM assignments a
    JOIN modules m ON a.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    JOIN enrollments e ON e.course_id = c.id
    LEFT JOIN submissions s ON s.assignment_id = a.id AND s.student_id = e.student_id
    WHERE e.student_id = ?
    ORDER BY a.due_date DESC
");
$stmt->execute([$uid]);
$rows = $stmt->fetchAll();

$pageTitle = 'Assignments';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>📝 Assignments</h1>
</div>

<?php if (empty($rows)): ?>
    <div class="empty-state">
        <div class="icon">📝</div>
        <h3>No assignments yet</h3>
        <p>Your instructors haven't posted any assignments.</p>
    </div>
<?php else: ?>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Course</th>
                    <th>Module</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Score</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $a):
                $isPast = strtotime($a['due_date']) < time();
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($a['title']); ?></td>
                    <td><?php echo htmlspecialchars($a['course_title']); ?></td>
                    <td><?php echo htmlspecialchars($a['module_title']); ?></td>
                    <td><?php echo formatDate($a['due_date'], 'M j, Y'); ?></td>
                    <td>
                        <?php if ($a['submission_status'] === 'graded'): ?>
                            <span class="badge badge-success">Graded</span>
                        <?php elseif ($a['submission_status'] === 'submitted'): ?>
                            <span class="badge badge-info">Submitted</span>
                        <?php elseif ($isPast): ?>
                            <span class="badge badge-danger">Late</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Pending</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $a['score'] !== null ? (int)$a['score'] . '/' . (int)$a['max_score'] : '—'; ?></td>
                    <td>
                        <?php if (!$a['submission_id'] && !$isPast): ?>
                            <button class="btn btn-primary btn-sm" onclick="openModal('submit-<?php echo (int)$a['id']; ?>')">Submit</button>
                            <div id="submit-<?php echo (int)$a['id']; ?>" class="modal-overlay">
                                <div class="modal-box">
                                    <div class="modal-header">
                                        <strong>Submit: <?php echo htmlspecialchars($a['title']); ?></strong>
                                        <button class="modal-close">&times;</button>
                                    </div>
                                    <form method="post" enctype="multipart/form-data" action="<?php echo BASE_URL; ?>/actions/student/submit_assignment.php">
                                        <?php echo csrfField(); ?>
                                        <div class="modal-body">
                                            <input type="hidden" name="assignment_id" value="<?php echo (int)$a['id']; ?>">
                                            <div class="form-group">
                                                <label class="form-label">Text Submission (optional)</label>
                                                <textarea name="text_content" class="form-textarea" placeholder="Type your answer here..."></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Attach File (optional, max 10MB)</label>
                                                <input type="file" name="submission_file" class="form-input" accept=".pdf,.doc,.docx,.zip">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-ghost" onclick="closeModal('submit-<?php echo (int)$a['id']; ?>')">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Submit</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
