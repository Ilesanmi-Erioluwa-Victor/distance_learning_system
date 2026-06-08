<?php
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? APP_NAME;
$extraCss  = $extraCss  ?? [];
$extraJs   = $extraJs   ?? [];
$currentUser = isLoggedIn() ? getCurrentUser() : null;
$unreadNotifs = 0;
if ($currentUser) {
    $unreadNotifs = getUnreadNotificationCount((int) $currentUser['id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo getCsrfToken(); ?>">
    <meta name="base-url" content="<?php echo BASE_URL; ?>">
    <title><?php echo htmlspecialchars($pageTitle . ' — ' . APP_NAME); ?></title>
    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>/assets/images/favicon.svg">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/auth.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/course.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/responsive.css">
    <?php foreach ($extraCss as $css): ?>
        <link rel="stylesheet" href="<?php echo htmlspecialchars($css); ?>">
    <?php endforeach; ?>
</head>
<body class="<?php echo $currentUser ? 'role-' . htmlspecialchars($currentUser['role']) : 'role-public'; ?>">

<?php if ($currentUser): ?>
    <button class="hamburger" id="hamburgerBtn" aria-label="Open menu">
        <span></span><span></span><span></span>
    </button>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="<?php echo BASE_URL; ?>/<?php echo $currentUser['role']; ?>/dashboard.php" class="sidebar-logo">
                <span class="logo-mark">D</span>
                <span class="logo-text">DSPoly</span>
            </a>
        </div>
        <nav class="sidebar-nav">
            <?php if ($currentUser['role'] === 'student'): ?>
                <a href="<?php echo BASE_URL; ?>/student/dashboard.php" class="sidebar-link"><span class="ico">🏠</span> Dashboard</a>
                <a href="<?php echo BASE_URL; ?>/student/courses.php" class="sidebar-link"><span class="ico">📚</span> My Courses</a>
                <a href="<?php echo BASE_URL; ?>/student/assignments.php" class="sidebar-link"><span class="ico">📝</span> Assignments</a>
                <a href="<?php echo BASE_URL; ?>/student/announcements.php" class="sidebar-link"><span class="ico">📢</span> Announcements</a>
                <a href="<?php echo BASE_URL; ?>/student/notifications.php" class="sidebar-link"><span class="ico">🔔</span> Notifications</a>
                <a href="<?php echo BASE_URL; ?>/student/profile.php" class="sidebar-link"><span class="ico">👤</span> Profile</a>
            <?php elseif ($currentUser['role'] === 'instructor'): ?>
                <a href="<?php echo BASE_URL; ?>/instructor/dashboard.php" class="sidebar-link"><span class="ico">🏠</span> Dashboard</a>
                <a href="<?php echo BASE_URL; ?>/instructor/courses.php" class="sidebar-link"><span class="ico">📚</span> My Courses</a>
                <a href="<?php echo BASE_URL; ?>/instructor/create_course.php" class="sidebar-link"><span class="ico">➕</span> New Course</a>
                <a href="<?php echo BASE_URL; ?>/instructor/assignments.php" class="sidebar-link"><span class="ico">📝</span> Assignments</a>
                <a href="<?php echo BASE_URL; ?>/instructor/students.php" class="sidebar-link"><span class="ico">🎓</span> Students</a>
                <a href="<?php echo BASE_URL; ?>/instructor/announcements.php" class="sidebar-link"><span class="ico">📢</span> Announcements</a>
                <a href="<?php echo BASE_URL; ?>/instructor/profile.php" class="sidebar-link"><span class="ico">👤</span> Profile</a>
            <?php elseif ($currentUser['role'] === 'admin'): ?>
                <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="sidebar-link"><span class="ico">🏠</span> Dashboard</a>
                <a href="<?php echo BASE_URL; ?>/admin/users.php" class="sidebar-link"><span class="ico">👥</span> Users</a>
                <a href="<?php echo BASE_URL; ?>/admin/courses.php" class="sidebar-link"><span class="ico">📚</span> Courses</a>
                <a href="<?php echo BASE_URL; ?>/admin/enrollments.php" class="sidebar-link"><span class="ico">📋</span> Enrollments</a>
                <a href="<?php echo BASE_URL; ?>/admin/faculties.php" class="sidebar-link"><span class="ico">🏛️</span> Faculties</a>
                <a href="<?php echo BASE_URL; ?>/admin/departments.php" class="sidebar-link"><span class="ico">📂</span> Departments</a>
                <a href="<?php echo BASE_URL; ?>/admin/levels.php" class="sidebar-link"><span class="ico">📊</span> Levels</a>
                <a href="<?php echo BASE_URL; ?>/admin/semesters.php" class="sidebar-link"><span class="ico">🗓️</span> Semesters</a>
                <a href="<?php echo BASE_URL; ?>/admin/academic_sessions.php" class="sidebar-link"><span class="ico">📅</span> Sessions</a>
                <a href="<?php echo BASE_URL; ?>/admin/announcements.php" class="sidebar-link"><span class="ico">📢</span> Announcements</a>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>/actions/auth/logout.php" class="sidebar-link logout"><span class="ico">🚪</span> Logout</a>
        </nav>
    </aside>
<?php else: ?>
    <header class="public-header">
        <div class="public-nav container">
            <a href="<?php echo BASE_URL; ?>/index.php" class="nav-logo">
                <span class="logo-mark">D</span>
                <span class="logo-text">DSPoly</span>
            </a>
            <nav class="public-nav-links">
                <a href="<?php echo BASE_URL; ?>/index.php">Home</a>
                <a href="<?php echo BASE_URL; ?>/courses.php">Courses</a>
                <a href="<?php echo BASE_URL; ?>/login.php" class="btn btn-outline btn-sm">Login</a>
                <a href="<?php echo BASE_URL; ?>/register.php" class="btn btn-primary btn-sm">Register</a>
            </nav>
        </div>
    </header>
<?php endif; ?>

<main class="main-content <?php echo $currentUser ? 'with-sidebar' : 'public'; ?>">
    <div class="container">
        <?php renderFlash(); ?>
