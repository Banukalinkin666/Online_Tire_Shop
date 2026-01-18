<?php
/**
 * Setup Verification Script
 * Run this file to verify your installation is correct
 * 
 * Usage: php setup-verify.php
 */

echo "=== Tire Fitment Application Setup Verification ===\n\n";

$errors = [];
$warnings = [];

// Check PHP version
echo "Checking PHP version...\n";
if (version_compare(PHP_VERSION, '8.2.0', '>=')) {
    echo "✓ PHP " . PHP_VERSION . " (OK)\n";
} else {
    $errors[] = "PHP 8.2+ required. Current version: " . PHP_VERSION;
    echo "✗ PHP " . PHP_VERSION . " (REQUIRES 8.2+)\n";
}

// Check required extensions
echo "\nChecking PHP extensions...\n";
$requiredExtensions = ['pdo', 'pdo_mysql', 'curl', 'json'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✓ $ext extension loaded\n";
    } else {
        $errors[] = "Required extension missing: $ext";
        echo "✗ $ext extension NOT loaded\n";
    }
}

// Check file structure
echo "\nChecking file structure...\n";
$requiredFiles = [
    'app/bootstrap.php',
    'app/Database/Connection.php',
    'app/Models/VehicleFitment.php',
    'app/Models/Tire.php',
    'app/Services/NHTSAService.php',
    'app/Services/TireMatchService.php',
    'api/vin.php',
    'api/ymm.php',
    'api/tires.php',
    'config/database.php',
    'public/index.php',
    'public/assets/js/app.js',
    'sql/schema.sql'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "✓ $file exists\n";
    } else {
        $errors[] = "Required file missing: $file";
        echo "✗ $file NOT FOUND\n";
    }
}

// Check database configuration
echo "\nChecking database configuration...\n";
if (file_exists('config/database.php')) {
    try {
        $config = require 'config/database.php';
        if (isset($config['host']) && isset($config['dbname'])) {
            echo "✓ Database config file exists\n";
            echo "  Host: " . $config['host'] . "\n";
            echo "  Database: " . $config['dbname'] . "\n";
        } else {
            $warnings[] = "Database config may be incomplete";
            echo "⚠ Database config file exists but may be incomplete\n";
        }
    } catch (Exception $e) {
        $warnings[] = "Could not read database config: " . $e->getMessage();
        echo "⚠ Could not read database config\n";
    }
}

// Test database connection
echo "\nTesting database connection...\n";
if (file_exists('config/database.php')) {
    try {
        require_once 'app/bootstrap.php';
        $db = App\Database\Connection::getInstance();
        echo "✓ Database connection successful\n";
        
        // Check if tables exist
        $stmt = $db->query("SHOW TABLES LIKE 'vehicle_fitment'");
        if ($stmt->rowCount() > 0) {
            echo "✓ vehicle_fitment table exists\n";
        } else {
            $warnings[] = "vehicle_fitment table not found. Run sql/schema.sql";
            echo "⚠ vehicle_fitment table NOT FOUND (run sql/schema.sql)\n";
        }
        
        $stmt = $db->query("SHOW TABLES LIKE 'tires'");
        if ($stmt->rowCount() > 0) {
            echo "✓ tires table exists\n";
            
            // Check sample data
            $stmt = $db->query("SELECT COUNT(*) as count FROM tires");
            $count = $stmt->fetch()['count'];
            if ($count > 0) {
                echo "✓ Found $count tires in database\n";
            } else {
                $warnings[] = "No tires in database. Run sql/schema.sql to insert sample data";
                echo "⚠ No tires found in database\n";
            }
        } else {
            $warnings[] = "tires table not found. Run sql/schema.sql";
            echo "⚠ tires table NOT FOUND (run sql/schema.sql)\n";
        }
        
    } catch (Exception $e) {
        $errors[] = "Database connection failed: " . $e->getMessage();
        echo "✗ Database connection FAILED: " . $e->getMessage() . "\n";
        echo "  Check config/database.php credentials\n";
    }
} else {
    $errors[] = "Database config file not found";
}

// Check permissions
echo "\nChecking file permissions...\n";
$writableDirs = [];
foreach ($writableDirs as $dir) {
    if (file_exists($dir) && is_writable($dir)) {
        echo "✓ $dir is writable\n";
    } else {
        $warnings[] = "$dir may need write permissions";
        echo "⚠ $dir may not be writable\n";
    }
}

// Summary
echo "\n=== Verification Summary ===\n";
if (empty($errors) && empty($warnings)) {
    echo "✓ All checks passed! Your installation looks good.\n";
    exit(0);
} else {
    if (!empty($errors)) {
        echo "\n✗ ERRORS FOUND:\n";
        foreach ($errors as $error) {
            echo "  - $error\n";
        }
    }
    if (!empty($warnings)) {
        echo "\n⚠ WARNINGS:\n";
        foreach ($warnings as $warning) {
            echo "  - $warning\n";
        }
    }
    exit(1);
}
