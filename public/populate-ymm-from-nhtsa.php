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

/**
 * Render-friendly batch runner
 * ---------------------------
 * Long-running HTTP requests frequently get interrupted on free tiers.
 * This script supports a "batch mode" that processes a small number of makes
 * per request, then auto-redirects to continue.
 */
function ymmProgressFile(): string
{
    $dir = sys_get_temp_dir();
    return rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'ymm_population_progress.json';
}

function loadYmmProgress(): array
{
    $file = ymmProgressFile();
    if (!file_exists($file)) {
        return [];
    }
    $raw = @file_get_contents($file);
    if ($raw === false || trim($raw) === '') {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function saveYmmProgress(array $data): void
{
    $file = ymmProgressFile();
    @file_put_contents($file, json_encode($data));
}

function resetYmmProgress(): void
{
    $file = ymmProgressFile();
    if (file_exists($file)) {
        @unlink($file);
    }
}

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
            $startYear = isset($_POST['start_year']) ? (int)$_POST['start_year'] : (int)($_GET['start_year'] ?? 2010);
            $endYear = isset($_POST['end_year']) ? (int)$_POST['end_year'] : (int)($_GET['end_year'] ?? 2024);
            $action = $_POST['action'] ?? '';
            $run = isset($_GET['run']) && $_GET['run'] === '1';
            $batchSize = isset($_GET['batch']) ? max(1, (int)$_GET['batch']) : 3; // makes per request
            
            if (($action === 'populate' || $run) && $startYear > 0 && $endYear > 0 && $endYear >= $startYear) {
                // IMPORTANT: Render-friendly batching (avoid long single HTTP request)
                @ini_set('max_execution_time', 120);
                @ini_set('memory_limit', '512M');
                ignore_user_abort(true);
                @set_time_limit(120);

                // If this is a fresh POST "start", reset progress and redirect to batch runner
                if ($action === 'populate' && !$run) {
                    resetYmmProgress();
                    $keyParam = $secretKey !== '' ? '&key=' . urlencode($secretKey) : '';
                    $nextUrl = "/populate-ymm-from-nhtsa.php?run=1&start_year={$startYear}&end_year={$endYear}&batch={$batchSize}{$keyParam}";
                    echo "<div class='info'>Starting batch population (Render-friendly). Redirecting...</div>";
                    echo "<script>setTimeout(function(){ window.location.href = " . json_encode($nextUrl) . "; }, 500);</script>";
                    flush();
                    return;
                }

                echo "<div class='info'>Batch population running for years {$startYear} to {$endYear} (batch={$batchSize} makes/request)...</div>";
                echo "<div class='info' style='background: #fff3cd;'>⏱️ Keep this tab open. If it reloads, it will resume. Last update: <span id='last-update'>" . date('H:i:s') . "</span></div>";
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
                </script>";
                flush();

                // Disable output buffering for real-time updates
                if (ob_get_level()) {
                    @ob_end_flush();
                }
                @ini_set('output_buffering', 'off');
                if (!headers_sent()) {
                    @ini_set('zlib.output_compression', false);
                }

                // Load or initialize progress
                $progressData = loadYmmProgress();
                if (empty($progressData) || ($progressData['start_year'] ?? null) !== $startYear || ($progressData['end_year'] ?? null) !== $endYear) {
                    $allMakes = $nhtsaService->getMakesForYear($startYear);
                    $progressData = [
                        'start_year' => $startYear,
                        'end_year' => $endYear,
                        'current_year' => $startYear,
                        'make_offset' => 0,
                        'makes' => $allMakes,
                        'total_inserted' => 0,
                        'total_skipped' => 0,
                    ];
                    saveYmmProgress($progressData);
                }

                $allMakes = $progressData['makes'] ?? [];
                $totalMakes = count($allMakes);
                $year = (int)($progressData['current_year'] ?? $startYear);
                $makeOffset = (int)($progressData['make_offset'] ?? 0);

                if ($year > $endYear) {
                    echo "<div class='success'><h2>✓ Population Complete!</h2></div>";
                    resetYmmProgress();
                    return;
                }

                // Progress bar based on year + make offset
                $totalYears = $endYear - $startYear + 1;
                $yearIndex = ($year - $startYear);
                $yearProgress = ($yearIndex / max(1, $totalYears)) * 100;
                $makeProgress = ($totalMakes > 0) ? (($makeOffset / $totalMakes) * (100 / max(1, $totalYears))) : 0;
                $progressPct = (int)min(99, round($yearProgress + $makeProgress));
                echo "<script>document.getElementById('progress').style.width = '{$progressPct}%'; document.getElementById('progress').textContent = '{$progressPct}%';</script>";

                $batchStart = $makeOffset;
                $batchEnd = min($totalMakes, $makeOffset + $batchSize);
                echo "<div class='info'>Processing year {$year} — makes " . ($batchStart + 1) . " to {$batchEnd} of {$totalMakes}</div>";
                echo "<script>updateStatus('Year {$year}: starting batch makes " . ($batchStart + 1) . "-{$batchEnd}');</script>";
                flush();

                $yearInserted = 0;
                $yearSkipped = 0;

                for ($i = $batchStart; $i < $batchEnd; $i++) {
                    $make = $allMakes[$i] ?? null;
                    if (!$make) {
                        continue;
                    }

                    echo "<script>updateStatus('Fetching models for {$year} {$make}...');</script>";
                    flush();

                    $models = $nhtsaService->getModelsForMakeYear($make, $year);
                    if (empty($models)) {
                        echo "<script>updateStatus('No models for {$year} {$make} — skip');</script>";
                        flush();
                        continue;
                    }

                    $modelCount = 0;
                    foreach ($models as $model) {
                        $modelCount++;
                        // Keep each request small; we do a simple existence check via getFitment()
                        try {
                            $existing = $fitmentModel->getFitment($year, $make, $model, null);
                            if ($existing) {
                                $yearSkipped++;
                                $progressData['total_skipped']++;
                                continue;
                            }
                            $fitmentModel->addFitment([
                                'year' => $year,
                                'make' => $make,
                                'model' => $model,
                                'trim' => null,
                                'front_tire' => 'TBD',
                                'rear_tire' => null,
                                'notes' => 'Populated from NHTSA vPIC API - tire sizes to be determined via AI or manual entry'
                            ]);
                            $yearInserted++;
                            $progressData['total_inserted']++;
                        } catch (Exception $e) {
                            $yearSkipped++;
                            $progressData['total_skipped']++;
                        }

                        if ($modelCount % 10 === 0) {
                            echo "<script>updateStatus('{$make}: processed {$modelCount}/" . count($models) . " models');</script>";
                            flush();
                        }
                    }

                    echo "<script>updateStatus('Completed {$make}: " . count($models) . " models, {$yearInserted} inserted so far');</script>";
                    flush();
                }

                // Advance progress
                $makeOffset = $batchEnd;
                if ($makeOffset >= $totalMakes) {
                    echo "<div class='success'>✓ Year {$year} batch complete. Inserted {$yearInserted}, Skipped {$yearSkipped}</div>";
                    $year++;
                    $makeOffset = 0;
                }

                $progressData['current_year'] = $year;
                $progressData['make_offset'] = $makeOffset;
                saveYmmProgress($progressData);

                $keyParam = $secretKey !== '' ? '&key=' . urlencode($secretKey) : '';
                $nextUrl = "/populate-ymm-from-nhtsa.php?run=1&start_year={$startYear}&end_year={$endYear}&batch={$batchSize}{$keyParam}";
                echo "<div class='info'>Continuing automatically...</div>";
                echo "<script>setTimeout(function(){ window.location.href = " . json_encode($nextUrl) . "; }, 800);</script>";
                flush();

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
