<?php
/**
 * Migration Script: Add vehicle_cache table
 * Run this once to add the vehicle_cache table to your existing database
 * 
 * Usage: Visit https://your-domain.com/add-vehicle-cache-table.php
 * Or run via command line: php public/add-vehicle-cache-table.php
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../app/bootstrap.php';

use App\Database\Connection;
use PDO;
use PDOException;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Vehicle Cache Table</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Add Vehicle Cache Table Migration</h1>
    
<?php
try {
    $db = Connection::getInstance();
    
    // Detect database type
    $dbType = $_ENV['DB_TYPE'] ?? $_SERVER['DB_TYPE'] ?? 'mysql';
    $isPostgreSQL = ($dbType === 'pgsql' || $dbType === 'postgresql');
    
    echo "<div class='info'><strong>Database Type:</strong> " . ($isPostgreSQL ? 'PostgreSQL' : 'MySQL') . "</div>";
    
    // Check if table already exists
    if ($isPostgreSQL) {
        $checkSql = "SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = 'vehicle_cache'
        )";
    } else {
        $checkSql = "SHOW TABLES LIKE 'vehicle_cache'";
    }
    
    $checkStmt = $db->query($checkSql);
    $tableExists = false;
    
    if ($isPostgreSQL) {
        $tableExists = $checkStmt->fetchColumn();
    } else {
        $tableExists = $checkStmt->rowCount() > 0;
    }
    
    if ($tableExists) {
        echo "<div class='info'><strong>Status:</strong> The vehicle_cache table already exists. No action needed.</div>";
        echo "<p>You can safely delete this file after verification.</p>";
    } else {
        // Create table based on database type
        if ($isPostgreSQL) {
            $createTableSql = "CREATE TABLE IF NOT EXISTS vehicle_cache (
                vin VARCHAR(17) PRIMARY KEY,
                year INTEGER NOT NULL,
                make VARCHAR(100) NOT NULL,
                model VARCHAR(100) NOT NULL,
                trim VARCHAR(150) DEFAULT NULL,
                body_class VARCHAR(100) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            
            $createIndexSql = "CREATE INDEX IF NOT EXISTS idx_cache_year_make_model ON vehicle_cache(year, make, model)";
        } else {
            $createTableSql = "CREATE TABLE IF NOT EXISTS vehicle_cache (
                vin VARCHAR(17) PRIMARY KEY,
                year YEAR NOT NULL,
                make VARCHAR(100) NOT NULL,
                model VARCHAR(100) NOT NULL,
                trim VARCHAR(150) DEFAULT NULL,
                body_class VARCHAR(100) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_cache_year_make_model (year, make, model)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            $createIndexSql = null; // Index created with table in MySQL
        }
        
        echo "<div class='info'><strong>Creating table...</strong></div>";
        echo "<pre>" . htmlspecialchars($createTableSql) . "</pre>";
        
        $db->exec($createTableSql);
        
        if ($createIndexSql) {
            echo "<div class='info'><strong>Creating index...</strong></div>";
            echo "<pre>" . htmlspecialchars($createIndexSql) . "</pre>";
            $db->exec($createIndexSql);
        }
        
        echo "<div class='success'><strong>✓ Success!</strong> The vehicle_cache table has been created successfully.</div>";
        echo "<p>The VIN caching feature is now enabled. You can safely delete this file.</p>";
    }
    
    // Show table structure
    echo "<h2>Table Structure</h2>";
    if ($isPostgreSQL) {
        $descSql = "SELECT column_name, data_type, character_maximum_length, is_nullable, column_default
                    FROM information_schema.columns
                    WHERE table_name = 'vehicle_cache'
                    ORDER BY ordinal_position";
    } else {
        $descSql = "DESCRIBE vehicle_cache";
    }
    
    $descStmt = $db->query($descSql);
    $columns = $descStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='10' cellspacing='0' style='width: 100%; border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'>";
    if ($isPostgreSQL) {
        echo "<th>Column</th><th>Type</th><th>Length</th><th>Nullable</th><th>Default</th>";
    } else {
        echo "<th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th>";
    }
    echo "</tr>";
    
    foreach ($columns as $col) {
        echo "<tr>";
        if ($isPostgreSQL) {
            echo "<td>" . htmlspecialchars($col['column_name']) . "</td>";
            echo "<td>" . htmlspecialchars($col['data_type']) . "</td>";
            echo "<td>" . ($col['character_maximum_length'] ?: '-') . "</td>";
            echo "<td>" . htmlspecialchars($col['is_nullable']) . "</td>";
            echo "<td>" . htmlspecialchars($col['column_default'] ?: '-') . "</td>";
        } else {
            echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Default'] ?: '-') . "</td>";
            echo "<td>" . htmlspecialchars($col['Extra'] ?: '-') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
} catch (PDOException $e) {
    echo "<div class='error'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} catch (Exception $e) {
    echo "<div class='error'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

</body>
</html>
