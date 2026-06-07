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

// Quizzes
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE course_id = ? ORDER BY created_at DESC");
$stmt->execute([$courseId]);
$quizzes = $stmt->fetchAll();

$activeQuiz = null;
$questions = [];
if (!empty($_GET['quiz_id'])) {
    $qid = (int)$_GET['quiz_id'];
    $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ? AND course_id = ?");
    $stmt->execute([$qid, $courseId]);
    $activeQuiz = $stmt->fetch();
    if ($activeQuiz) {
        $stmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY sort_order");
        $stmt->execute([$qid]);
        $questions = $stmt->fetchAll();
    }
}
if (!$activeQuiz && !empty($quizzes)) $activeQuiz = $quizzes[0];

$pageTitle = 'Quiz Builder';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>📋 Quiz Builder — <?php echo htmlspecialchars($course['title']); ?></h1>
    <a href="<?php echo BASE_URL; ?>/instructor/dashboard.php" class="btn btn-ghost">← Back</a>
</div>

<div class="card mb-3">
    <div class="card-header">Quiz Settings</div>
    <div class="card-body">
        <button class="btn btn-primary btn-sm" onclick="openModal('newQuiz')">+ New Quiz</button>
        <?php if (!empty($quizzes)): ?>
            <div class="tabs mt-2">
                <?php foreach ($quizzes as $q): ?>
                    <a class="tab-item <?php echo $activeQuiz && $activeQuiz['id']==$q['id']?'active':''; ?>" href="?course_id=<?php echo $courseId; ?>&quiz_id=<?php echo (int)$q['id']; ?>">
                        <?php echo htmlspecialchars($q['title']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($activeQuiz): ?>
            <form method="post" action="<?php echo BASE_URL; ?>/actions/instructor/save_quiz.php" class="mt-2">
                <?php echo csrfField(); ?>
                <input type="hidden" name="quiz_id" value="<?php echo (int)$activeQuiz['id']; ?>">
                <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">
                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-input" value="<?php echo htmlspecialchars($activeQuiz['title']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Time Limit (min, blank=none)</label>
                        <input type="number" name="time_limit" class="form-input" value="<?php echo htmlspecialchars($activeQuiz['time_limit'] ?? ''); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea"><?php echo htmlspecialchars($activeQuiz['description'] ?? ''); ?></textarea>
                </div>
                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Max Attempts</label>
                        <input type="number" name="max_attempts" class="form-input" value="<?php echo (int)$activeQuiz['max_attempts']; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pass Mark (%)</label>
                        <input type="number" name="pass_mark" class="form-input" value="<?php echo (int)$activeQuiz['pass_mark']; ?>">
                    </div>
                </div>
                <div class="d-flex gap-1">
                    <button class="btn btn-primary">Save Settings</button>
                </div>
            </form>

            <form method="post" action="<?php echo BASE_URL; ?>/actions/instructor/publish_quiz.php" class="mt-1">
                <?php echo csrfField(); ?>
                <input type="hidden" name="quiz_id" value="<?php echo (int)$activeQuiz['id']; ?>">
                <button class="btn btn-<?php echo $activeQuiz['is_published']?'warning':'success'; ?>">
                    <?php echo $activeQuiz['is_published']?'Unpublish':'Publish'; ?>
                </button>
                <form method="post" action="<?php echo BASE_URL; ?>/actions/instructor/delete_quiz.php" style="display:inline;">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="quiz_id" value="<?php echo (int)$activeQuiz['id']; ?>">
                    <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">
                    <button class="btn btn-danger" data-confirm="Delete this quiz?">Delete Quiz</button>
                </form>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if ($activeQuiz): ?>
    <div class="card">
        <div class="card-header">Questions (<?php echo count($questions); ?>)</div>
        <div class="card-body">
            <button class="btn btn-primary btn-sm" onclick="openModal('newQ')">+ Add Question</button>
            <?php foreach ($questions as $i => $q): ?>
                <div class="card mt-2" style="background:#f8fafc;">
                    <div class="card-body">
                        <p><strong>Q<?php echo $i+1; ?>.</strong> <?php echo htmlspecialchars($q['question_text']); ?></p>
                        <ul style="list-style:none; padding-left: 20px;">
                            <li <?php echo $q['correct_answer']==='A'?'style="color: var(--color-success); font-weight:600;"':''; ?>>A. <?php echo htmlspecialchars($q['option_a']); ?></li>
                            <li <?php echo $q['correct_answer']==='B'?'style="color: var(--color-success); font-weight:600;"':''; ?>>B. <?php echo htmlspecialchars($q['option_b']); ?></li>
                            <li <?php echo $q['correct_answer']==='C'?'style="color: var(--color-success); font-weight:600;"':''; ?>>C. <?php echo htmlspecialchars($q['option_c']); ?></li>
                            <li <?php echo $q['correct_answer']==='D'?'style="color: var(--color-success); font-weight:600;"':''; ?>>D. <?php echo htmlspecialchars($q['option_d']); ?></li>
                        </ul>
                        <form method="post" action="<?php echo BASE_URL; ?>/actions/instructor/delete_question.php" style="display:inline;">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="question_id" value="<?php echo (int)$q['id']; ?>">
                            <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">
                            <input type="hidden" name="quiz_id" value="<?php echo (int)$activeQuiz['id']; ?>">
                            <button class="btn btn-sm btn-danger" data-confirm="Delete this question?">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<div id="newQuiz" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header"><strong>New Quiz</strong><button class="modal-close">&times;</button></div>
        <form method="post" action="<?php echo BASE_URL; ?>/actions/instructor/save_quiz.php">
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
                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Time Limit (min)</label>
                        <input type="number" name="time_limit" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Max Attempts</label>
                        <input type="number" name="max_attempts" class="form-input" value="1">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Pass Mark (%)</label>
                    <input type="number" name="pass_mark" class="form-input" value="50">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('newQuiz')">Cancel</button>
                <button class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>

<?php if ($activeQuiz): ?>
<div id="newQ" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header"><strong>Add Question</strong><button class="modal-close">&times;</button></div>
        <form method="post" action="<?php echo BASE_URL; ?>/actions/instructor/save_questions.php">
            <?php echo csrfField(); ?>
            <input type="hidden" name="quiz_id" value="<?php echo (int)$activeQuiz['id']; ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Question *</label>
                    <textarea name="question_text" class="form-textarea" required></textarea>
                </div>
                <div class="form-group"><label class="form-label">Option A *</label><input type="text" name="option_a" class="form-input" required></div>
                <div class="form-group"><label class="form-label">Option B *</label><input type="text" name="option_b" class="form-input" required></div>
                <div class="form-group"><label class="form-label">Option C *</label><input type="text" name="option_c" class="form-input" required></div>
                <div class="form-group"><label class="form-label">Option D *</label><input type="text" name="option_d" class="form-input" required></div>
                <div class="form-group">
                    <label class="form-label">Correct Answer</label>
                    <select name="correct_answer" class="form-select">
                        <option value="A">A</option><option value="B">B</option><option value="C">C</option><option value="D">D</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Points</label>
                    <input type="number" name="points" class="form-input" value="1">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('newQ')">Cancel</button>
                <button class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
