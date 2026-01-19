<?php
/**
 * Tire Matching API Endpoint
 * 
 * GET /api/tires.php?year=2020&make=Toyota&model=Camry&trim=LE
 */

// Suppress ALL output and errors before JSON
error_reporting(0);
ini_set('display_errors', '0');
ob_start();

require_once __DIR__ . '/../app/bootstrap.php';

use App\Services\TireMatchService;
use App\Helpers\ResponseHelper;
use App\Helpers\InputHelper;
use PDOException;

// Clear ALL output buffer and set JSON headers
ob_end_clean();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    ResponseHelper::error('Method not allowed. Use GET.', 405);
}

// Get and validate parameters
$year = InputHelper::sanitizeInt(InputHelper::get('year'));
$make = InputHelper::sanitizeString(InputHelper::get('make') ?? '');
$model = InputHelper::sanitizeString(InputHelper::get('model') ?? '');
$trim = InputHelper::sanitizeString(InputHelper::get('trim') ?? '');

// Validate required fields
$errors = [];

if (!$year) {
    $errors[] = 'Year is required and must be a valid year (1900-2100)';
}

if (empty($make)) {
    $errors[] = 'Make is required';
}

if (empty($model)) {
    $errors[] = 'Model is required';
}

if (!empty($errors)) {
    ResponseHelper::error('Validation failed', 400, $errors);
}

// Normalize trim (empty string becomes null)
$trim = empty($trim) ? null : $trim;

try {
    $tireMatchService = new TireMatchService();
    $result = $tireMatchService->getMatchingTires($year, $make, $model, $trim);

    if (!$result['success']) {
        // Return vehicle info so frontend can offer to add it
        ResponseHelper::error($result['message'], 404, [
            'vehicle_not_found' => true,
            'vehicle' => [
                'year' => $year,
                'make' => $make,
                'model' => $model,
                'trim' => $trim
            ]
        ]);
    }

    ResponseHelper::success($result);

} catch (PDOException $e) {
    error_log("Tire matching database error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    // Ensure no output before JSON
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    ResponseHelper::error('Database error: Unable to retrieve tire matches. Please try again later.', 500);
} catch (Exception $e) {
    error_log("Tire matching error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    // Ensure no output before JSON
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    ResponseHelper::error('Failed to retrieve tire matches: ' . $e->getMessage(), 500);
} catch (Throwable $e) {
    error_log("Fatal error: " . $e->getMessage());
    // Ensure no output before JSON
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    ResponseHelper::error('An unexpected error occurred. Please try again later.', 500);
}
