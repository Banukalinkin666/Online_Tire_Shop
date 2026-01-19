<?php
/**
 * VIN Decode API Endpoint
 * 
 * POST /api/vin.php
 * Body: { "vin": "1HGBH41JXMN109186" }
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Services\NHTSAService;
use App\Services\TireMatchService;
use App\Services\AITireSizeService;
use App\Helpers\ResponseHelper;
use App\Helpers\InputHelper;

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

$vin = InputHelper::sanitizeString($input['vin'] ?? '');

if (empty($vin)) {
    ResponseHelper::error('VIN is required');
}

if (!InputHelper::validateVIN($vin)) {
    ResponseHelper::error('Entered VIN is not valid. VIN must be exactly 17 alphanumeric characters (no I, O, or Q).', 400);
}

try {
    // Decode VIN
    $nhtsaService = new NHTSAService();
    $vehicleInfo = $nhtsaService->decodeVIN($vin);

    // Get available trims for this vehicle
    $tireMatchService = new TireMatchService();
    $trims = $tireMatchService->getAvailableTrims(
        $vehicleInfo['year'],
        $vehicleInfo['make'],
        $vehicleInfo['model']
    );

    // Try to get tire sizes using AI
    $aiTireService = new AITireSizeService();
    $aiTireSizes = null;
    $tireSizesSource = 'database';
    
    if ($aiTireService->isAvailable()) {
        try {
            $aiTireSizes = $aiTireService->getTireSizesFromAI(
                $vehicleInfo['year'],
                $vehicleInfo['make'],
                $vehicleInfo['model'],
                $vehicleInfo['trim'] ?? null,
                $vehicleInfo['body_class'] ?? null,
                $vehicleInfo['drive_type'] ?? null
            );
            
            if ($aiTireSizes) {
                $tireSizesSource = $aiTireSizes['source'] ?? 'ai';
            }
        } catch (Exception $e) {
            // AI failed, but continue with database lookup
            error_log("AI tire size lookup failed: " . $e->getMessage());
        }
    }

    // Return vehicle info with available trims and AI tire sizes
    // Note: VIN is NOT stored in database per privacy requirements
    $responseData = [
        'vehicle' => [
            'year' => $vehicleInfo['year'],
            'make' => $vehicleInfo['make'],
            'model' => $vehicleInfo['model'],
            'body_class' => $vehicleInfo['body_class'] ?? '',
            'drive_type' => $vehicleInfo['drive_type'] ?? '',
            'fuel_type' => $vehicleInfo['fuel_type'] ?? ''
        ],
        'trims' => $trims,
        'message' => 'VIN decoded successfully. Please select a trim to continue.'
    ];
    
    // Add AI tire sizes if available
    if ($aiTireSizes) {
        $responseData['tire_sizes'] = [
            'front_tire' => $aiTireSizes['front_tire'],
            'rear_tire' => $aiTireSizes['rear_tire'],
            'source' => $tireSizesSource,
            'is_staggered' => !empty($aiTireSizes['rear_tire']) && $aiTireSizes['front_tire'] !== $aiTireSizes['rear_tire']
        ];
        $responseData['message'] = 'VIN decoded successfully. Tire sizes determined using AI.';
    }
    
    ResponseHelper::success($responseData);

} catch (Exception $e) {
    error_log("VIN decode error: " . $e->getMessage());
    error_log("VIN decode stack trace: " . $e->getTraceAsString());
    
    // Check for specific error types
    $message = $e->getMessage();
    
    // Invalid VIN format errors
    if (strpos($message, 'Invalid VIN') !== false || strpos($message, 'VIN contains invalid') !== false) {
        ResponseHelper::error('Entered VIN is not valid. Please check the VIN and try again.', 400);
    }
    
    // Network/API errors
    if (strpos($message, 'curl') !== false || strpos($message, 'API request failed') !== false) {
        ResponseHelper::error('Network error: Unable to connect to VIN decoding service. Please try again later or use Year/Make/Model search instead.', 503);
    }
    
    // Unable to decode (vehicle not in NHTSA database)
    if (strpos($message, 'Unable to decode') !== false || strpos($message, 'missing') !== false) {
        ResponseHelper::error('Unable to decode VIN. This VIN may not be in the NHTSA database. Please try using Year/Make/Model search instead.', 404);
    }
    
    // Generic error
    ResponseHelper::error('Failed to decode VIN. Please verify the VIN is correct or use Year/Make/Model search instead.', 500);
}
