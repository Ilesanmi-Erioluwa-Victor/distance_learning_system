<?php
require_once __DIR__ . '/config/config.php';

echo "<h1>Environment Debug</h1>";
echo "<pre>";
echo "RENDER: " . ($_ENV['RENDER'] ?? 'not set') . "\n";
echo "MYSQL_HOST: " . ($_ENV['MYSQL_HOST'] ?? 'not set') . "\n";
echo "MYSQL_DATABASE: " . ($_ENV['MYSQL_DATABASE'] ?? 'not set') . "\n";
echo "MYSQL_USER: " . ($_ENV['MYSQL_USER'] ?? 'not set') . "\n";
echo "MYSQL_PASSWORD: " . (isset($_ENV['MYSQL_PASSWORD']) ? '***set***' : 'not set') . "\n";
echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'not set') . "\n";
echo "DB_NAME: " . ($_ENV['DB_NAME'] ?? 'not set') . "\n";
echo "DB_USER: " . ($_ENV['DB_USER'] ?? 'not set') . "\n";
echo "DB_PASS: " . (isset($_ENV['DB_PASS']) ? '***set***' : 'not set') . "\n";
echo "BASE_URL: " . ($_ENV['BASE_URL'] ?? 'not set') . "\n";
echo "RENDER_EXTERNAL_URL: " . ($_ENV['RENDER_EXTERNAL_URL'] ?? 'not set') . "\n";
echo "\n--- Constants ---\n";
echo "DB_HOST: " . DB_HOST . "\n";
echo "DB_NAME: " . DB_NAME . "\n";
echo "DB_USER: " . DB_USER . "\n";
echo "DB_PASS length: " . strlen(DB_PASS) . "\n";
echo "BASE_URL: " . BASE_URL . "\n";
echo "</pre>";