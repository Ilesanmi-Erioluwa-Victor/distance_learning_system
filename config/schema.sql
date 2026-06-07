
-- USERS
CREATE TABLE IF NOT EXISTS users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name  VARCHAR(100) NOT NULL,
    last_name   VARCHAR(100) NOT NULL,
    email       VARCHAR(255) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('admin','instructor','student') NOT NULL DEFAULT 'student',
    profile_photo VARCHAR(255) DEFAULT NULL,
    bio         TEXT DEFAULT NULL,
    phone       VARCHAR(20) DEFAULT NULL,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    otp_code    VARCHAR(10) DEFAULT NULL,
    otp_expires_at DATETIME DEFAULT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- COURSES
CREATE TABLE IF NOT EXISTS courses (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    instructor_id INT UNSIGNED NOT NULL,
    title        VARCHAR(255) NOT NULL,
    description  TEXT NOT NULL,
    thumbnail    VARCHAR(255) DEFAULT NULL,
    level        ENUM('Beginner','Intermediate','Advanced') NOT NULL DEFAULT 'Beginner',
    category     VARCHAR(100) NOT NULL,
    duration     VARCHAR(50) DEFAULT NULL,
    is_published TINYINT(1) NOT NULL DEFAULT 0,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- MODULES
CREATE TABLE IF NOT EXISTS modules (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id   INT UNSIGNED NOT NULL,
    title       VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- LESSONS
CREATE TABLE IF NOT EXISTS lessons (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_id   INT UNSIGNED NOT NULL,
    title       VARCHAR(255) NOT NULL,
    content     LONGTEXT DEFAULT NULL,
    video_url   VARCHAR(500) DEFAULT NULL,
    file_path   VARCHAR(255) DEFAULT NULL,
    duration    INT DEFAULT NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ENROLLMENTS
CREATE TABLE IF NOT EXISTS enrollments (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id  INT UNSIGNED NOT NULL,
    course_id   INT UNSIGNED NOT NULL,
    status      ENUM('active','completed','dropped') NOT NULL DEFAULT 'active',
    progress    DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    enrolled_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_enrollment (student_id, course_id),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id)  REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- LESSON PROGRESS
CREATE TABLE IF NOT EXISTS lesson_progress (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id   INT UNSIGNED NOT NULL,
    lesson_id    INT UNSIGNED NOT NULL,
    completed    TINYINT(1) NOT NULL DEFAULT 0,
    completed_at DATETIME DEFAULT NULL,
    UNIQUE KEY uq_progress (student_id, lesson_id),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id)  REFERENCES lessons(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ASSIGNMENTS
CREATE TABLE IF NOT EXISTS assignments (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_id   INT UNSIGNED NOT NULL,
    title       VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    due_date    DATETIME NOT NULL,
    max_score   INT NOT NULL DEFAULT 100,
    file_path   VARCHAR(255) DEFAULT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- SUBMISSIONS
CREATE TABLE IF NOT EXISTS submissions (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT UNSIGNED NOT NULL,
    student_id    INT UNSIGNED NOT NULL,
    file_path     VARCHAR(255) DEFAULT NULL,
    text_content  TEXT DEFAULT NULL,
    score         INT DEFAULT NULL,
    feedback      TEXT DEFAULT NULL,
    status        ENUM('pending','submitted','graded','late') NOT NULL DEFAULT 'submitted',
    submitted_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    graded_at     DATETIME DEFAULT NULL,
    UNIQUE KEY uq_submission (assignment_id, student_id),
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id)    REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- QUIZZES
CREATE TABLE IF NOT EXISTS quizzes (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id    INT UNSIGNED NOT NULL,
    title        VARCHAR(255) NOT NULL,
    description  TEXT DEFAULT NULL,
    time_limit   INT DEFAULT NULL,
    max_attempts INT NOT NULL DEFAULT 1,
    pass_mark    INT NOT NULL DEFAULT 50,
    is_published TINYINT(1) NOT NULL DEFAULT 0,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- QUIZ QUESTIONS
CREATE TABLE IF NOT EXISTS quiz_questions (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quiz_id        INT UNSIGNED NOT NULL,
    question_text  TEXT NOT NULL,
    option_a       VARCHAR(500) NOT NULL,
    option_b       VARCHAR(500) NOT NULL,
    option_c       VARCHAR(500) NOT NULL,
    option_d       VARCHAR(500) NOT NULL,
    correct_answer ENUM('A','B','C','D') NOT NULL,
    points         INT NOT NULL DEFAULT 1,
    sort_order     INT NOT NULL DEFAULT 0,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- QUIZ ATTEMPTS
CREATE TABLE IF NOT EXISTS quiz_attempts (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quiz_id      INT UNSIGNED NOT NULL,
    student_id   INT UNSIGNED NOT NULL,
    answers      JSON NOT NULL,
    score        INT NOT NULL,
    percentage   DECIMAL(5,2) NOT NULL,
    passed       TINYINT(1) NOT NULL,
    started_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME DEFAULT NULL,
    FOREIGN KEY (quiz_id)    REFERENCES quizzes(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ANNOUNCEMENTS
CREATE TABLE IF NOT EXISTS announcements (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    author_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED DEFAULT NULL,
    title     VARCHAR(255) NOT NULL,
    content   TEXT NOT NULL,
    target    ENUM('all','students','instructors') NOT NULL DEFAULT 'all',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- FORUM POSTS
CREATE TABLE IF NOT EXISTS forum_posts (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id INT UNSIGNED NOT NULL,
    author_id INT UNSIGNED NOT NULL,
    title     VARCHAR(255) NOT NULL,
    content   TEXT NOT NULL,
    is_pinned TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- FORUM REPLIES
CREATE TABLE IF NOT EXISTS forum_replies (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id   INT UNSIGNED NOT NULL,
    author_id INT UNSIGNED NOT NULL,
    content   TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id)   REFERENCES forum_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- NOTIFICATIONS
CREATE TABLE IF NOT EXISTS notifications (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id   INT UNSIGNED NOT NULL,
    message   VARCHAR(500) NOT NULL,
    type      VARCHAR(50) NOT NULL,
    link      VARCHAR(255) DEFAULT NULL,
    is_read   TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
