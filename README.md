# Web-Based Distance Learning System (WBDLS)

### Case Study: Delta State Polytechnic, Otefe-Oghara

A complete distance learning platform built with **HTML, CSS, PHP 8.1+ and MySQL**.
The system allows instructors and students to participate in distance learning
activities while geographically separated — the internet is the sole delivery medium.

---

## Tech Stack (as required by project abstract)

| Layer        | Technology                                    |
|--------------|-----------------------------------------------|
| Frontend     | HTML5, CSS3 (hand-written, no framework)     |
| Backend      | PHP 8.1+ (procedural with OOP for DB class)   |
| Database     | MySQL 8.0+ with PDO + prepared statements     |
| Email        | PHPMailer (Gmail SMTP)                        |
| Environment  | vlucas/phpdotenv                              |
| JavaScript   | Vanilla JS (no jQuery/React/Vue)              |

---

## Features

- **3 user roles** — Admin, Instructor, Student — each with a dedicated dashboard
- **Email verification** with 6-digit OTP via PHPMailer + Gmail SMTP
- **Password reset** flow with OTP
- **Course catalog** (public) + course detail pages
- **Enrollments** with progress tracking (auto-recalculated as lessons complete)
- **Module / lesson management** for instructors (text content + YouTube or mp4 + downloadable resources)
- **Quizzes** with auto-grading, timer, max attempts and pass mark
- **Assignments** with file upload + grading
- **Forum / discussions** per course
- **Announcements** (platform-wide or per-course)
- **Notifications** for every key event
- **Admin panel** — manage users, courses, enrollments
- **Mobile responsive** — CSS Grid + media queries (breakpoints: 1024/768/480)
- **Security** — CSRF tokens on every POST, bcrypt password hashing, MIME-validated file uploads, prepared statements, XSS escaping, session hardening, .htaccess blocks in `/uploads/`

---

## Folder Structure

```
wbdls/
├── config/        database, config, mail, schema.sql, seed.php
├── includes/      auth, functions, header, footer
├── assets/        css/, js/, images/
├── uploads/       profiles/, assignments/, resources/, thumbnails/
├── actions/
│   ├── auth/      register, login, logout, OTP, password reset
│   ├── student/   enroll, complete_lesson, submit_assignment, submit_quiz
│   ├── instructor/ create/update/delete course, module, lesson, assignment, quiz
│   ├── admin/     toggle/delete users, courses, announcements
│   ├── user/      profile, photo, change password
│   ├── forum/     posts, replies
│   └── notifications/  mark read
├── student/       dashboard, courses, learn, assignments, quiz, forum, profile…
├── instructor/    dashboard, courses, course_builder, assignments, quiz_builder…
├── admin/         dashboard, users, courses, enrollments, announcements
├── index.php, courses.php, course_detail.php
├── login.php, register.php, verify_email.php, forgot/reset_password.php
├── composer.json, .env.example, .htaccess, README.md
```

---

## Requirements

- PHP **8.1+** with extensions: `pdo`, `pdo_mysql`, `mbstring`, `fileinfo`, `openssl`
- MySQL **8.0+** (or MariaDB 10.5+)
- Composer (https://getcomposer.org)
- A Gmail account + App Password (for OTP email sending)

---

## Free Online Services You Will Need (No Local Install)

Because we are not installing PHP/MySQL locally, the project runs on a free
remote stack. You will need:

| Service                | What it does                        | Free option                                |
|------------------------|-------------------------------------|--------------------------------------------|
| PHP + MySQL hosting    | Runs the app                        | **InfinityFree.net** (free, recommended), 000webhost.com, Render.com |
| Remote MySQL (alt)     | DB only                             | db4free.net, freesqldatabase.com           |

> Most free PHP hosts (like InfinityFree) give you MySQL in their control panel.
> You will not need a separate MySQL host unless you choose a free PHP host that
> doesn't include a database (like some Render free tier setups).

---

## LOCAL SETUP (Quick — for your own computer testing)

> If you only have a remote hosting plan, **skip to "DEPLOY TO FREE HOSTING" below**.

1. **Install PHP 8.1+ and MySQL** (or use XAMPP / Laragon / MAMP — all free).
2. **Clone / unzip this project** into your web root (e.g., `htdocs/wbdls` for XAMPP).
3. **Create a database** called `wbdls` in phpMyAdmin.
4. **Import the schema**: open phpMyAdmin → wbdls → Import → choose `config/schema.sql` → Go.
5. **Install dependencies** — open a terminal in the project folder and run:
   ```bash
   composer install
   ```
6. **Create your `.env`** from the example:
   ```bash
   cp .env.example .env
   ```
   Edit `.env` and fill in your database credentials and Gmail App Password.
7. **Seed demo data** (run once):
   ```bash
   php config/seed.php
   ```
8. **Start the dev server**:
   ```bash
   php -S localhost:8000
   ```
9. Open `http://localhost:8000` in your browser.

---

## DEPLOY TO FREE HOSTING (RECOMMENDED — no local install)

### Option A — InfinityFree (Free PHP + MySQL Hosting)

1. Go to **https://www.infinityfree.com** → Sign up → Create a free hosting account.
   - Pick any free subdomain (e.g., `wbdls.epizy.com`) or connect your own domain.
2. After account creation, open **cPanel** (the "Control Panel" link).
3. **Create a MySQL database**:
   - Click "MySQL Databases".
   - Note the hostname, database name, username, password.
4. **Import the schema**:
   - Open **phpMyAdmin** (from cPanel).
   - Select your new database → **Import** tab → choose `config/schema.sql` → **Go**.
5. **Upload the project**:
   - Open **File Manager** (or use FileZilla with the FTP credentials from cPanel).
   - Upload everything **inside** `htdocs/` (or `public_html/`):
     - All `.php` files
     - All folders: `config/`, `includes/`, `assets/`, `uploads/`, `actions/`, `student/`, `instructor/`, `admin/`
     - `.htaccess` (the root one)
   - Do **NOT** upload `.env` yet.
6. **Install Composer dependencies on the server**:
   - InfinityFree has no SSH. So, on your computer:
     ```bash
     cd /path/to/this/project
     composer install --no-dev
     ```
   - Upload the generated `vendor/` folder via FTP into your web root.
7. **Create `.env` on the server**:
   - In File Manager, click "New File" → name it `.env`.
   - Edit it and fill in the values:
     ```
     DB_HOST=sqlXXX.infinityfree.com
     DB_NAME=epiz_XXXXX_wbdls
     DB_USER=epiz_XXXXX
     DB_PASS=your_db_password
     BASE_URL=https://yoursubdomain.infinityfree.com
     MAIL_USER=your.gmail@gmail.com
     MAIL_APP_PASSWORD=your_16char_app_password
     ```
     (Replace the placeholders with the values InfinityFree showed you in cPanel.)
8. **Run the seed script** (creates demo users/courses):
   - Easiest: temporarily move `config/seed.php` to the project root, then visit
     `https://yoursite.com/seed.php` in your browser. You should see "Seed complete."
   - **Then DELETE `seed.php` from the server immediately** (security).
9. Visit your site URL and log in with the demo credentials (below).

### Option B — Hostinger (Paid, Recommended for reliability — ~$3/month)

1. Buy a Hostinger plan. cPanel is auto-configured.
2. Create a MySQL database in cPanel.
3. Upload all project files via File Manager or FTP into `public_html/`.
4. Import `config/schema.sql` via phpMyAdmin.
5. SSH in (Hostinger provides SSH) and run:
   ```bash
   cd public_html
   composer install
   php config/seed.php
   ```
6. Create `.env` in File Manager with your DB credentials.
7. Visit your site.

### Option C — Render (Free, with Docker)

> Best for tech-savvy users. Render free tier can be slow to spin up.

1. Push this project to a GitHub repo.
2. On Render, create a **New Web Service** → connect the repo.
3. Render auto-detects the `Dockerfile` (we'll add one if needed).
4. Add environment variables in the Render dashboard.
5. For the database, use **db4free.net** (free remote MySQL) or **Clever Cloud MySQL** free tier.

---

## Gmail App Password Setup (for OTP emails)

The system uses Gmail SMTP to send verification codes.

1. Go to https://myaccount.google.com/security
2. Enable **2-Step Verification** (you must do this first).
3. After enabling, go back to Security → **App passwords**.
4. Choose "Mail" + "Other (Custom name)" → type "WBDLS" → Generate.
5. Google will show a **16-character password**. Copy it.
6. Put it in your `.env` as `MAIL_APP_PASSWORD`.
7. Put your full Gmail address in `.env` as `MAIL_USER`.

> **If you don't set up Gmail App Password**, the system will still work — the OTP
> will be shown on screen after registration (development mode).

---

## Default Credentials (after running seed.php)

| Role       | Email                        | Password         |
|------------|------------------------------|------------------|
| Admin      | admin@dspoly.edu.ng          | `Admin@123`      |
| Instructor | instructor@dspoly.edu.ng     | `Instructor@123` |
| Student    | student@dspoly.edu.ng        | `Student@123`    |

---

## Security Notes

- All POST forms include a CSRF token (`<?php echo csrfField(); ?>`).
- Every SQL query uses PDO prepared statements — never string interpolation.
- Passwords are hashed with `password_hash(..., PASSWORD_BCRYPT, ['cost' => 12])`.
- File uploads are validated with `finfo_file()` (real MIME) and renamed to random UUIDs.
- `/uploads/.htaccess` blocks PHP execution in the upload directory.
- `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy` headers are set.
- Session cookies are `HttpOnly`, `SameSite=Lax`, with 30-min idle timeout.

---

## Quick Test Checklist

After deployment, verify:

- [ ] Home page loads at `https://yoursite.com/`
- [ ] Register a new account → receive OTP email (or see it on screen)
- [ ] Verify OTP → log in
- [ ] Browse catalog, enroll in a course, watch a lesson, mark it complete
- [ ] Take a quiz and see results
- [ ] As instructor: create a course, add a module + lesson, publish
- [ ] As admin: toggle a user, view enrollments

---

## License

Final Year Project — Delta State Polytechnic, Otefe-Oghara. For academic use.
