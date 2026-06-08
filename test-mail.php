<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/mail.php';
require_once __DIR__ . '/includes/functions.php';

echo "<h2>Mail Debug</h2>";
echo "<pre>";

echo "MAIL_USER value: '" . (defined('MAIL_USER') ? MAIL_USER : 'N/A') . "'\n";
echo "MAIL_APP_PASSWORD: " . (defined('MAIL_APP_PASSWORD') && MAIL_APP_PASSWORD !== '' ? '[SET]' : '[EMPTY]') . "\n\n";

echo "--- Network tests ---\n";
$host = 'smtp.gmail.com';
$ports = [25, 465, 587];
foreach ($ports as $port) {
    $errno = 0; $errstr = '';
    $fp = @fsockopen($host, $port, $errno, $errstr, 5);
    if ($fp) {
        echo "Port $port: CONNECTED ✓\n";
        fclose($fp);
    } else {
        echo "Port $port: FAILED ($errno) $errstr\n";
    }
}

echo "\n--- DNS test ---\n";
$ips = gethostbynamel($host);
echo $host . " -> " . ($ips ? implode(', ', $ips) : 'DNS FAILED') . "\n";

echo "\n--- PHPMailer test ---\n";
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
