<?php
/**
 * AI Tire Size Detection API Endpoint
 * Detects tire sizes for a vehicle using AI
 * 
 * POST /api/detect-tire-sizes.php
 * Body: { "year": 2020, "make": "Toyota", "model": "Camry", "trim": "LE", "body_class": "", "drive_type": "" }
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
$year = InputHelper::sanitizeInt($input['year'] ?? 0);
$make = InputHelper::sanitizeString($input['make'] ?? '');
$model = InputHelper::sanitizeString($input['model'] ?? '');
$trim = InputHelper::sanitizeString($input['trim'] ?? '');
$bodyClass = InputHelper::sanitizeString($input['body_class'] ?? '');
$driveType = InputHelper::sanitizeString($input['drive_type'] ?? '');

if (!$year || empty($make) || empty($model)) {
    ResponseHelper::error('Year, make, and model are required', 400);
}

try {
    $aiTireService = new AITireSizeService();
    
    if (!$aiTireService->isAvailable()) {
        ResponseHelper::error('AI service is not available. Please configure GEMINI_API_KEY.', 503);
    }
    
    error_log("AI tire size detection requested for: {$year} {$make} {$model} {$trim}");
    
    $aiTireSizes = $aiTireService->getTireSizesFromAI(
        $year,
        $make,
        $model,
        !empty($trim) ? $trim : null,
        !empty($bodyClass) ? $bodyClass : null,
        !empty($driveType) ? $driveType : null
    );
    
    if ($aiTireSizes && isset($aiTireSizes['front_tire']) && !empty($aiTireSizes['front_tire'])) {
        $responseData = [
            'front_tire' => $aiTireSizes['front_tire'],
            'rear_tire' => $aiTireSizes['rear_tire'] ?? null,
            'source' => $aiTireSizes['source'] ?? 'ai',
            'is_staggered' => !empty($aiTireSizes['rear_tire']) && $aiTireSizes['front_tire'] !== $aiTireSizes['rear_tire']
        ];
        
        error_log("✓ AI tire sizes detected: Front=" . $responseData['front_tire'] . ", Rear=" . ($responseData['rear_tire'] ?? 'null'));
        
        ResponseHelper::success($responseData);
    } else {
        ResponseHelper::error('AI could not detect tire sizes for this vehicle. Please enter tire sizes manually.', 404);
    }
    
} catch (Exception $e) {
    error_log("AI tire size detection error: " . $e->getMessage());
    ResponseHelper::error('Failed to detect tire sizes: ' . $e->getMessage(), 500);
}
