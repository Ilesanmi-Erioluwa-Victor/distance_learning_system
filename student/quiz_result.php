<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('student');

$attemptId = (int)($_GET['attempt_id'] ?? 0);
$uid = (int) getCurrentUser()['id'];

$pdo = Database::getConnection();
$stmt = $pdo->prepare("
    SELECT a.*, q.title as quiz_title, q.pass_mark
    FROM quiz_attempts a
    JOIN quizzes q ON a.quiz_id = q.id
    WHERE a.id = ? AND a.student_id = ?
");
$stmt->execute([$attemptId, $uid]);
$attempt = $stmt->fetch();
if (!$attempt) { setFlash('error', 'Result not found.'); redirect('/student/dashboard.php'); }

$answers = json_decode($attempt['answers'], true) ?: [];

$stmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY sort_order");
$stmt->execute([$attempt['quiz_id']]);
$questions = $stmt->fetchAll();

$pageTitle = 'Quiz Result';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Quiz Result: <?php echo htmlspecialchars($attempt['quiz_title']); ?></h1>
    <a href="<?php echo BASE_URL; ?>/student/dashboard.php" class="btn btn-ghost">← Dashboard</a>
</div>

<div class="text-center">
    <div class="score-circle" style="--p: <?php echo $attempt['percentage']; ?>%">
        <div class="score-value"><?php echo $attempt['percentage']; ?>%</div>
    </div>
    <h2>
        <?php if ($attempt['passed']): ?>
            <span class="badge badge-success">✓ Passed</span>
        <?php else: ?>
            <span class="badge badge-danger">✗ Failed</span>
        <?php endif; ?>
    </h2>
    <p>Score: <?php echo (int)$attempt['score']; ?> points</p>
    <p class="text-muted">Pass mark: <?php echo (int)$attempt['pass_mark']; ?>%</p>
</div>

<h3 class="mt-3 mb-2">Question Review</h3>
<?php foreach ($questions as $i => $q):
    $studentAnswer = $answers[$q['id']] ?? null;
    $isCorrect = $studentAnswer === $q['correct_answer'];
?>
    <div class="answer-review <?php echo $isCorrect ? 'correct' : 'wrong'; ?>">
        <p><strong>Q<?php echo $i+1; ?>.</strong> <?php echo htmlspecialchars($q['question_text']); ?></p>
        <p>Your answer: <strong><?php echo htmlspecialchars($studentAnswer ?? '—'); ?></strong> — <?php echo $isCorrect ? '✓ Correct' : '✗ Wrong'; ?></p>
        <?php if (!$isCorrect): ?>
            <p class="text-muted">Correct: <strong><?php echo htmlspecialchars($q['correct_answer']); ?></strong></p>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
