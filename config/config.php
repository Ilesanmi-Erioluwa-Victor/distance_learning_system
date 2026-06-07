<?php

/**
 * =========================================================
 * SAFE BOOTSTRAP CONFIG (WORKS LOCALLY + INFINITYFREE)
 * =========================================================
 */

// -----------------------------
// Load Composer Autoloader safely
// -----------------------------
$autoloadPath = __DIR__ . '/../vendor/autoload.php';

if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

// -----------------------------
// Load .env safely (ONLY if Dotenv exists)
// -----------------------------
if (class_exists('Dotenv\\Dotenv')) {
    $dotenvPath = __DIR__ . '/../';

    try {
        $dotenv = Dotenv\Dotenv::createImmutable($dotenvPath);
        $dotenv->safeLoad();
    } catch (Exception $e) {
        // ignore if .env fails
    }
}

// -----------------------------
// ENV FALLBACKS (Render + InfinityFree-safe)
// -----------------------------
if (!defined('DB_HOST')) {
    define('DB_HOST', $_ENV['MYSQL_HOST'] ?? $_ENV['DB_HOST'] ?? 'localhost');
}

if (!defined('DB_NAME')) {
    define('DB_NAME', $_ENV['MYSQL_DATABASE'] ?? $_ENV['DB_NAME'] ?? 'wbdls');
}

if (!defined('DB_USER')) {
    define('DB_USER', $_ENV['MYSQL_USER'] ?? $_ENV['DB_USER'] ?? 'root');
}

if (!defined('DB_PASS')) {
    define('DB_PASS', $_ENV['MYSQL_PASSWORD'] ?? $_ENV['DB_PASS'] ?? '');
}

if (!defined('BASE_URL')) {
    define('BASE_URL', $_ENV['RENDER_EXTERNAL_URL'] ?? $_ENV['BASE_URL'] ?? 'http://localhost:8000');
}

if (!defined('MAIL_USER')) {
    define('MAIL_USER', $_ENV['MAIL_USER'] ?? '');
}

if (!defined('MAIL_APP_PASSWORD')) {
    define('MAIL_APP_PASSWORD', $_ENV['MAIL_APP_PASSWORD'] ?? '');
}

// -----------------------------
// APP CONSTANTS
// -----------------------------
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', BASE_URL . '/uploads/');
define('APP_NAME', 'WBDLS — Delta State Polytechnic');
define('SESSION_NAME', 'wbdls_session');

// -----------------------------
// SESSION SECURITY
// -----------------------------
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');

session_name(SESSION_NAME);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// -----------------------------
// SESSION TIMEOUT (30 minutes)
// -----------------------------
$timeout = 30 * 60;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    session_start();
}

$_SESSION['last_activity'] = time();

// -----------------------------
// CSRF TOKEN
// -----------------------------
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}