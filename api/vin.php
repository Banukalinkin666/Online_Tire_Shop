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
    ResponseHelper::error('Invalid VIN format. VIN must be exactly 17 alphanumeric characters.');
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

    // Return vehicle info with available trims
    // Note: VIN is NOT stored in database per privacy requirements
    ResponseHelper::success([
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
    ]);

} catch (Exception $e) {
    error_log("VIN decode error: " . $e->getMessage());
    error_log("VIN decode stack trace: " . $e->getTraceAsString());
    
    // Check if it's a cURL error
    if (strpos($e->getMessage(), 'curl') !== false || strpos($e->getMessage(), 'API request failed') !== false) {
        ResponseHelper::error('Network error: Unable to connect to VIN decoding service. Please try again later or use Year/Make/Model search instead.', 503);
    } else {
        ResponseHelper::error('Failed to decode VIN: ' . $e->getMessage(), 500);
    }
}
