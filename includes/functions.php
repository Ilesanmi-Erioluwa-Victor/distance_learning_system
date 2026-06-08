<?php
require_once __DIR__ . '/../config/database.php';

function sanitize(string $input): string
{
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function generateOTP(int $length = 6): string
{
    return str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function formatDate(string $dateString, string $format = 'M j, Y'): string
{
    if (empty($dateString)) return '';
    return date($format, strtotime($dateString));
}

function timeAgo(string $dateString): string
{
    if (empty($dateString)) return '';
    $diff = time() - strtotime($dateString);
    if ($diff < 60)         return 'Just now';
    if ($diff < 3600)       return floor($diff / 60) . 'm ago';
    if ($diff < 86400)      return floor($diff / 3600) . 'h ago';
    if ($diff < 604800)     return floor($diff / 86400) . 'd ago';
    return formatDate($dateString);
}

function createNotification(int $userId, string $message, string $type, string $link = ''): void
{
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, type, link) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $message, $type, $link]);
}

function getUnreadNotificationCount(int $userId): int
{
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND NOT is_read");
    $stmt->execute([$userId]);
    return (int) $stmt->fetchColumn();
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function renderFlash(): void
{
    $flash = getFlash();
    if ($flash) {
        echo '<div class="alert alert-' . htmlspecialchars($flash['type']) . '">'
           . htmlspecialchars($flash['message']) . '</div>';
    }
}

function getCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . getCsrfToken() . '">';
}

function validateCsrf(): void
{
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        http_response_code(403);
        die('CSRF validation failed.');
    }
}

function calculateCourseProgress(int $studentId, int $courseId): float
{
    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("
        SELECT COUNT(l.id)
        FROM lessons l
        JOIN modules m ON l.module_id = m.id
        WHERE m.course_id = ?
    ");
    $stmt->execute([$courseId]);
    $total = (int) $stmt->fetchColumn();
    if ($total === 0) return 0.0;

    $stmt = $pdo->prepare("
        SELECT COUNT(lp.id)
        FROM lesson_progress lp
        JOIN lessons l ON lp.lesson_id = l.id
        JOIN modules m ON l.module_id = m.id
        WHERE m.course_id = ? AND lp.student_id = ? AND lp.completed
    ");
    $stmt->execute([$courseId, $studentId]);
    $completed = (int) $stmt->fetchColumn();

    return round(($completed / $total) * 100, 2);
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function getInitials(string $firstName, string $lastName): string
{
    return strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
}

function uploadFile(array $file, string $subdir, array $allowedMimes, int $maxSize = 5242880): array
{
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload failed.'];
    }
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'File exceeds maximum size.'];
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, $allowedMimes, true)) {
        return ['success' => false, 'error' => 'Invalid file type.'];
    }
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newName = bin2hex(random_bytes(16)) . '.' . strtolower($ext);
    $dir = UPLOAD_DIR . $subdir . '/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $dest = $dir . $newName;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return ['success' => false, 'error' => 'Could not save file.'];
    }
    return ['success' => true, 'path' => $subdir . '/' . $newName, 'url' => UPLOAD_URL . $subdir . '/' . $newName];
}

function youtubeEmbedUrl(string $url): ?string
{
    if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/)([A-Za-z0-9_\-]{6,15})~', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    return null;
}
