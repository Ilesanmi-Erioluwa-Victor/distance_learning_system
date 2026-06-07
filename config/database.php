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
            error_log("DB Connection: host=" . DB_HOST . ", dbname=" . DB_NAME . ", user=" . DB_USER);
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                error_log("DB Connection Failed: " . $e->getMessage());
                die('Database connection failed. Please check config/config.php and your .env settings. Error: ' . htmlspecialchars($e->getMessage()));
            }
        }
        return self::$instance;
    }
}
