<?php

namespace App\Database;

use PDO;
use PDOException;

/**
 * Database Connection Singleton
 * Handles PDO connection with error handling
 */
class Connection
{
    private static ?PDO $instance = null;
    private static array $config = [];

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct()
    {
    }

    /**
     * Prevent cloning
     */
    private function __clone()
    {
    }

    /**
     * Prevent unserialization
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }

    /**
     * Get database configuration
     */
    private static function loadConfig(): array
    {
        if (empty(self::$config)) {
            $configPath = __DIR__ . '/../../config/database.php';
            if (!file_exists($configPath)) {
                throw new \RuntimeException("Database configuration file not found at: {$configPath}");
            }
            self::$config = require $configPath;
        }
        return self::$config;
    }

    /**
     * Get PDO instance (Singleton pattern)
     * 
     * @return PDO
     * @throws PDOException
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = self::loadConfig();
            
            // Build DSN with port if provided
            $port = isset($config['port']) && !empty($config['port']) 
                ? ';port=' . $config['port'] 
                : '';
            
            $dsn = sprintf(
                "mysql:host=%s%s;dbname=%s;charset=%s",
                $config['host'],
                $port,
                $config['dbname'],
                $config['charset']
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                throw new PDOException("Database connection failed. Please check your configuration.", 0, $e);
            }
        }

        return self::$instance;
    }

    /**
     * Reset connection (useful for testing)
     */
    public static function reset(): void
    {
        self::$instance = null;
    }
}
