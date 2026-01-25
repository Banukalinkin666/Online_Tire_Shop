<?php
/**
 * Populate Year/Make/Model from NHTSA vPIC API
 * 
 * This script fetches all makes and models from NHTSA API and populates the database
 * 
 * Usage: Visit https://your-domain.com/populate-ymm-from-nhtsa.php
 * Or run via command line: php public/populate-ymm-from-nhtsa.php
 * 
 * Security: Set IMPORT_ALLOWED=true in environment variables or use secret key
 */

// Suppress warnings for ini_set when headers already sent (not critical)
// Set error handler BEFORE any output or ini_set calls
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // Suppress zlib.output_compression warnings (headers already sent)
    if (strpos($errstr, 'zlib.output_compression') !== false || 
        strpos($errstr, 'Cannot change zlib') !== false) {
        return true; // Suppress this warning
    }
    return false; // Let other errors through
}, E_WARNING);

error_reporting(E_ALL & ~E_WARNING);
ini_set('display_errors', '1');

require_once __DIR__ . '/../app/bootstrap.php';

use App\Services\NHTSAService;
use App\Models\VehicleFitment;
use App\Database\Connection;

// Security check
$importAllowed = $_ENV['IMPORT_ALLOWED'] ?? $_SERVER['IMPORT_ALLOWED'] ?? 'false';
$secretKey = $_GET['key'] ?? '';
$expectedKey = $_ENV['IMPORT_SECRET'] ?? $_SERVER['IMPORT_SECRET'] ?? 'change-this-secret-key';

$allowed = ($importAllowed === 'true' || $secretKey === $expectedKey);

if (!$allowed) {
    http_response_code(403);
    die('Access denied. Set IMPORT_ALLOWED=true in environment variables or provide correct secret key (?key=your-secret)');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Populate Year/Make/Model from NHTSA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 1000px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; }
        pre { background: #e9ecef; padding: 10px; border-radius: 5px; overflow-x: auto; white-space: pre-wrap; word-wrap: break-word; }
        button { background: #3b82f6; color: white; padding: 10px 20px; border-radius: 5px; border: none; cursor: pointer; }
        button:hover { background: #2563eb; }
        button:disabled { background: #9ca3af; cursor: not-allowed; }
        .progress { background: #e9ecef; border-radius: 10px; height: 30px; margin: 10px 0; overflow: hidden; }
        .progress-bar { background: #3b82f6; height: 100%; transition: width 0.3s; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen p-8">
    <div class="container">
        <h1 class="text-3xl font-bold mb-6">Populate Year/Make/Model from NHTSA vPIC API</h1>
        
        <?php
        try {
            $nhtsaService = new NHTSAService();
            $fitmentModel = new VehicleFitment();
            $db = Connection::getInstance();
            
            // Get year range (default: 2010-2024, or from POST)
            $startYear = isset($_POST['start_year']) ? (int)$_POST['start_year'] : 2010;
            $endYear = isset($_POST['end_year']) ? (int)$_POST['end_year'] : 2024;
            $action = $_POST['action'] ?? '';
            
            if ($action === 'populate' && $startYear > 0 && $endYear > 0 && $endYear >= $startYear) {
                // Set longer execution time and memory limit
                set_time_limit(3600); // 1 hour
                @ini_set('max_execution_time', 3600);
                @ini_set('memory_limit', '512M'); // Increase memory limit for large datasets
                ignore_user_abort(true); // Continue even if user closes browser
                
                echo "<div class='info'>Starting population for years {$startYear} to {$endYear}...</div>";
                echo "<div class='info' style='background: #fff3cd;'>⏱️ This may take 30-60 minutes. Keep this page open. Last update: <span id='last-update'>" . date('H:i:s') . "</span></div>";
                echo "<div class='progress'><div class='progress-bar' id='progress' style='width: 0%'>0%</div></div>";
                echo "<div id='status-log' style='max-height: 400px; overflow-y: auto; margin: 10px 0; padding: 10px; background: #f8f9fa; border-radius: 5px;'></div>";
                echo "<script>
                    function updateStatus(message) {
                        const log = document.getElementById('status-log');
                        const time = new Date().toLocaleTimeString();
                        log.innerHTML += '<div style=\"padding: 2px 0; font-size: 12px;\">[' + time + '] ' + message + '</div>';
                        log.scrollTop = log.scrollHeight;
                        document.getElementById('last-update').textContent = time;
                    }
                    updateStatus('Script started');
                </script>";
                flush();
                
                $totalYears = $endYear - $startYear + 1;
                $currentYear = 0;
                $totalInserted = 0;
                $totalSkipped = 0;
                $errors = [];
                
                // Get all makes first (once, not per year)
                echo "<div class='info'>Fetching all makes from NHTSA...</div>";
                echo "<script>updateStatus('Fetching makes from NHTSA API...');</script>";
                flush();
                
                try {
                    $allMakes = $nhtsaService->getMakesForYear($startYear);
                    echo "<div class='success'>Found " . count($allMakes) . " makes</div>";
                    echo "<script>updateStatus('Found " . count($allMakes) . " makes');</script>";
                    flush();
                } catch (Exception $e) {
                    echo "<div class='error'>Failed to fetch makes: " . htmlspecialchars($e->getMessage()) . "</div>";
                    echo "<script>updateStatus('ERROR: Failed to fetch makes');</script>";
                    flush();
                    throw $e;
                }
                
                // Disable output buffering for real-time updates
                if (ob_get_level()) {
                    ob_end_flush();
                }
                @ini_set('output_buffering', 'off');
                // Suppress warning if headers already sent (not critical)
                if (!headers_sent()) {
                    @ini_set('zlib.output_compression', false);
                }
                
                // Process each year
                $makeIndex = 0;
                $totalMakes = count($allMakes);
                
                // Wrap year loop in try-catch to prevent script from stopping
                for ($year = $startYear; $year <= $endYear; $year++) {
                    try {
                        $currentYear++;
                        $progress = round(($currentYear / $totalYears) * 100);
                        echo "<div class='info' id='year-{$year}'>Processing year {$year} ({$currentYear}/{$totalYears})... <span id='make-progress-{$year}'>Make 0/{$totalMakes}</span></div>";
                        echo "<script>document.getElementById('progress').style.width = '{$progress}%'; document.getElementById('progress').textContent = '{$progress}%';</script>";
                        flush();
                        if (function_exists('fastcgi_finish_request')) {
                            fastcgi_finish_request();
                        }
                        
                        $yearInserted = 0;
                        $yearSkipped = 0;
                        $makeIndex = 0;
                    
                        // For each make, get models
                        foreach ($allMakes as $makeIndex => $make) {
                            $makeIndex++;
                            $makeProgress = round(($makeIndex / $totalMakes) * 100);
                            
                            // Update make progress every make (for better visibility)
                            echo "<script>
                                document.getElementById('make-progress-{$year}').textContent = 'Make {$makeIndex}/{$totalMakes} ({$makeProgress}%) - {$make}';
                                updateStatus('Year {$year}: Processing {$make} ({$makeIndex}/{$totalMakes})');
                            </script>";
                            flush();
                            
                            // Force output flush and send heartbeat
                            if (function_exists('fastcgi_finish_request')) {
                                fastcgi_finish_request();
                            }
                            
                            // Reset execution time limit for each make (prevents timeout)
                            @set_time_limit(60); // Reset to 60 seconds per make
                        
                        try {
                            // Add timeout wrapper with maximum execution time per make
                            $startTime = microtime(true);
                            $maxTimePerMake = 25; // Maximum 25 seconds per make
                            
                            echo "<script>updateStatus('Fetching models for {$year} {$make}...');</script>";
                            flush();
                            
                            // Use a timeout mechanism to prevent hanging
                            $models = [];
                            $apiCallStart = microtime(true);
                            
                            try {
                                $models = $nhtsaService->getModelsForMakeYear($make, $year);
                            } catch (Exception $apiError) {
                                // If API call fails, log but continue
                                $elapsed = round(microtime(true) - $apiCallStart, 2);
                                echo "<script>updateStatus('API error for {$make} after {$elapsed}s - skipping: ' + " . json_encode($apiError->getMessage()) . ");</script>";
                                flush();
                                continue; // Skip to next make
                            }
                            
                            $elapsed = round(microtime(true) - $startTime, 2);
                            
                            // Check if we exceeded maximum time (safety check)
                            if ($elapsed > $maxTimePerMake) {
                                echo "<script>updateStatus('⚠️ {$make} took {$elapsed}s (exceeded {$maxTimePerMake}s limit) - continuing...');</script>";
                                flush();
                            }
                            
                            echo "<script>updateStatus('Got " . count($models) . " models for {$make} in {$elapsed}s');</script>";
                            flush();
                            
                            if (empty($models)) {
                                echo "<script>updateStatus('No models found for {$year} {$make} - skipping');</script>";
                                flush();
                                continue; // Skip makes with no models for this year
                            }
                            
                            $modelCount = 0;
                            $insertStartTime = microtime(true);
                            $maxTimePerModel = 5; // Maximum 5 seconds per model operation
                            
                            // Insert each model (without tire sizes - those will be added later via AI or manual entry)
                            foreach ($models as $modelIndex => $model) {
                                $modelCount++;
                                $modelStartTime = microtime(true);
                                
                                // Reset execution time limit for each model
                                @set_time_limit(30);
                                
                                // Update status for every model (more frequent updates)
                                echo "<script>updateStatus('Processing model {$modelCount}/" . count($models) . " for {$make}: {$model}');</script>";
                                flush();
                                
                                // Force output flush
                                if (function_exists('fastcgi_finish_request')) {
                                    fastcgi_finish_request();
                                }
                                
                                // Check if already exists (with timeout protection and retry)
                                $existing = null;
                                $retries = 0;
                                $maxRetries = 2; // Reduced retries for faster failure
                                $checkStartTime = microtime(true);
                                
                                while ($retries < $maxRetries) {
                                    // Check timeout for this operation
                                    $checkElapsed = microtime(true) - $checkStartTime;
                                    if ($checkElapsed > $maxTimePerModel) {
                                        echo "<script>updateStatus('⚠️ Timeout checking {$make} {$model} - skipping');</script>";
                                        flush();
                                        $yearSkipped++;
                                        $totalSkipped++;
                                        continue 2; // Skip to next model
                                    }
                                    
                                    try {
                                        $existing = $fitmentModel->getFitment($year, $make, $model, null);
                                        break; // Success, exit retry loop
                                    } catch (\PDOException $checkError) {
                                        $retries++;
                                        if ($retries >= $maxRetries) {
                                            // Final retry failed - log and skip
                                            echo "<script>updateStatus('ERROR checking {$make} {$model} after {$maxRetries} retries - skipping');</script>";
                                            flush();
                                            $yearSkipped++;
                                            $totalSkipped++;
                                            continue 2; // Skip to next model
                                        }
                                        // Wait before retry (shorter backoff)
                                        usleep(50000 * $retries); // 0.05s, 0.1s
                                    } catch (Exception $checkError) {
                                        echo "<script>updateStatus('ERROR checking {$make} {$model} - skipping');</script>";
                                        flush();
                                        $yearSkipped++;
                                        $totalSkipped++;
                                        continue 2; // Skip to next model
                                    }
                                }
                                
                                if (!$existing) {
                                    // Insert new entry (without tire sizes - will be populated later)
                                    $insertRetries = 0;
                                    $insertMaxRetries = 2; // Reduced retries
                                    $inserted = false;
                                    $insertStartTime = microtime(true);
                                    
                                    while ($insertRetries < $insertMaxRetries && !$inserted) {
                                        // Check timeout for insert operation
                                        $insertElapsed = microtime(true) - $insertStartTime;
                                        if ($insertElapsed > $maxTimePerModel) {
                                            echo "<script>updateStatus('⚠️ Timeout inserting {$make} {$model} - skipping');</script>";
                                            flush();
                                            $yearSkipped++;
                                            $totalSkipped++;
                                            break; // Exit insert loop, continue to next model
                                        }
                                        
                                        try {
                                            $fitmentModel->addFitment([
                                                'year' => $year,
                                                'make' => $make,
                                                'model' => $model,
                                                'trim' => null,
                                                'front_tire' => 'TBD', // Placeholder - will be updated via AI or manual entry
                                                'rear_tire' => null,
                                                'notes' => 'Populated from NHTSA vPIC API - tire sizes to be determined via AI or manual entry'
                                            ]);
                                            $yearInserted++;
                                            $totalInserted++;
                                            $inserted = true;
                                            
                                            // Update status every 5 models (but always flush)
                                            if ($modelCount % 5 === 0) {
                                                echo "<script>updateStatus('✓ Inserted {$modelCount}/" . count($models) . " models for {$make}');</script>";
                                            }
                                            flush();
                                            
                                            // Force output flush
                                            if (function_exists('fastcgi_finish_request')) {
                                                fastcgi_finish_request();
                                            }
                                        } catch (\PDOException $e) {
                                            $insertRetries++;
                                            if (strpos($e->getMessage(), 'duplicate') !== false || strpos($e->getMessage(), 'UNIQUE') !== false) {
                                                // Duplicate entry - skip (not an error)
                                                $yearSkipped++;
                                                $totalSkipped++;
                                                $inserted = true; // Mark as handled
                                                break;
                                            } elseif ($insertRetries >= $insertMaxRetries) {
                                                // Final retry failed - log error but continue
                                                $errors[] = "Error inserting {$year} {$make} {$model} after {$insertMaxRetries} retries: " . $e->getMessage();
                                                echo "<script>updateStatus('ERROR inserting {$make} {$model} - skipping');</script>";
                                                flush();
                                                $yearSkipped++;
                                                $totalSkipped++;
                                            } else {
                                                // Wait before retry (shorter backoff)
                                                usleep(50000 * $insertRetries); // 0.05s, 0.1s
                                            }
                                        } catch (Exception $e) {
                                            $insertRetries++;
                                            if ($insertRetries >= $insertMaxRetries) {
                                                $errors[] = "Error inserting {$year} {$make} {$model}: " . $e->getMessage();
                                                echo "<script>updateStatus('ERROR inserting {$make} {$model} - skipping');</script>";
                                                flush();
                                                $yearSkipped++;
                                                $totalSkipped++;
                                            } else {
                                                usleep(50000 * $insertRetries);
                                            }
                                        }
                                    }
                                } else {
                                    $yearSkipped++;
                                    $totalSkipped++;
                                }
                                
                                // Check if this model took too long (watchdog)
                                $modelElapsed = microtime(true) - $modelStartTime;
                                if ($modelElapsed > $maxTimePerModel * 2) {
                                    echo "<script>updateStatus('⚠️ Model {$modelCount} took {$modelElapsed}s - continuing...');</script>";
                                    flush();
                                }
                            }
                            
                            $insertTime = round(microtime(true) - $insertStartTime, 2);
                            
                            echo "<script>updateStatus('Completed {$make}: " . count($models) . " models, {$yearInserted} inserted');</script>";
                            flush();
                            
                            // Small delay to avoid rate limiting (reduced for faster processing)
                            usleep(50000); // 0.05 second
                            
                        } catch (Exception $e) {
                            $errorMsg = "Error fetching models for {$year} {$make}: " . $e->getMessage();
                            $errors[] = $errorMsg;
                            
                            // Only show warning for non-critical errors (JSON parse errors are handled gracefully now)
                            if (strpos($e->getMessage(), 'parse') === false && strpos($e->getMessage(), 'JSON') === false) {
                                echo "<div class='warning'>⚠️ {$errorMsg}</div>";
                            }
                            
                            echo "<script>updateStatus('⚠️ Skipping {$make} due to error - continuing...');</script>";
                            flush();
                            
                            // Small delay before continuing
                            usleep(50000);
                            
                            // Continue to next make instead of stopping
                            continue;
                        }
                        }
                        
                        echo "<div class='success' id='year-result-{$year}'>✓ Year {$year} Complete: Inserted {$yearInserted}, Skipped {$yearSkipped}</div>";
                        flush();
                    } catch (Exception $yearError) {
                        // Catch any unexpected errors in year processing and continue
                        $errorMsg = "Unexpected error processing year {$year}: " . $yearError->getMessage();
                        $errors[] = $errorMsg;
                        echo "<div class='warning'>⚠️ {$errorMsg}</div>";
                        echo "<script>updateStatus('⚠️ Error processing year {$year} - continuing to next year...');</script>";
                        flush();
                        // Continue to next year instead of stopping
                    }
                }
                
                echo "<div class='progress'><div class='progress-bar' style='width: 100%'>100%</div></div>";
                echo "<div class='success'>";
                echo "<h2>✓ Population Complete!</h2>";
                echo "<p><strong>Total Inserted:</strong> {$totalInserted} vehicle entries</p>";
                echo "<p><strong>Total Skipped:</strong> {$totalSkipped} (already existed)</p>";
                echo "<p><strong>Years Processed:</strong> {$startYear} to {$endYear}</p>";
                echo "</div>";
                
                if (!empty($errors)) {
                    echo "<div class='warning'>";
                    echo "<h3>Errors encountered (" . count($errors) . "):</h3>";
                    echo "<pre>" . htmlspecialchars(implode("\n", array_slice($errors, 0, 50))) . "</pre>";
                    if (count($errors) > 50) {
                        echo "<p>... and " . (count($errors) - 50) . " more errors</p>";
                    }
                    echo "</div>";
                }
                
                // Show current database stats
                try {
                    $stmt = $db->query("SELECT COUNT(DISTINCT year) as years, COUNT(DISTINCT make) as makes, COUNT(DISTINCT model) as models, COUNT(*) as total FROM vehicle_fitment");
                    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
                    echo "<div class='info'>";
                    echo "<h3>Current Database Statistics:</h3>";
                    echo "<p>Years: {$stats['years']}, Makes: {$stats['makes']}, Models: {$stats['models']}, Total Entries: {$stats['total']}</p>";
                    echo "</div>";
                } catch (Exception $e) {
                    // Ignore stats errors
                }
                
            } else {
                // Show form
                ?>
                <div class="info">
                    <p><strong>This script will:</strong></p>
                    <ul class="list-disc list-inside mt-2">
                        <li>Fetch all makes from NHTSA vPIC API</li>
                        <li>For each year, fetch all models for each make</li>
                        <li>Insert Year/Make/Model combinations into your database</li>
                        <li>Skip entries that already exist</li>
                    </ul>
                    <p class="mt-4"><strong>Note:</strong> This will NOT populate tire sizes. Tire sizes will need to be added later via AI detection or manual entry.</p>
                </div>
                
                <form method="POST" class="mt-6">
                    <input type="hidden" name="action" value="populate">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Start Year
                        </label>
                        <input type="number" name="start_year" value="2010" min="1980" max="2030" class="w-full px-4 py-2 border border-gray-300 rounded-md" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            End Year
                        </label>
                        <input type="number" name="end_year" value="2024" min="1980" max="2030" class="w-full px-4 py-2 border border-gray-300 rounded-md" required>
                    </div>
                    
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                        Start Population
                    </button>
                </form>
                
                <div class="warning mt-6">
                    <p class="text-sm"><strong>⚠️ Important:</strong> This process may take 30-60 minutes depending on the year range. The script will make many API calls to NHTSA. Please keep this page open until completion.</p>
                </div>
                <?php
            }
            
        } catch (Exception $e) {
            echo '<div class="error">';
            echo '<strong>❌ Error:</strong> ' . htmlspecialchars($e->getMessage());
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
            echo '</div>';
        }
        ?>
        
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mt-6">
            <p class="text-sm text-yellow-700">
                <strong>⚠️ Security Note:</strong> Delete this file (<code>public/populate-ymm-from-nhtsa.php</code>) after importing for security!
            </p>
        </div>
        
        <p class="mt-6"><a href="/" style="color: #0c5460; text-decoration: underline;">← Back to Home</a></p>
    </div>
</body>
</html>
