-- Run this in Supabase SQL Editor after pulling the latest code

-- ====== New tables ======

CREATE TABLE IF NOT EXISTS faculties (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS departments (
    id SERIAL PRIMARY KEY,
    faculty_id INTEGER NOT NULL REFERENCES faculties(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (name)
);

CREATE TABLE IF NOT EXISTS levels (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS semesters (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    sort_order INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS academic_sessions (
    id SERIAL PRIMARY KEY,
    name VARCHAR(20) NOT NULL UNIQUE,
    is_current BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ====== Users ======

ALTER TABLE users ADD COLUMN IF NOT EXISTS faculty_id INTEGER DEFAULT NULL REFERENCES faculties(id) ON DELETE SET NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS department_id INTEGER DEFAULT NULL REFERENCES departments(id) ON DELETE SET NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS student_level VARCHAR(10) DEFAULT NULL;

-- ====== Courses ======

ALTER TABLE courses ADD COLUMN IF NOT EXISTS department_id INTEGER DEFAULT NULL REFERENCES departments(id) ON DELETE SET NULL;
ALTER TABLE courses ADD COLUMN IF NOT EXISTS semester_id INTEGER DEFAULT NULL REFERENCES semesters(id);
ALTER TABLE courses ADD COLUMN IF NOT EXISTS academic_session_id INTEGER DEFAULT NULL REFERENCES academic_sessions(id);

-- ====== Enrollments ======

ALTER TABLE enrollments ADD COLUMN IF NOT EXISTS academic_session_id INTEGER DEFAULT NULL REFERENCES academic_sessions(id);

-- ====== Seed defaults ======

INSERT INTO levels (name) VALUES ('ND1'), ('ND2'), ('HND1'), ('HND2') ON CONFLICT (name) DO NOTHING;

INSERT INTO semesters (name, sort_order) VALUES ('First', 1), ('Second', 2) ON CONFLICT (name) DO NOTHING;

-- ====== Clean up old columns & constraints ======

-- Drop the old CHECK constraint on level (it only allowed Beginner/Intermediate/Advanced)
ALTER TABLE courses DROP CONSTRAINT IF EXISTS courses_level_check;

-- Drop the old category column (replaced by department_id)
ALTER TABLE courses DROP COLUMN IF EXISTS category;
