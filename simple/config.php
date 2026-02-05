<?php
/**
 * Simple Configuration File
 * Free-only VIN lookup application
 */

// Database configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? 'vin_lookup');
define('DB_USER', $_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? $_SERVER['DB_PASS'] ?? '');
define('DB_CHARSET', 'utf8mb4');

// NHTSA API endpoint
define('NHTSA_API_URL', 'https://vpic.nhtsa.dot.gov/api/vehicles/DecodeVinValues/');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Timezone
date_default_timezone_set('UTC');
