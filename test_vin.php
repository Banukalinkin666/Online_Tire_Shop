<?php
/**
 * Quick VIN Test Script
 * Test a specific VIN to see what's happening
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Services\NHTSAService;
use App\Helpers\InputHelper;

$vin = '19XFC2F59GE123456';

echo "Testing VIN: $vin\n";
echo "VIN Length: " . strlen($vin) . "\n";
echo "VIN Validation: " . (InputHelper::validateVIN($vin) ? 'PASS' : 'FAIL') . "\n";

// Check regex pattern
$pattern = '/^[A-HJ-NPR-Z0-9]{17}$/';
echo "Regex Pattern Match: " . (preg_match($pattern, $vin) ? 'PASS' : 'FAIL') . "\n";

// Test NHTSA API
try {
    $nhtsaService = new NHTSAService();
    $vehicleInfo = $nhtsaService->decodeVIN($vin);
    
    echo "\n=== VIN Decode Success ===\n";
    echo "Year: " . ($vehicleInfo['year'] ?? 'N/A') . "\n";
    echo "Make: " . ($vehicleInfo['make'] ?? 'N/A') . "\n";
    echo "Model: " . ($vehicleInfo['model'] ?? 'N/A') . "\n";
    echo "Trim: " . ($vehicleInfo['trim'] ?? 'N/A') . "\n";
    
    print_r($vehicleInfo);
    
} catch (Exception $e) {
    echo "\n=== VIN Decode Error ===\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
