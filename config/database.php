<?php
require_once __DIR__ . '/config.php';

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            // Visible debug - remove after fixing
            echo "<pre style='background:#fff3cd;border:1px solid #ffc107;padding:10px;margin:10px;color:#856404'>";
            echo "DB DEBUG: host=" . DB_HOST . "\n";
            echo "DB DEBUG: dbname=" . DB_NAME . "\n";
            echo "DB DEBUG: user=" . DB_USER . "\n";
            echo "DB DEBUG: pass length=" . strlen(DB_PASS) . "\n";
            echo "DB DEBUG: RENDER=" . ($_ENV['RENDER'] ?? 'not set') . "\n";
            echo "DB DEBUG: MYSQL_HOST=" . ($_ENV['MYSQL_HOST'] ?? 'not set') . "\n";
            echo "</pre>";
            
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                echo "<pre style='background:#f8d7da;border:1px solid #f5c6cb;padding:10px;margin:10px;color:#721c24'>";
                echo "DB Connection Failed: " . $e->getMessage();
                echo "</pre>";
                die('Database connection failed. Please check config/config.php and your .env settings. Error: ' . htmlspecialchars($e->getMessage()));
            }
        }
        return self::$instance;
    }
}
