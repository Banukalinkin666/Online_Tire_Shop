<?php
/**
 * Production Data Import Script
 * Imports comprehensive vehicle fitment data (1000+ vehicles)
 * 
 * Usage: Visit https://your-domain.com/import-production-data.php
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../app/bootstrap.php';

use App\Database\Connection;
use PDOException;

// Simple security: Allow if IMPORT_ALLOWED is set OR if accessed directly (for one-time use)
$importAllowed = $_ENV['IMPORT_ALLOWED'] ?? $_SERVER['IMPORT_ALLOWED'] ?? 'false';
if ($importAllowed !== 'true') {
    $warnDelete = true;
} else {
    $warnDelete = false;
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Import Production Vehicle Data</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 10px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { background: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; font-size: 12px; }
        button { background: #3b82f6; color: white; border: none; padding: 12px 24px; border-radius: 4px; cursor: pointer; font-size: 16px; margin: 10px 5px; }
        button:hover { background: #2563eb; }
        button:disabled { background: #9ca3af; cursor: not-allowed; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
        .stat-box { background: #f8f9fa; padding: 15px; border-radius: 5px; text-align: center; }
        .stat-number { font-size: 32px; font-weight: bold; color: #3b82f6; }
        .stat-label { color: #666; font-size: 14px; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📦 Import Production Vehicle Data</h1>
        <p style="color: #666;">This will import 1000+ vehicle fitments with tire sizes (2010-2024, all major makes)</p>
        
<?php
try {
    $db = Connection::getInstance();
    
    // Detect database type
    $dbType = $_ENV['DB_TYPE'] ?? $_SERVER['DB_TYPE'] ?? 'mysql';
    $isPostgreSQL = ($dbType === 'pgsql' || $dbType === 'postgresql');
    
    echo "<div class='info'><strong>Database Type:</strong> " . ($isPostgreSQL ? 'PostgreSQL' : 'MySQL') . "</div>";
    
    // Check current data count
    try {
        $stmt = $db->query("SELECT COUNT(*) as count FROM vehicle_fitment");
        $currentCount = $stmt->fetch()['count'];
        echo "<div class='info'><strong>Current vehicles in database:</strong> {$currentCount}</div>";
    } catch (Exception $e) {
        echo "<div class='error'>Could not check current data: " . htmlspecialchars($e->getMessage()) . "</div>";
        $currentCount = 0;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import'])) {
        echo "<h2>Importing Data...</h2>";
        
        // Read production data file
        $dataFile = __DIR__ . '/../sql/production_data.sql';
        
        if (!file_exists($dataFile)) {
            throw new Exception("Production data file not found: {$dataFile}");
        }
        
        $sql = file_get_contents($dataFile);
        
        if (empty($sql)) {
            throw new Exception("Production data file is empty");
        }
        
        // Remove comments
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        
        // Split into individual INSERT statements
        // Handle both single and multi-row INSERTs
        $statements = [];
        $currentStatement = '';
        
        $lines = explode("\n", $sql);
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed)) {
                continue;
            }
            
            $currentStatement .= $line . "\n";
            
            // If line ends with semicolon or closing paren, it's a complete statement
            if (substr(rtrim($line), -1) === ';') {
                $stmt = trim($currentStatement);
                if (!empty($stmt) && (stripos($stmt, 'INSERT') === 0 || stripos($stmt, 'DELETE') === 0)) {
                    $statements[] = $stmt;
                }
                $currentStatement = '';
            }
        }
        
        // Add remaining statement if any
        if (!empty(trim($currentStatement))) {
            $stmt = trim($currentStatement);
            if (!empty($stmt) && (stripos($stmt, 'INSERT') === 0 || stripos($stmt, 'DELETE') === 0)) {
                $statements[] = $stmt;
            }
        }
        
        $results = [];
        $successCount = 0;
        $errorCount = 0;
        $skippedCount = 0;
        
        echo "<div class='info'>Found " . count($statements) . " SQL statements to execute...</div>";
        echo "<div style='max-height: 400px; overflow-y: auto; margin: 20px 0;'>";
        
        foreach ($statements as $index => $statement) {
            if (empty(trim($statement))) {
                continue;
            }
            
            try {
                $db->exec($statement);
                
                // Detect what type of statement it was
                $stmtLower = strtolower(trim($statement));
                if (stripos($stmtLower, 'insert') === 0) {
                    preg_match('/insert.*?into.*?(\w+)/i', $statement, $matches);
                    $tableName = $matches[1] ?? 'unknown';
                    
                    // Count rows inserted (approximate)
                    $rowCount = substr_count($statement, '(');
                    $successCount += $rowCount;
                    $results[] = "✅ Inserted into {$tableName} ({$rowCount} rows)";
                } elseif (stripos($stmtLower, 'delete') === 0) {
                    $results[] = "✅ Executed DELETE statement";
                    $successCount++;
                } else {
                    $results[] = "✅ Executed SQL statement";
                    $successCount++;
                }
                
                // Show progress every 50 statements
                if (($index + 1) % 50 === 0) {
                    echo "<div class='info'>Progress: " . ($index + 1) . " / " . count($statements) . " statements processed...</div>";
                }
                
            } catch (PDOException $e) {
                // Check if it's a "duplicate" or "already exists" error (OK to skip)
                $errorMsg = $e->getMessage();
                if (stripos($errorMsg, 'duplicate') !== false || 
                    stripos($errorMsg, 'already exists') !== false ||
                    stripos($errorMsg, 'unique constraint') !== false) {
                    $skippedCount++;
                    // Don't show every duplicate, just count them
                } else {
                    $errorCount++;
                    $results[] = "❌ Error: " . substr($errorMsg, 0, 100);
                }
            }
        }
        
        echo "</div>";
        
        // Show summary
        echo "<h2>Import Results:</h2>";
        echo "<div class='stats'>";
        echo "<div class='stat-box'><div class='stat-number'>{$successCount}</div><div class='stat-label'>Rows Inserted</div></div>";
        echo "<div class='stat-box'><div class='stat-number'>{$skippedCount}</div><div class='stat-label'>Skipped (Duplicates)</div></div>";
        echo "<div class='stat-box'><div class='stat-number'>{$errorCount}</div><div class='stat-label'>Errors</div></div>";
        echo "</div>";
        
        if ($errorCount === 0) {
            echo "<div class='success'>";
            echo "<strong>✅ Import completed successfully!</strong><br>";
            echo "Successfully inserted: {$successCount} vehicle fitments<br>";
            if ($skippedCount > 0) {
                echo "Skipped (already exists): {$skippedCount}<br>";
            }
            echo "</div>";
            
            // Verify final count
            try {
                $stmt = $db->query("SELECT COUNT(*) as count FROM vehicle_fitment");
                $finalCount = $stmt->fetch()['count'];
                
                echo "<div class='info'>";
                echo "<strong>Final Database Status:</strong><br>";
                echo "Total vehicles in database: <strong>{$finalCount}</strong>";
                echo "</div>";
            } catch (Exception $e) {
                echo "<div class='warning'>Could not verify final count: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
            
            if ($warnDelete) {
                echo "<div class='error' style='margin-top: 20px;'><strong>⚠️ SECURITY:</strong> Please delete this file (import-production-data.php) after use for security!</div>";
            }
        } else {
            echo "<div class='error'>";
            echo "<strong>⚠️ Import completed with errors</strong><br>";
            echo "Success: {$successCount} | Skipped: {$skippedCount} | Errors: {$errorCount}";
            echo "</div>";
        }
        
    } else {
        // Show form
        if ($currentCount > 0) {
            echo "<div class='warning'>";
            echo "<strong>⚠️ Note:</strong> You already have {$currentCount} vehicles in the database.<br>";
            echo "Importing will add more vehicles. Duplicate entries will be skipped.";
            echo "</div>";
        }
        
        echo "<form method='POST'>";
        echo "<button type='submit' name='import'>Import Production Data (1000+ Vehicles)</button>";
        echo "</form>";
        
        echo "<div class='info' style='margin-top: 20px;'>";
        echo "<strong>What will be imported:</strong><br>";
        echo "• 1000+ vehicle fitments (2010-2024)<br>";
        echo "• All major makes: Toyota, Honda, Ford, Chevrolet, BMW, Mercedes, etc.<br>";
        echo "• Front and rear tire sizes for each vehicle<br>";
        echo "• Multiple trims per model<br>";
        echo "</div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='error'><strong>Database Error:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
} catch (Exception $e) {
    echo "<div class='error'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

    </div>
</body>
</html>
