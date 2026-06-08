<?php
/**
 * Seed script — populates the database with demo data.
 * Run once via browser:
 *   https://your-site.com/seed.php?key=seed123
 * Then DELETE this file immediately.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* =========================================================
   SECURITY (VERY IMPORTANT)
   Prevent random people from re-seeding your DB
========================================================= */
$SECURE_KEY = "seed123";

if (!isset($_GET['key']) || $_GET['key'] !== $SECURE_KEY) {
    die("Unauthorized access");
}

/* =========================================================
   FIXED PATHS (IMPORTANT FOR INFINITYFREE)
========================================================= */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$pdo = Database::getConnection();

echo "Seeding database...\n";

/* =========================================================
   USERS
========================================================= */
$users = [
    ['Admin', 'User', 'admin@dspoly.edu.ng', password_hash('Admin@123', PASSWORD_BCRYPT), 'admin', 1, 1],
    ['Dr. Felix', 'Elugwa', 'instructor@dspoly.edu.ng', password_hash('Instructor@123', PASSWORD_BCRYPT), 'instructor', 1, 1],
    ['Demo', 'Student', 'student@dspoly.edu.ng', password_hash('Student@123', PASSWORD_BCRYPT), 'student', 1, 1],
];

foreach ($users as $u) {
    $stmt = $pdo->prepare("
        INSERT INTO users (first_name, last_name, email, password, role, is_active, is_verified)
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ON CONFLICT (email) DO NOTHING
    ");
    $stmt->execute($u);
}
echo "- Users inserted\n";

/* =========================================================
   GET USER IDS
========================================================= */
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute(['instructor@dspoly.edu.ng']);
$instructorId = $stmt->fetchColumn();

$stmt->execute(['student@dspoly.edu.ng']);
$studentId = $stmt->fetchColumn();

/* =========================================================
   COURSES
========================================================= */
$courses = [
    [$instructorId, 'Introduction to Computer Science',
     'A foundational course covering the basics of computing, algorithms, and problem solving.',
     'Computer Science', 'Beginner', '8 weeks'],

    [$instructorId, 'Web Development with HTML & CSS',
     'Learn to build modern, responsive websites from scratch using HTML5 and CSS3.',
     'Web Development', 'Beginner', '6 weeks'],

    [$instructorId, 'Database Management Systems',
     'Understand relational databases, SQL, normalization, and real-world database design.',
     'Database', 'Intermediate', '10 weeks'],
];

foreach ($courses as $c) {
    $stmt = $pdo->prepare("
        INSERT INTO courses (instructor_id, title, description, category, level, duration, is_published)
        VALUES (?, ?, ?, ?, ?, ?, 1)
    ");
    $stmt->execute($c);
}
echo "- Courses inserted\n";

/* =========================================================
   FETCH COURSE IDS
========================================================= */
$stmt = $pdo->query("SELECT id FROM courses ORDER BY id LIMIT 3");
$courseIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

/* =========================================================
   MODULES & LESSONS
========================================================= */
foreach ($courseIds as $courseId) {

    $check = $pdo->prepare("SELECT COUNT(*) FROM modules WHERE course_id = ?");
    $check->execute([$courseId]);

    if ($check->fetchColumn() > 0) continue;

    for ($m = 1; $m <= 2; $m++) {
        $stmt = $pdo->prepare("
            INSERT INTO modules (course_id, title, sort_order)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$courseId, "Module $m: Topic Area $m", $m]);

        $moduleId = $pdo->lastInsertId();

        for ($l = 1; $l <= 3; $l++) {
            $stmt = $pdo->prepare("
                INSERT INTO lessons (module_id, title, content, sort_order)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $moduleId,
                "Lesson $l of Module $m",
                "<h3>Lesson $l</h3><p>This is the content for lesson $l in module $m.</p>
                 <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>",
                $l
            ]);
        }
    }
}
echo "- Modules & lessons inserted\n";

/* =========================================================
   ENROLLMENTS
========================================================= */
foreach (array_slice($courseIds, 0, 2) as $courseId) {
    $stmt = $pdo->prepare("
        INSERT INTO enrollments (student_id, course_id)
        VALUES (?, ?)
        ON CONFLICT (student_id, course_id) DO NOTHING
    ");
    $stmt->execute([$studentId, $courseId]);
}
echo "- Enrollments inserted\n";

/* =========================================================
   QUIZ
========================================================= */
$check = $pdo->prepare("SELECT COUNT(*) FROM quizzes WHERE course_id = ?");
$check->execute([$courseIds[0]]);   // FIXED (missing execute earlier)

if ($check->fetchColumn() == 0) {

    $stmt = $pdo->prepare("
        INSERT INTO quizzes (course_id, title, description, time_limit, max_attempts, pass_mark, is_published)
        VALUES (?, 'Introduction Quiz', 'Test your knowledge of Module 1.', 15, 2, 50, 1)
    ");
    $stmt->execute([$courseIds[0]]);
    $quizId = $pdo->lastInsertId();

    $questions = [
        ['What does CPU stand for?', 'Central Processing Unit', 'Computer Power Unit', 'Central Power Unit', 'Control Processing Unit', 'A'],
        ['Which language is used for web structure?', 'CSS', 'HTML', 'PHP', 'SQL', 'B'],
        ['What is an algorithm?', 'A type of computer', 'A set of instructions to solve a problem', 'A programming language', 'A database', 'B'],
        ['What does RAM stand for?', 'Read Access Memory', 'Random Access Memory', 'Remote Access Module', 'Read Allocated Memory', 'B'],
        ['Which of these is a database?', 'HTML', 'CSS', 'MySQL', 'PHP', 'C'],
    ];

    foreach ($questions as $i => $q) {
        $stmt = $pdo->prepare("
            INSERT INTO quiz_questions
            (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_answer, sort_order)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute(array_merge([$quizId], $q, [$i + 1]));
    }

    echo "- Quiz & questions inserted\n";
}

/* =========================================================
   ANNOUNCEMENTS (FIXED execute bug)
========================================================= */
$check = $pdo->prepare("SELECT COUNT(*) FROM announcements");
$check->execute();   // FIXED

if ($check->fetchColumn() == 0) {

    foreach ($courseIds as $courseId) {
        $stmt = $pdo->prepare("
            INSERT INTO announcements (author_id, course_id, title, content, target)
            VALUES (?, ?, 'Welcome to the Course!',
            'Welcome to this course. Please review the course materials and reach out if you need help.',
            'students')
        ");
        $stmt->execute([$instructorId, $courseId]);
    }

    echo "- Announcements inserted\n";
}

echo "\nSeed complete.\n";

echo "Login credentials:\n";
echo "Admin: admin@dspoly.edu.ng / Admin@123\n";
echo "Instructor: instructor@dspoly.edu.ng / Instructor@123\n";
echo "Student: student@dspoly.edu.ng / Student@123\n";