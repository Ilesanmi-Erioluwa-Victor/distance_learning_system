<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/mail.php';
require_once __DIR__ . '/includes/functions.php';

echo "<h2>Mail Debug</h2><pre>";

echo "BREVO_API_KEY: " . (defined('BREVO_API_KEY') && BREVO_API_KEY !== '' ? '[SET]' : '[NOT SET]') . "\n";
echo "MAIL_USER: '" . (defined('MAIL_USER') ? MAIL_USER : 'N/A') . "'\n";
echo "MAIL_APP_PASSWORD: " . (defined('MAIL_APP_PASSWORD') && MAIL_APP_PASSWORD !== '' ? '[SET]' : '[EMPTY]') . "\n\n";

$from = defined('MAIL_USER') ? MAIL_USER : 'ilesanmierioluwavictor@gmail.com';

// Test 1: send to self (same as from address)
echo "--- Test 1: Send to self ($from) ---\n";
$err1 = sendEmail($from, 'Test', 'Test from DSPoly', '<p>Self-test email.</p>');
echo $err1 === '' ? "✓ PASSED\n" : "✗ FAILED: $err1\n";

// Test 2: ask Brevo why it might fail with external addresses
echo "\n--- Test 2: Check sender verification ---\n";
echo "You must verify the sender email in Brevo:\n";
echo "1. Go to https://app.brevo.com/senders/\n";
echo "2. Click 'Add a Sender' or verify \"$from\"\n";
echo "3. Check your Gmail inbox for the verification link\n\n";

echo "After verification, registration OTP emails will work.\n";
echo "\n</pre>";
