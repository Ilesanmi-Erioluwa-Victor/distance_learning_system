<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('student');

$quizId = (int)($_GET['quiz_id'] ?? 0);
if ($quizId <= 0) redirect('/student/dashboard.php');

$pdo = Database::getConnection();
$uid = (int) getCurrentUser()['id'];

// Fetch quiz
$stmt = $pdo->prepare("
    SELECT q.*, c.title as course_title, c.id as course_id
    FROM quizzes q JOIN courses c ON q.course_id = c.id
    WHERE q.id = ? AND q.is_published
");
$stmt->execute([$quizId]);
$quiz = $stmt->fetch();
if (!$quiz) { setFlash('error', 'Quiz not found.'); redirect('/student/dashboard.php'); }

// Verify enrollment
$stmt = $pdo->prepare("SELECT 1 FROM enrollments WHERE student_id = ? AND course_id = ?");
$stmt->execute([$uid, $quiz['course_id']]);
if (!$stmt->fetch()) { setFlash('error', 'You are not enrolled in this course.'); redirect('/student/courses.php'); }

// Attempts
$stmt = $pdo->prepare("SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = ? AND student_id = ?");
$stmt->execute([$quizId, $uid]);
$attemptsUsed = (int) $stmt->fetchColumn();
$maxAttempts = (int)$quiz['max_attempts'];

if ($attemptsUsed >= $maxAttempts) {
    $stmt = $pdo->prepare("SELECT id FROM quiz_attempts WHERE quiz_id = ? AND student_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$quizId, $uid]);
    $lastId = $stmt->fetchColumn();
    redirect('/student/quiz_result.php?attempt_id=' . (int)$lastId);
}

// Start?
$started = isset($_GET['start']) && $_GET['start'] == '1';

$pageTitle = $quiz['title'];
include __DIR__ . '/../includes/header.php';
?>

<?php if (!$started): ?>
    <div class="card" style="max-width:600px; margin: 30px auto;">
        <div class="card-body text-center">
            <h1><?php echo htmlspecialchars($quiz['title']); ?></h1>
            <p class="text-muted"><?php echo htmlspecialchars($quiz['description'] ?? ''); ?></p>
            <p><span class="badge badge-info">Course: <?php echo htmlspecialchars($quiz['course_title']); ?></span></p>
            <ul style="list-style:none; padding:0; text-align:left; max-width:300px; margin: 20px auto;">
                <li>⏱ Time Limit: <?php echo $quiz['time_limit'] ? $quiz['time_limit'].' minutes' : 'None'; ?></li>
                <li>🎯 Pass Mark: <?php echo (int)$quiz['pass_mark']; ?>%</li>
                <li>🔁 Attempts: <?php echo $attemptsUsed; ?> / <?php echo $maxAttempts; ?></li>
            </ul>
            <a href="?quiz_id=<?php echo $quizId; ?>&start=1" class="btn btn-primary btn-lg">Start Quiz</a>
        </div>
    </div>
<?php else: ?>
    <?php
    $stmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY sort_order");
    $stmt->execute([$quizId]);
    $questions = $stmt->fetchAll();
    ?>
    <div class="quiz-header">
        <div><strong><?php echo htmlspecialchars($quiz['title']); ?></strong> · <span id="questionCounter">Question 1 of <?php echo count($questions); ?></span></div>
        <?php if ($quiz['time_limit']): ?>
            <div class="timer" id="quizTimer">--:--</div>
        <?php endif; ?>
    </div>

    <form id="quizForm" method="post" action="<?php echo BASE_URL; ?>/actions/student/submit_quiz.php">
        <?php echo csrfField(); ?>
        <input type="hidden" name="quiz_id" value="<?php echo $quizId; ?>">
        <input type="hidden" name="answers" id="answersJson">
        <div id="quizContainer">
            <?php foreach ($questions as $i => $q): ?>
                <div class="question-slide <?php echo $i === 0 ? 'active' : ''; ?>" data-q="<?php echo $i; ?>">
                    <div class="quiz-question">
                        <div class="question-text"><?php echo ($i+1) . '. ' . nl2br(htmlspecialchars($q['question_text'])); ?></div>
                        <?php foreach (['A','B','C','D'] as $opt):
                            $val = $q['option_' . strtolower($opt)];
                        ?>
                            <label class="quiz-option" data-name="q<?php echo $q['id']; ?>" data-value="<?php echo $opt; ?>">
                                <input type="radio" name="q<?php echo $q['id']; ?>" value="<?php echo $opt; ?>" required>
                                <strong><?php echo $opt; ?>.</strong> <?php echo htmlspecialchars($val); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="quiz-nav">
            <button type="button" class="btn btn-ghost" id="prevBtn">← Previous</button>
            <button type="button" class="btn btn-primary" id="nextBtn">Next →</button>
            <button type="submit" class="btn btn-success" id="submitQuizBtn" style="display:none;">Submit Quiz</button>
        </div>
    </form>

    <script>
    document.getElementById('quizForm').addEventListener('submit', function() {
        const answers = {};
        document.querySelectorAll('.question-slide').forEach(slide => {
            const sel = slide.querySelector('.quiz-option.selected');
            if (sel) {
                const name = sel.dataset.name; // e.g., q123
                const qid = name.replace('q','');
                answers[qid] = sel.dataset.value;
            }
        });
        document.getElementById('answersJson').value = JSON.stringify(answers);
    });
    <?php if ($quiz['time_limit']): ?>
        const timeLimit = <?php echo (int)$quiz['time_limit']; ?>;
    <?php else: ?>
        const timeLimit = 0;
    <?php endif; ?>
    </script>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
