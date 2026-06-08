<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/mail.php';
require_once __DIR__ . '/includes/functions.php';

echo "<h2>Mail Debug</h2><pre>";

echo "BREVO_API_KEY: " . (defined('BREVO_API_KEY') && BREVO_API_KEY !== '' ? '[SET]' : '[NOT SET]') . "\n";
echo "MAIL_USER: '" . (defined('MAIL_USER') ? MAIL_USER : 'N/A') . "'\n";

$from = defined('MAIL_USER') ? MAIL_USER : 'ilesanmierioluwavictor@gmail.com';

echo "\n--- Test: Send to self ($from) ---\n";
$err1 = sendEmail($from, 'Test', 'Test from DSPoly', '<p>Self-test email.</p>');
echo $err1 === '' ? "✓ PASSED\n" : "✗ FAILED: $err1\n";

echo "\n--- Try registering a new account ---\n";
echo "Then check the PHP error log below:\n\n";

// Show recent error log entries related to mail
$logFile = ini_get('error_log');
if ($logFile && file_exists($logFile)) {
    $lines = shell_exec("tail -20 " . escapeshellarg($logFile));
    echo "=== Recent PHP error log ===\n";
    echo $lines ?: "(empty)\n";
} else {
    echo "(error_log not found or not accessible)\n";
    // Try common locations
    foreach (['/var/log/apache2/error.log', '/var/log/php_errors.log', __DIR__ . '/../storage/logs/laravel.log'] as $f) {
        if (file_exists($f)) {
            echo "\n--- $f ---\n";
            echo shell_exec("tail -20 " . escapeshellarg($f)) ?: "(empty)\n";
        }
    }
}

echo "\n</pre>";
