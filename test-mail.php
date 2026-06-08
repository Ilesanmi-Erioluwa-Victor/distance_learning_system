<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/mail.php';
require_once __DIR__ . '/includes/functions.php';

echo "<h2>Mail Debug</h2>";
echo "<pre>";

echo "MAIL_USER defined: " . (defined('MAIL_USER') ? 'YES' : 'NO') . "\n";
echo "MAIL_USER value: '" . (defined('MAIL_USER') ? MAIL_USER : 'N/A') . "'\n";
echo "MAIL_APP_PASSWORD defined: " . (defined('MAIL_APP_PASSWORD') ? 'YES' : 'NO') . "\n";
echo "MAIL_APP_PASSWORD value: '" . (defined('MAIL_APP_PASSWORD') ? (MAIL_APP_PASSWORD !== '' ? '[SET]' : '[EMPTY]') : 'N/A') . "'\n\n";

echo "Testing PHPMailer...\n";
$err = sendEmail(
    MAIL_USER,
    'Test',
    'Test from DSPoly',
    '<p>This is a test email.</p>'
);

if ($err === '') {
    echo "SUCCESS: Email sent!\n";
} else {
    echo "FAILED: $err\n";
}

echo "\n</pre>";
