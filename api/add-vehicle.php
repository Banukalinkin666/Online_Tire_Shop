<?php
/**
 * Add Vehicle to Database API Endpoint
 * 
 * POST /api/add-vehicle.php
 * Body: { "year": 2020, "make": "Toyota", "model": "Camry", "trim": "LE", "front_tire": "215/55R17", "rear_tire": null, "notes": "User added" }
 */

// Suppress ALL output and errors before JSON
error_reporting(0);
ini_set('display_errors', '0');
ob_start();

require_once __DIR__ . '/../app/bootstrap.php';

use App\Models\VehicleFitment;
use App\Helpers\ResponseHelper;
use App\Helpers\InputHelper;
use PDOException;

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

// Validate and sanitize input
$year = InputHelper::sanitizeInt($input['year'] ?? null);
$make = InputHelper::sanitizeString($input['make'] ?? '');
$model = InputHelper::sanitizeString($input['model'] ?? '');
$trim = !empty($input['trim']) ? InputHelper::sanitizeString($input['trim']) : null;
$frontTire = InputHelper::sanitizeString($input['front_tire'] ?? '');
$rearTire = !empty($input['rear_tire']) ? InputHelper::sanitizeString($input['rear_tire']) : null;
$notes = !empty($input['notes']) ? InputHelper::sanitizeString($input['notes']) : 'User added vehicle';

// Validation
$errors = [];

if (!$year || $year < 1900 || $year > 2100) {
    $errors[] = 'Year is required and must be between 1900 and 2100';
}

if (empty($make)) {
    $errors[] = 'Make is required';
}

if (empty($model)) {
    $errors[] = 'Model is required';
}

if (empty($frontTire)) {
    $errors[] = 'Front tire size is required (e.g., 215/55R17)';
}

// Validate tire size format (basic check)
if (!empty($frontTire) && !preg_match('/^\d{3}\/\d{2}R\d{2}$/', $frontTire)) {
    $errors[] = 'Front tire size must be in format: 225/65R17';
}

if (!empty($rearTire) && !preg_match('/^\d{3}\/\d{2}R\d{2}$/', $rearTire)) {
    $errors[] = 'Rear tire size must be in format: 225/65R17';
}

if (!empty($errors)) {
    ResponseHelper::error('Validation failed', 400, $errors);
}

try {
    $vehicleFitment = new VehicleFitment();
    
    // Check if vehicle already exists
    $existing = $vehicleFitment->getFitment($year, $make, $model, $trim);
    
    if ($existing) {
        // Vehicle already exists - return success but indicate it was already there
        ResponseHelper::success([
            'message' => 'Vehicle already exists in database.',
            'already_exists' => true,
            'vehicle' => [
                'year' => $year,
                'make' => $make,
                'model' => $model,
                'trim' => $trim,
                'front_tire' => $frontTire,
                'rear_tire' => $rearTire
            ]
        ], 200);
    }
    
    // Add vehicle to database
    $result = $vehicleFitment->addFitment([
        'year' => $year,
        'make' => $make,
        'model' => $model,
        'trim' => $trim,
        'front_tire' => $frontTire,
        'rear_tire' => $rearTire,
        'notes' => $notes
    ]);
    
    if ($result) {
        ResponseHelper::success([
            'message' => 'Vehicle added successfully to database.',
            'vehicle' => [
                'year' => $year,
                'make' => $make,
                'model' => $model,
                'trim' => $trim,
                'front_tire' => $frontTire,
                'rear_tire' => $rearTire
            ]
        ], 201);
    } else {
        ResponseHelper::error('Failed to add vehicle to database.', 500);
    }
    
} catch (PDOException $e) {
    error_log("Add vehicle database error: " . $e->getMessage());
    ResponseHelper::error('Database error: Unable to add vehicle. Please try again later.', 500);
} catch (Exception $e) {
    error_log("Add vehicle error: " . $e->getMessage());
    ResponseHelper::error('Failed to add vehicle: ' . $e->getMessage(), 500);
}
