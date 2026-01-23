<?php
/**
 * Quick AI Connection Test
 * Tests if Gemini API key is working
 * 
 * GET /api/test-ai-connection.php
 */

error_reporting(0);
ini_set('display_errors', '0');
ob_start();

require_once __DIR__ . '/../app/bootstrap.php';

use App\Services\AITireSizeService;

ob_end_clean();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    $aiService = new AITireSizeService();
    
    // Check if API key is available
    $isAvailable = $aiService->isAvailable();
    
    if (!$isAvailable) {
        echo json_encode([
            'success' => false,
            'message' => 'GEMINI_API_KEY not found in environment variables',
            'debug' => [
                'getenv' => getenv('GEMINI_API_KEY') ? 'Found (length: ' . strlen(getenv('GEMINI_API_KEY')) . ')' : 'Not found',
                '_ENV' => isset($_ENV['GEMINI_API_KEY']) ? 'Found (length: ' . strlen($_ENV['GEMINI_API_KEY']) . ')' : 'Not found',
                '_SERVER' => isset($_SERVER['GEMINI_API_KEY']) ? 'Found (length: ' . strlen($_SERVER['GEMINI_API_KEY']) . ')' : 'Not found'
            ]
        ], JSON_PRETTY_PRINT);
        exit;
    }
    
    // Test with a simple vehicle - catch exceptions to see actual errors
    try {
        $result = $aiService->getTireSizesFromAI(2020, 'Toyota', 'Camry', 'LE');
        
        if ($result && isset($result['front_tire'])) {
            echo json_encode([
                'success' => true,
                'message' => 'AI connection working!',
                'test_result' => [
                    'vehicle' => '2020 Toyota Camry LE',
                    'front_tire' => $result['front_tire'],
                    'rear_tire' => $result['rear_tire'] ?? null,
                    'source' => $result['source'] ?? 'ai'
                ]
            ], JSON_PRETTY_PRINT);
        } else {
            // Try to get more details by calling the private method directly
            $reflection = new ReflectionClass($aiService);
            $method = $reflection->getMethod('getTireSizesFromGemini');
            $method->setAccessible(true);
            
            try {
                $directResult = $method->invoke($aiService, 2020, 'Toyota', 'Camry', 'LE', null, null);
                
                if ($directResult) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'AI connection working (via direct call)!',
                        'test_result' => [
                            'vehicle' => '2020 Toyota Camry LE',
                            'front_tire' => $directResult['front_tire'],
                            'rear_tire' => $directResult['rear_tire'] ?? null
                        ]
                    ], JSON_PRETTY_PRINT);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'API key found but Gemini API returned null',
                        'debug' => [
                            'api_key_length' => strlen(getenv('GEMINI_API_KEY') ?: $_ENV['GEMINI_API_KEY'] ?? $_SERVER['GEMINI_API_KEY'] ?? ''),
                            'api_key_prefix' => substr(getenv('GEMINI_API_KEY') ?: $_ENV['GEMINI_API_KEY'] ?? $_SERVER['GEMINI_API_KEY'] ?? '', 0, 10),
                            'note' => 'Check Render logs for detailed Gemini API errors'
                        ]
                    ], JSON_PRETTY_PRINT);
                }
            } catch (Exception $directError) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Gemini API error: ' . $directError->getMessage(),
                    'error_type' => get_class($directError),
                    'debug' => [
                        'api_key_length' => strlen(getenv('GEMINI_API_KEY') ?: $_ENV['GEMINI_API_KEY'] ?? $_SERVER['GEMINI_API_KEY'] ?? ''),
                        'api_key_prefix' => substr(getenv('GEMINI_API_KEY') ?: $_ENV['GEMINI_API_KEY'] ?? $_SERVER['GEMINI_API_KEY'] ?? '', 0, 10),
                        'full_error' => $directError->getMessage()
                    ]
                ], JSON_PRETTY_PRINT);
            }
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Exception during AI test: ' . $e->getMessage(),
            'error_type' => get_class($e),
            'stack_trace' => $e->getTraceAsString()
        ], JSON_PRETTY_PRINT);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error testing AI connection: ' . $e->getMessage(),
        'error_type' => get_class($e)
    ], JSON_PRETTY_PRINT);
}
