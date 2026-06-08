
-- Auto-update trigger for updated_at columns
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- FACULTIES
CREATE TABLE IF NOT EXISTS faculties (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- DEPARTMENTS
CREATE TABLE IF NOT EXISTS departments (
    id SERIAL PRIMARY KEY,
    faculty_id INTEGER NOT NULL REFERENCES faculties(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (name)
);

-- LEVELS
CREATE TABLE IF NOT EXISTS levels (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- SEMESTERS
CREATE TABLE IF NOT EXISTS semesters (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    sort_order INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ACADEMIC SESSIONS
CREATE TABLE IF NOT EXISTS academic_sessions (
    id SERIAL PRIMARY KEY,
    name VARCHAR(20) NOT NULL UNIQUE,
    is_current BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- USERS
CREATE TABLE IF NOT EXISTS users (
    id          SERIAL PRIMARY KEY,
    first_name  VARCHAR(100) NOT NULL,
    last_name   VARCHAR(100) NOT NULL,
    email       VARCHAR(255) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        VARCHAR(20) NOT NULL DEFAULT 'student' CHECK (role IN ('admin','instructor','student')),
    profile_photo VARCHAR(255) DEFAULT NULL,
    bio         TEXT DEFAULT NULL,
    phone       VARCHAR(20) DEFAULT NULL,
    is_active   BOOLEAN NOT NULL DEFAULT TRUE,
    is_verified BOOLEAN NOT NULL DEFAULT FALSE,
    otp_code    VARCHAR(10) DEFAULT NULL,
    otp_expires_at TIMESTAMP DEFAULT NULL,
    faculty_id  INTEGER DEFAULT NULL REFERENCES faculties(id) ON DELETE SET NULL,
    department_id INTEGER DEFAULT NULL REFERENCES departments(id) ON DELETE SET NULL,
    student_level VARCHAR(10) DEFAULT NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

DROP TRIGGER IF EXISTS trg_users_updated_at ON users;
CREATE TRIGGER trg_users_updated_at
    BEFORE UPDATE ON users
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- COURSES
CREATE TABLE IF NOT EXISTS courses (
    id                 SERIAL PRIMARY KEY,
    instructor_id      INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title              VARCHAR(255) NOT NULL,
    description        TEXT NOT NULL,
    thumbnail          VARCHAR(255) DEFAULT NULL,
    level              VARCHAR(10) NOT NULL DEFAULT 'ND1',
    department_id      INTEGER DEFAULT NULL REFERENCES departments(id) ON DELETE SET NULL,
    semester_id        INTEGER NOT NULL REFERENCES semesters(id),
    academic_session_id INTEGER DEFAULT NULL REFERENCES academic_sessions(id),
    duration           VARCHAR(50) DEFAULT NULL,
    is_published       BOOLEAN NOT NULL DEFAULT FALSE,
    created_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

DROP TRIGGER IF EXISTS trg_courses_updated_at ON courses;
CREATE TRIGGER trg_courses_updated_at
    BEFORE UPDATE ON courses
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- MODULES
CREATE TABLE IF NOT EXISTS modules (
    id          SERIAL PRIMARY KEY,
    course_id   INTEGER NOT NULL REFERENCES courses(id) ON DELETE CASCADE,
    title       VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    sort_order  INTEGER NOT NULL DEFAULT 0,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- LESSONS
CREATE TABLE IF NOT EXISTS lessons (
    id          SERIAL PRIMARY KEY,
    module_id   INTEGER NOT NULL REFERENCES modules(id) ON DELETE CASCADE,
    title       VARCHAR(255) NOT NULL,
    content     TEXT DEFAULT NULL,
    video_url   VARCHAR(500) DEFAULT NULL,
    file_path   VARCHAR(255) DEFAULT NULL,
    duration    INTEGER DEFAULT NULL,
    sort_order  INTEGER NOT NULL DEFAULT 0,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ENROLLMENTS
CREATE TABLE IF NOT EXISTS enrollments (
    id                  SERIAL PRIMARY KEY,
    student_id          INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    course_id           INTEGER NOT NULL REFERENCES courses(id) ON DELETE CASCADE,
    status              VARCHAR(20) NOT NULL DEFAULT 'active' CHECK (status IN ('active','completed','dropped')),
    progress            DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    academic_session_id INTEGER DEFAULT NULL REFERENCES academic_sessions(id),
    enrolled_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (student_id, course_id)
);

DROP TRIGGER IF EXISTS trg_enrollments_updated_at ON enrollments;
CREATE TRIGGER trg_enrollments_updated_at
    BEFORE UPDATE ON enrollments
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- LESSON PROGRESS
CREATE TABLE IF NOT EXISTS lesson_progress (
    id           SERIAL PRIMARY KEY,
    student_id   INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    lesson_id    INTEGER NOT NULL REFERENCES lessons(id) ON DELETE CASCADE,
    completed    BOOLEAN NOT NULL DEFAULT FALSE,
    completed_at TIMESTAMP DEFAULT NULL,
    UNIQUE (student_id, lesson_id)
);

-- ASSIGNMENTS
CREATE TABLE IF NOT EXISTS assignments (
    id          SERIAL PRIMARY KEY,
    module_id   INTEGER NOT NULL REFERENCES modules(id) ON DELETE CASCADE,
    title       VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    due_date    TIMESTAMP NOT NULL,
    max_score   INTEGER NOT NULL DEFAULT 100,
    file_path   VARCHAR(255) DEFAULT NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- SUBMISSIONS
CREATE TABLE IF NOT EXISTS submissions (
    id            SERIAL PRIMARY KEY,
    assignment_id INTEGER NOT NULL REFERENCES assignments(id) ON DELETE CASCADE,
    student_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    file_path     VARCHAR(255) DEFAULT NULL,
    text_content  TEXT DEFAULT NULL,
    score         INTEGER DEFAULT NULL,
    feedback      TEXT DEFAULT NULL,
    status        VARCHAR(20) NOT NULL DEFAULT 'submitted' CHECK (status IN ('pending','submitted','graded','late')),
    submitted_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    graded_at     TIMESTAMP DEFAULT NULL,
    UNIQUE (assignment_id, student_id)
);

-- QUIZZES
CREATE TABLE IF NOT EXISTS quizzes (
    id           SERIAL PRIMARY KEY,
    course_id    INTEGER NOT NULL REFERENCES courses(id) ON DELETE CASCADE,
    title        VARCHAR(255) NOT NULL,
    description  TEXT DEFAULT NULL,
    time_limit   INTEGER DEFAULT NULL,
    max_attempts INTEGER NOT NULL DEFAULT 1,
    pass_mark    INTEGER NOT NULL DEFAULT 50,
    is_published BOOLEAN NOT NULL DEFAULT FALSE,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- QUIZ QUESTIONS
CREATE TABLE IF NOT EXISTS quiz_questions (
    id             SERIAL PRIMARY KEY,
    quiz_id        INTEGER NOT NULL REFERENCES quizzes(id) ON DELETE CASCADE,
    question_text  TEXT NOT NULL,
    option_a       VARCHAR(500) NOT NULL,
    option_b       VARCHAR(500) NOT NULL,
    option_c       VARCHAR(500) NOT NULL,
    option_d       VARCHAR(500) NOT NULL,
    correct_answer VARCHAR(1) NOT NULL CHECK (correct_answer IN ('A','B','C','D')),
    points         INTEGER NOT NULL DEFAULT 1,
    sort_order     INTEGER NOT NULL DEFAULT 0
);

-- QUIZ ATTEMPTS
CREATE TABLE IF NOT EXISTS quiz_attempts (
    id           SERIAL PRIMARY KEY,
    quiz_id      INTEGER NOT NULL REFERENCES quizzes(id) ON DELETE CASCADE,
    student_id   INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    answers      TEXT NOT NULL,
    score        INTEGER NOT NULL,
    percentage   DECIMAL(5,2) NOT NULL,
    passed       BOOLEAN NOT NULL,
    started_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP DEFAULT NULL
);

-- ANNOUNCEMENTS
CREATE TABLE IF NOT EXISTS announcements (
    id        SERIAL PRIMARY KEY,
    author_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    course_id INTEGER DEFAULT NULL REFERENCES courses(id) ON DELETE SET NULL,
    title     VARCHAR(255) NOT NULL,
    content   TEXT NOT NULL,
    target    VARCHAR(20) NOT NULL DEFAULT 'all' CHECK (target IN ('all','students','instructors')),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- FORUM POSTS
CREATE TABLE IF NOT EXISTS forum_posts (
    id        SERIAL PRIMARY KEY,
    course_id INTEGER NOT NULL REFERENCES courses(id) ON DELETE CASCADE,
    author_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title     VARCHAR(255) NOT NULL,
    content   TEXT NOT NULL,
    is_pinned BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

DROP TRIGGER IF EXISTS trg_forum_posts_updated_at ON forum_posts;
CREATE TRIGGER trg_forum_posts_updated_at
    BEFORE UPDATE ON forum_posts
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- FORUM REPLIES
CREATE TABLE IF NOT EXISTS forum_replies (
    id        SERIAL PRIMARY KEY,
    post_id   INTEGER NOT NULL REFERENCES forum_posts(id) ON DELETE CASCADE,
    author_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    content   TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- NOTIFICATIONS
CREATE TABLE IF NOT EXISTS notifications (
    id        SERIAL PRIMARY KEY,
    user_id   INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    message   VARCHAR(500) NOT NULL,
    type      VARCHAR(50) NOT NULL,
    link      VARCHAR(255) DEFAULT NULL,
    is_read   BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
