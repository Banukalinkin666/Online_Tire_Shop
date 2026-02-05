<?php
/**
 * AI Natural Language Tire Size Detection API Endpoint
 * Handles natural language queries like "What tire sizes fit my 2018 honda civic with 16 wheels"
 * 
 * POST /api/ai-natural-language.php
 * Body: { "query": "What tire sizes fit my 2018 honda civic with 16 wheels" }
 */

// Suppress ALL output and errors before JSON
error_reporting(0);
ini_set('display_errors', '0');
ob_start();

require_once __DIR__ . '/../app/bootstrap.php';

use App\Services\AITireSizeService;
use App\Helpers\ResponseHelper;
use App\Helpers\InputHelper;

// Clear ALL output buffer and set JSON headers
ob_end_clean();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ResponseHelper::error('Method not allowed. Use POST.', 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    ResponseHelper::error('Invalid JSON input');
}

// Validate required fields
$query = trim($input['query'] ?? '');

if (empty($query)) {
    ResponseHelper::error('Query is required', 400);
}

try {
    $aiTireService = new AITireSizeService();
    
    if (!$aiTireService->isAvailable()) {
        ResponseHelper::error('AI service is not available. Please configure GEMINI_API_KEY.', 503);
    }
    
    error_log("AI natural language query: " . $query);
    
    // Use AI to parse the natural language query and get tire sizes
    $result = $aiTireService->getTireSizesFromNaturalLanguage($query);
    
    if ($result && isset($result['success']) && $result['success']) {
        error_log("✓ AI natural language query successful: " . json_encode($result));
        ResponseHelper::success($result);
    } else {
        $errorMsg = $result['message'] ?? 'AI could not process your query. Please try rephrasing your question.';
        error_log("✗ AI natural language query failed: " . $errorMsg);
        ResponseHelper::error($errorMsg, 404);
    }
    
} catch (Exception $e) {
    error_log("AI natural language query error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    ResponseHelper::error('Failed to process query: ' . $e->getMessage(), 500);
}
