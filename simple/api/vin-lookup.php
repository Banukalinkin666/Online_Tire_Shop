<?php
/**
 * VIN Lookup API Endpoint
 * Returns vehicle info and tire sizes
 * 
 * GET /api/vin-lookup.php?vin=1HGBH41JXMN109186
 */

// Suppress output before JSON
error_reporting(0);
ini_set('display_errors', '0');
ob_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/../VINDecoder.php';
require_once __DIR__ . '/../TireLookup.php';

// Clear output buffer and set JSON headers
ob_end_clean();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use GET.'
    ]);
    exit;
}

// Get VIN from query parameter
$vin = $_GET['vin'] ?? '';

if (empty($vin)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'VIN is required'
    ]);
    exit;
}

try {
    $tireLookup = new TireLookup();
    $vinDecoder = new VINDecoder();

    // Check cache first
    $vehicle = $tireLookup->getCachedVehicle($vin);

    // If not cached, decode VIN
    if (!$vehicle) {
        $vehicle = $vinDecoder->decode($vin);
        // Cache the result
        $tireLookup->cacheVehicle($vehicle);
    }

    // Lookup tire sizes
    $tireSizes = $tireLookup->findTireSizes(
        $vehicle['year'],
        $vehicle['make'],
        $vehicle['model'],
        $vehicle['trim']
    );

    // Build response
    $response = [
        'success' => true,
        'data' => [
            'vehicle' => [
                'year' => $vehicle['year'],
                'make' => $vehicle['make'],
                'model' => $vehicle['model'],
                'trim' => $vehicle['trim']
            ],
            'tires' => $tireSizes ? [
                'front_tire' => $tireSizes['front_tire'],
                'rear_tire' => $tireSizes['rear_tire'],
                'verified' => $tireSizes['verified']
            ] : null
        ]
    ];

    // If tire sizes not found
    if (!$tireSizes) {
        $response['data']['tires'] = null;
        $response['message'] = 'Tire size information not available for this vehicle.';
    } else {
        $response['message'] = $tireSizes['verified'] 
            ? 'Vehicle and tire information found.' 
            : 'Vehicle found. Tire size is estimated (trim not matched).';
    }

    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
