<?php
/**
 * Application Bootstrap
 * 
 * Autoloader and configuration setup
 * WordPress-compatible: No output unless executed
 */

// Prevent direct access
if (!defined('TIRESHOP_BOOTSTRAP_LOADED')) {
    define('TIRESHOP_BOOTSTRAP_LOADED', true);
}

// Load .env from project root (so API and all entry points see QUOTE_*, GEMINI_*, DB_*, etc.)
$envFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
if (file_exists($envFile) && is_readable($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            if ($key !== '') {
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

// Sync getenv() into $_ENV/$_SERVER for consistency (Render sets real env vars)
if (function_exists('getenv')) {
    $envVars = ['GEMINI_API_KEY', 'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_PORT', 'DB_TYPE', 'DATABASE_URL', 'QUOTE_NOTIFICATION_EMAIL', 'QUOTE_MAIL_FROM'];
    foreach ($envVars as $var) {
        $value = getenv($var);
        if ($value !== false && $value !== '') {
            $_ENV[$var] = $value;
            $_SERVER[$var] = $value;
        }
    }
}

// Set error reporting (adjust for production)
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Register autoloader
spl_autoload_register(function ($className) {
    // Remove leading backslash if present
    $className = ltrim($className, '\\');
    
    // Check if this is our namespace
    if (strpos($className, 'App\\') !== 0) {
        return;
    }
    
    // Convert namespace to file path
    $className = str_replace('App\\', '', $className);
    $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);
    
    // Build full path
    $filePath = __DIR__ . DIRECTORY_SEPARATOR . $className . '.php';
    
    if (file_exists($filePath)) {
        require_once $filePath;
    }
});
