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

// Load environment variables (for Render and local development)
// Render automatically injects env vars, but ensure they're accessible
if (function_exists('getenv')) {
    // Sync getenv() values to $_ENV and $_SERVER for consistency
    $envVars = ['GEMINI_API_KEY', 'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_PORT', 'DB_TYPE'];
    foreach ($envVars as $var) {
        $value = getenv($var);
        if ($value !== false) {
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
