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
            
            // Determine database type from environment or default to MySQL
            $dbType = $_ENV['DB_TYPE'] ?? $_SERVER['DB_TYPE'] ?? $config['type'] ?? 'mysql';
            
            // Build DSN based on database type
            if ($dbType === 'pgsql' || $dbType === 'postgresql') {
                // PostgreSQL connection
                $port = isset($config['port']) && !empty($config['port']) 
                    ? $config['port'] 
                    : '5432';
                
                $dsn = sprintf(
                    "pgsql:host=%s;port=%s;dbname=%s",
                    $config['host'],
                    $port,
                    $config['dbname']
                );
            } else {
                // MySQL connection (default)
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
            }

            try {
                // Get user-provided options or use defaults
                $userOptions = $config['options'] ?? [];
                
                // Set default options if not already provided by user
                $options = [
                    PDO::ATTR_ERRMODE => $userOptions[PDO::ATTR_ERRMODE] ?? PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => $userOptions[PDO::ATTR_DEFAULT_FETCH_MODE] ?? PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => $userOptions[PDO::ATTR_EMULATE_PREPARES] ?? false,
                ];
                
                // Add any other user-provided options (merge remaining options)
                foreach ($userOptions as $key => $value) {
                    if (!isset($options[$key])) {
                        $options[$key] = $value;
                    }
                }
                
                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $options
                );
                
                // Set statement timeout for PostgreSQL (if using PostgreSQL)
                if ($dbType === 'pgsql' || $dbType === 'postgresql') {
                    // Set statement timeout to 10 seconds
                    self::$instance->exec("SET statement_timeout = 10000"); // 10 seconds in milliseconds
                } else {
                    // For MySQL, set query timeout via connection attribute
                    // Note: MySQL doesn't support per-query timeout via PDO, but we can set it globally
                    self::$instance->exec("SET SESSION max_execution_time = 10000"); // 10 seconds in milliseconds
                }
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
