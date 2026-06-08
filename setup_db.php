<?php
/**
 * Database Setup Script - Run once via browser:
 *   https://your-site.com/setup_db.php?key=setup123
 * Then DELETE this file immediately.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$SECURE_KEY = "setup123";

if (!isset($_GET['key']) || $_GET['key'] !== $SECURE_KEY) {
    die("Unauthorized access");
}

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$pdo = Database::getConnection();

echo "Setting up database schema...\n";

$sql = file_get_contents(__DIR__ . '/config/schema.sql');
$statements = array_filter(array_map('trim', explode(';', $sql)));

$success = 0;
$failed = 0;

foreach ($statements as $stmt) {
    if (empty($stmt) || str_starts_with($stmt, '--')) continue;
    try {
        $pdo->exec($stmt);
        $success++;
    } catch (PDOException $e) {
        $failed++;
        echo "FAILED: " . $e->getMessage() . "\n";
    }
}

echo "\nSchema: $success created, $failed failed\n";

if ($failed === 0) {
    echo "\nRunning seed...\n";
    include __DIR__ . '/seed.php';
} else {
    echo "\nSome statements failed. Check errors above.\n";
}

echo "\nSetup complete. DELETE this file immediately for security.\n";