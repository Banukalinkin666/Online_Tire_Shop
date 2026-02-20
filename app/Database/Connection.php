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
        $pdo = self::connect(null);
        // For PostgreSQL, verify connection is alive (handles server-side idle close)
        if (self::$instance !== null && self::isPgsql()) {
            try {
                self::$instance->query('SELECT 1');
            } catch (\Throwable $e) {
                self::reset();
                $pdo = self::connect(null);
            }
        }
        return $pdo;
    }

    private static function isPgsql(): bool
    {
        if (self::parseDatabaseUrl() !== null) {
            return true;
        }
        $config = self::loadConfig();
        $dbType = $_ENV['DB_TYPE'] ?? $_SERVER['DB_TYPE'] ?? $config['type'] ?? 'mysql';
        return $dbType === 'pgsql' || $dbType === 'postgresql';
    }

    /**
     * Parse Render-style DATABASE_URL (postgresql://user:pass@host:port/dbname?sslmode=require)
     * Returns ['dsn' => string, 'username' => string, 'password' => string] or null if not set/invalid
     */
    private static function parseDatabaseUrl(): ?array
    {
        $url = $_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? '';
        if ($url === '' || !preg_match('#^postgres(?:ql)?://#i', $url)) {
            return null;
        }
        $parsed = parse_url($url);
        if (!isset($parsed['host'], $parsed['user'], $parsed['path'])) {
            return null;
        }
        $host = $parsed['host'];
        $port = $parsed['port'] ?? 5432;
        $dbname = ltrim($parsed['path'], '/');
        $username = isset($parsed['user']) ? rawurldecode($parsed['user']) : '';
        $password = isset($parsed['pass']) ? rawurldecode($parsed['pass']) : '';
        $query = isset($parsed['query']) ? $parsed['query'] : '';
        parse_str($query, $params);
        // prefer: try SSL, don't fail if server closes; require: strict SSL (use ?sslmode=require in URL if needed)
        $sslmode = $params['sslmode'] ?? 'prefer';
        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode={$sslmode}";
        return ['dsn' => $dsn, 'username' => $username, 'password' => $password];
    }

    /**
     * Establish connection with optional retry on transient failure (e.g. SSL closed)
     */
    private static function connect(?PDOException $previousError): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $config = self::loadConfig();

        // Prefer DATABASE_URL when set (Render: use Internal Database URL from dashboard)
        $useDatabaseUrl = self::parseDatabaseUrl();
        $dbType = $useDatabaseUrl !== null ? 'pgsql' : ($_ENV['DB_TYPE'] ?? $_SERVER['DB_TYPE'] ?? $config['type'] ?? 'mysql');

        // Build DSN and credentials
        if ($useDatabaseUrl !== null) {
            $dsn = $useDatabaseUrl['dsn'];
            $username = $useDatabaseUrl['username'];
            $password = $useDatabaseUrl['password'];
        } elseif ($dbType === 'pgsql' || $dbType === 'postgresql') {
            // PostgreSQL from config
            $port = isset($config['port']) && !empty($config['port'])
                ? $config['port']
                : '5432';
            $sslmode = $_ENV['DB_SSLMODE'] ?? $config['sslmode'] ?? 'require';
            $dsn = sprintf(
                "pgsql:host=%s;port=%s;dbname=%s;sslmode=%s",
                $config['host'],
                $port,
                $config['dbname'],
                $sslmode
            );
            $username = $config['username'];
            $password = $config['password'];
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
            $username = $config['username'];
            $password = $config['password'];
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

            self::$instance = new PDO($dsn, $username, $password, $options);

            // Set statement timeout for PostgreSQL (if using PostgreSQL)
            if ($dbType === 'pgsql' || $dbType === 'postgresql') {
                self::$instance->exec("SET statement_timeout = 10000");
            } else {
                self::$instance->exec("SET SESSION max_execution_time = 10000");
            }
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());

            // Retry once on transient SSL/connection errors (e.g. Render Postgres)
            $isTransient = (stripos($e->getMessage(), 'SSL') !== false || stripos($e->getMessage(), 'connection') !== false);
            if ($isTransient && $previousError === null) {
                self::$instance = null;
                usleep(200000); // 200ms before retry
                return self::connect($e);
            }

            throw new PDOException("Database connection failed. Please check your configuration.", 0, $e);
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
