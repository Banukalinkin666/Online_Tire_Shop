<?php
/**
 * One-time Database Schema Import Script
 * 
 * Access this file once via browser to import the schema:
 * https://your-site.onrender.com/import-schema.php
 * 
 * DELETE THIS FILE AFTER IMPORTING FOR SECURITY!
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Database\Connection;
use PDO;

// Security: Only allow in development or with a secret key
$allowed = false;

// Option 1: Check for secret key in URL
$secretKey = $_GET['key'] ?? '';
$expectedKey = $_ENV['IMPORT_SECRET'] ?? $_SERVER['IMPORT_SECRET'] ?? 'change-this-secret-key';

// Option 2: Allow only if IMPORT_ALLOWED is set
$importAllowed = $_ENV['IMPORT_ALLOWED'] ?? $_SERVER['IMPORT_ALLOWED'] ?? 'false';

if ($secretKey === $expectedKey || $importAllowed === 'true') {
    $allowed = true;
}

if (!$allowed) {
    http_response_code(403);
    die('Access denied. Set IMPORT_SECRET in environment variables or IMPORT_ALLOWED=true');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Schema Import</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 { color: #333; }
        .success { color: #10b981; background: #d1fae5; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .error { color: #ef4444; background: #fee2e2; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .info { color: #3b82f6; background: #dbeafe; padding: 10px; border-radius: 4px; margin: 10px 0; }
        pre { background: #f3f4f6; padding: 15px; border-radius: 4px; overflow-x: auto; }
        button {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px 5px;
        }
        button:hover { background: #2563eb; }
        button:disabled { background: #9ca3af; cursor: not-allowed; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗄️ Database Schema Import</h1>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import'])) {
            try {
                $db = Connection::getInstance();
                
                // Read PostgreSQL schema file
                $schemaFile = __DIR__ . '/../sql/schema_postgresql.sql';
                
                if (!file_exists($schemaFile)) {
                    throw new Exception("Schema file not found: {$schemaFile}");
                }
                
                $sql = file_get_contents($schemaFile);
                
                if (empty($sql)) {
                    throw new Exception("Schema file is empty");
                }
                
                // Split SQL by semicolons, but preserve dollar-quoted strings
                // Remove comments and empty lines
                $sql = preg_replace('/--.*$/m', '', $sql);
                $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
                
                // Handle dollar-quoted strings (PostgreSQL functions)
                // Split by semicolon, but keep multi-line statements together
                $statements = [];
                $currentStatement = '';
                $inDollarQuote = false;
                $dollarTag = '';
                
                $lines = explode("\n", $sql);
                foreach ($lines as $line) {
                    $currentStatement .= $line . "\n";
                    
                    // Check for dollar-quote start/end
                    if (preg_match('/\$(\w*)\$/', $line, $matches)) {
                        if (!$inDollarQuote) {
                            $inDollarQuote = true;
                            $dollarTag = $matches[0];
                        } elseif ($matches[0] === $dollarTag) {
                            $inDollarQuote = false;
                            $dollarTag = '';
                        }
                    }
                    
                    // If not in dollar quote and line ends with semicolon, split
                    if (!$inDollarQuote && strpos(trim($line), ';') !== false) {
                        $stmt = trim($currentStatement);
                        if (!empty($stmt) && !preg_match('/^\s*(CREATE|DROP|INSERT|SELECT|UPDATE|DELETE|ALTER)\s+EXTENSION/i', $stmt)) {
                            $statements[] = $stmt;
                        }
                        $currentStatement = '';
                    }
                }
                
                // Add remaining statement if any
                if (!empty(trim($currentStatement))) {
                    $statements[] = trim($currentStatement);
                }
                
                $statements = array_filter($statements, function($stmt) {
                    return !empty($stmt);
                });
                
                $results = [];
                $successCount = 0;
                $errorCount = 0;
                
                echo '<div class="info">Starting import...</div>';
                
                foreach ($statements as $statement) {
                    if (empty(trim($statement))) {
                        continue;
                    }
                    
                    try {
                        // Execute statement
                        $db->exec($statement);
                        
                        // Detect what type of statement it was
                        $stmtLower = strtolower(trim($statement));
                        if (strpos($stmtLower, 'create table') === 0) {
                            preg_match('/create table.*?(\w+)/i', $statement, $matches);
                            $tableName = $matches[1] ?? 'unknown';
                            $results[] = "✅ Created table: {$tableName}";
                            $successCount++;
                        } elseif (strpos($stmtLower, 'create index') === 0) {
                            preg_match('/create.*?index.*?on.*?(\w+)/i', $statement, $matches);
                            $tableName = $matches[1] ?? 'unknown';
                            $results[] = "✅ Created index on: {$tableName}";
                            $successCount++;
                        } elseif (strpos($stmtLower, 'insert') === 0) {
                            preg_match('/insert.*?into.*?(\w+)/i', $statement, $matches);
                            $tableName = $matches[1] ?? 'unknown';
                            $results[] = "✅ Inserted data into: {$tableName}";
                            $successCount++;
                        } else {
                            $results[] = "✅ Executed SQL statement";
                            $successCount++;
                        }
                    } catch (PDOException $e) {
                        // Check if it's a "already exists" error (OK to ignore)
                        if (strpos($e->getMessage(), 'already exists') !== false) {
                            $results[] = "⚠️ Already exists (skipped)";
                        } else {
                            $results[] = "❌ Error: " . $e->getMessage();
                            $errorCount++;
                        }
                    }
                }
                
                // Verify import
                echo '<h2>Import Results:</h2>';
                echo '<pre>' . implode("\n", $results) . '</pre>';
                
                if ($errorCount === 0) {
                    echo '<div class="success">';
                    echo '<strong>✅ Import completed successfully!</strong><br>';
                    echo "Success: {$successCount} | Errors: {$errorCount}";
                    echo '</div>';
                    
                    // Check if data exists
                    try {
                        $stmt = $db->query("SELECT COUNT(*) as count FROM vehicle_fitment");
                        $vehicleCount = $stmt->fetch()['count'];
                        
                        $stmt = $db->query("SELECT COUNT(*) as count FROM tires");
                        $tireCount = $stmt->fetch()['count'];
                        
                        echo '<div class="info">';
                        echo "<strong>Database Status:</strong><br>";
                        echo "Vehicles: {$vehicleCount} rows<br>";
                        echo "Tires: {$tireCount} rows";
                        echo '</div>';
                    } catch (Exception $e) {
                        echo '<div class="error">Could not verify data: ' . $e->getMessage() . '</div>';
                    }
                    
                    echo '<div class="info">';
                    echo '<strong>⚠️ IMPORTANT:</strong> Delete this file (import-schema.php) for security!';
                    echo '</div>';
                } else {
                    echo '<div class="error">';
                    echo '<strong>⚠️ Import completed with errors</strong><br>';
                    echo "Success: {$successCount} | Errors: {$errorCount}";
                    echo '</div>';
                }
                
            } catch (Exception $e) {
                echo '<div class="error">';
                echo '<strong>❌ Import failed:</strong><br>';
                echo htmlspecialchars($e->getMessage());
                echo '</div>';
            }
        } else {
            // Show form
            try {
                $db = Connection::getInstance();
                
                // Check if tables already exist
                $tablesExist = false;
                try {
                    $stmt = $db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public' AND table_name IN ('vehicle_fitment', 'tires')");
                    $count = $stmt->fetchColumn();
                    $tablesExist = $count >= 2;
                } catch (Exception $e) {
                    // Tables don't exist yet
                }
                
                if ($tablesExist) {
                    // Check if data exists
                    try {
                        $stmt = $db->query("SELECT COUNT(*) as count FROM vehicle_fitment");
                        $vehicleCount = $stmt->fetch()['count'];
                        
                        $stmt = $db->query("SELECT COUNT(*) as count FROM tires");
                        $tireCount = $stmt->fetch()['count'];
                        
                        echo '<div class="info">';
                        echo '<strong>Database already has data:</strong><br>';
                        echo "Vehicles: {$vehicleCount} rows<br>";
                        echo "Tires: {$tireCount} rows<br><br>";
                        echo 'You can re-import if needed (will skip existing data).';
                        echo '</div>';
                    } catch (Exception $e) {
                        echo '<div class="info">Tables exist but could not verify data.</div>';
                    }
                } else {
                    echo '<div class="info">';
                    echo '<strong>Ready to import schema.</strong><br>';
                    echo 'This will create tables and insert sample data.';
                    echo '</div>';
                }
                
                echo '<form method="POST">';
                echo '<button type="submit" name="import">Import Schema</button>';
                echo '</form>';
                
            } catch (Exception $e) {
                echo '<div class="error">';
                echo '<strong>❌ Database connection failed:</strong><br>';
                echo htmlspecialchars($e->getMessage());
                echo '<br><br>Please check your database configuration.';
                echo '</div>';
            }
        }
        ?>
    </div>
</body>
</html>
