<?php
/**
 * Database Configuration
 * 
 * Reads from environment variables for production (Render, etc.)
 * Falls back to local development values
 * Supports both MySQL and PostgreSQL
 */

return [
    'type' => $_ENV['DB_TYPE'] ?? $_SERVER['DB_TYPE'] ?? 'mysql', // 'mysql' or 'pgsql'
    'host' => $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? 'localhost',
    'dbname' => $_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? 'tire_shop',
    'username' => $_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASS'] ?? $_SERVER['DB_PASS'] ?? '',
    'port' => $_ENV['DB_PORT'] ?? $_SERVER['DB_PORT'] ?? '3306',
    'charset' => 'utf8mb4', // Only used for MySQL
    'sslmode' => $_ENV['DB_SSLMODE'] ?? $_SERVER['DB_SSLMODE'] ?? 'require', // For PostgreSQL (e.g. Render): require, prefer, disable
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
