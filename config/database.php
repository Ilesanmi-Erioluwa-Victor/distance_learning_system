<?php
require_once __DIR__ . '/config.php';

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = 'pgsql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            
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
