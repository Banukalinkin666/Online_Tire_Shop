<?php
/**
 * Test AI Tire Size Service
 * 
 * Access: https://your-site.onrender.com/test-ai.php
 * 
 * This script tests if the Gemini API is working and which models are available
 */

// Suppress output
error_reporting(E_ALL);
ini_set('display_errors', '1');
ob_start();

require_once __DIR__ . '/../app/bootstrap.php';

use App\Services\AITireSizeService;

ob_end_clean();

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>AI Tire Size Service Test</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        .success { color: #4ec9b0; }
        .error { color: #f48771; }
        .info { color: #569cd6; }
        pre { background: #252526; padding: 10px; border-radius: 4px; overflow-x: auto; }
        h2 { color: #4ec9b0; border-bottom: 1px solid #3e3e42; padding-bottom: 5px; }
    </style>
</head>
<body>
    <h1>🤖 AI Tire Size Service Test</h1>
    
    <?php
    echo "<h2>1. API Key Check</h2>";
    $aiService = new AITireSizeService();
    $isAvailable = $aiService->isAvailable();
    
    if ($isAvailable) {
        echo "<p class='success'>✓ API Key found</p>";
    } else {
        echo "<p class='error'>✗ API Key NOT found</p>";
        echo "<p>Please set GEMINI_API_KEY in Render environment variables.</p>";
    }
    
    if ($isAvailable) {
        echo "<h2>2. Testing Models</h2>";
        echo "<p class='info'>Testing different Gemini models to find one that works...</p>";
        
        // Test with a simple vehicle
        $testYear = 2020;
        $testMake = 'Toyota';
        $testModel = 'Camry';
        
        echo "<p>Testing: $testYear $testMake $testModel</p>";
        
        try {
            $result = $aiService->getTireSizesFromAI($testYear, $testMake, $testModel);
            
            if ($result) {
                echo "<p class='success'>✓ AI Service Working!</p>";
                echo "<pre>";
                echo "Front Tire: " . ($result['front_tire'] ?? 'null') . "\n";
                echo "Rear Tire: " . ($result['rear_tire'] ?? 'null') . "\n";
                echo "Source: " . ($result['source'] ?? 'unknown') . "\n";
                echo "</pre>";
            } else {
                echo "<p class='error'>✗ AI Service returned null</p>";
                echo "<p>Check Render logs for detailed error messages.</p>";
            }
        } catch (Exception $e) {
            echo "<p class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p>Check Render logs for more details.</p>";
        }
    }
    
    echo "<h2>3. Environment Check</h2>";
    echo "<pre>";
    echo "GEMINI_API_KEY set: " . (getenv('GEMINI_API_KEY') ? 'YES (length: ' . strlen(getenv('GEMINI_API_KEY')) . ')' : 'NO') . "\n";
    echo "_ENV['GEMINI_API_KEY']: " . (isset($_ENV['GEMINI_API_KEY']) ? 'YES' : 'NO') . "\n";
    echo "_SERVER['GEMINI_API_KEY']: " . (isset($_SERVER['GEMINI_API_KEY']) ? 'YES' : 'NO') . "\n";
    echo "</pre>";
    
    echo "<h2>4. Next Steps</h2>";
    echo "<ul>";
    echo "<li>If API key is missing: Add GEMINI_API_KEY to Render environment variables</li>";
    echo "<li>If models fail: Check Render logs to see which models were tried</li>";
    echo "<li>If still not working: The API key might not have access to Gemini models</li>";
    echo "</ul>";
    ?>
    
    <p><a href="/" style="color: #4ec9b0;">← Back to Tire Finder</a></p>
</body>
</html>
