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
   FACULTIES & DEPARTMENTS & LEVELS
========================================================= */
$pdo->exec("INSERT INTO faculties (name) VALUES ('Faculty of Science') ON CONFLICT (name) DO NOTHING");
$pdo->exec("INSERT INTO faculties (name) VALUES ('Faculty of Engineering') ON CONFLICT (name) DO NOTHING");
$pdo->exec("INSERT INTO faculties (name) VALUES ('Faculty of Management Sciences') ON CONFLICT (name) DO NOTHING");

$stmt = $pdo->query("SELECT id, name FROM faculties");
$facs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
$pdo->prepare("INSERT INTO departments (faculty_id, name) VALUES (?, 'Computer Science') ON CONFLICT (name) DO NOTHING")->execute([$facs['Faculty of Science'] ?? 1]);
$pdo->prepare("INSERT INTO departments (faculty_id, name) VALUES (?, 'Mathematics') ON CONFLICT (name) DO NOTHING")->execute([$facs['Faculty of Science'] ?? 1]);
$pdo->prepare("INSERT INTO departments (faculty_id, name) VALUES (?, 'Electrical Engineering') ON CONFLICT (name) DO NOTHING")->execute([$facs['Faculty of Engineering'] ?? 2]);
$pdo->prepare("INSERT INTO departments (faculty_id, name) VALUES (?, 'Mechanical Engineering') ON CONFLICT (name) DO NOTHING")->execute([$facs['Faculty of Engineering'] ?? 2]);
$pdo->prepare("INSERT INTO departments (faculty_id, name) VALUES (?, 'Business Administration') ON CONFLICT (name) DO NOTHING")->execute([$facs['Faculty of Management Sciences'] ?? 3]);
echo "- Faculties & departments inserted\n";

$pdo->exec("INSERT INTO levels (name) VALUES ('ND1'), ('ND2'), ('HND1'), ('HND2') ON CONFLICT (name) DO NOTHING");
echo "- Levels inserted\n";

$pdo->exec("INSERT INTO semesters (name, sort_order) VALUES ('First', 1), ('Second', 2) ON CONFLICT (name) DO NOTHING");
$pdo->exec("INSERT INTO academic_sessions (name, is_current) VALUES ('2024/2025', TRUE) ON CONFLICT (name) DO NOTHING");
echo "- Semesters & session inserted\n";

/* =========================================================
   USERS
========================================================= */
$users = [
    ['Admin', 'User', 'admin@dspoly.edu.ng', password_hash('Admin@123', PASSWORD_BCRYPT), 'admin', 1, 1, null, null, null],
    ['Dr. Felix', 'Elugwa', 'instructor@dspoly.edu.ng', password_hash('Instructor@123', PASSWORD_BCRYPT), 'instructor', 1, 1, null, null, null],
    ['Demo', 'Student', 'student@dspoly.edu.ng', password_hash('Student@123', PASSWORD_BCRYPT), 'student', 1, 1, null, null, null],
];

foreach ($users as $u) {
    $stmt = $pdo->prepare("
        INSERT INTO users (first_name, last_name, email, password, role, is_active, is_verified, faculty_id, department_id, student_level)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
   GET DEPARTMENT / SEMESTER / SESSION IDS
========================================================= */
$stmt = $pdo->query("SELECT id, name FROM departments WHERE name IN ('Computer Science', 'Mathematics', 'Business Administration')");
$depts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$stmt = $pdo->query("SELECT id, name FROM semesters");
$semesters = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$stmt = $pdo->query("SELECT id FROM academic_sessions WHERE is_current = TRUE");
$sessionId = (int) $stmt->fetchColumn();

/* =========================================================
   COURSES
========================================================= */
$courses = [
    [$instructorId, 'Introduction to Computer Science',
     'A foundational course covering the basics of computing, algorithms, and problem solving.',
     ($depts['Computer Science'] ?? 1), 'ND1', ($semesters['First'] ?? 1), $sessionId, '8 weeks'],

    [$instructorId, 'Web Development with HTML & CSS',
     'Learn to build modern, responsive websites from scratch using HTML5 and CSS3.',
     ($depts['Computer Science'] ?? 1), 'ND2', ($semesters['First'] ?? 1), $sessionId, '6 weeks'],

    [$instructorId, 'Database Management Systems',
     'Understand relational databases, SQL, normalization, and real-world database design.',
     ($depts['Computer Science'] ?? 1), 'HND1', ($semesters['Second'] ?? 2), $sessionId, '10 weeks'],
];

foreach ($courses as $c) {
    $stmt = $pdo->prepare("
        INSERT INTO courses (instructor_id, title, description, department_id, level, semester_id, academic_session_id, duration, is_published)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, TRUE)
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
$currentSessionId = $pdo->query("SELECT id FROM academic_sessions WHERE is_current = TRUE")->fetchColumn();
foreach (array_slice($courseIds, 0, 2) as $courseId) {
    $stmt = $pdo->prepare("
        INSERT INTO enrollments (student_id, course_id, academic_session_id)
        VALUES (?, ?, ?)
        ON CONFLICT (student_id, course_id) DO NOTHING
    ");
    $stmt->execute([$studentId, $courseId, $currentSessionId ?: null]);
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
        VALUES (?, 'Introduction Quiz', 'Test your knowledge of Module 1.', 15, 2, 50, TRUE)
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