<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/mail.php';
require_once __DIR__ . '/includes/functions.php';

echo "<h2>Mail Debug</h2><pre>";

echo "BREVO_API_KEY: " . (defined('BREVO_API_KEY') && BREVO_API_KEY !== '' ? '[SET]' : '[NOT SET]') . "\n";
echo "MAIL_USER: '" . (defined('MAIL_USER') ? MAIL_USER : 'N/A') . "'\n";
echo "MAIL_APP_PASSWORD: " . (defined('MAIL_APP_PASSWORD') && MAIL_APP_PASSWORD !== '' ? '[SET]' : '[EMPTY]') . "\n\n";

echo "Sending test email to: " . (defined('MAIL_USER') ? MAIL_USER : 'not set') . "\n\n";

$err = sendEmail(
    defined('MAIL_USER') ? MAIL_USER : 'test@example.com',
    'Test',
    'Test from DSPoly',
    '<p>This is a test email.</p>'
);

if ($err === '') {
    echo "✓ SUCCESS: Email sent! Check your inbox.\n";
} else {
    echo "✗ FAILED: $err\n";
}
echo "\n</pre>";
