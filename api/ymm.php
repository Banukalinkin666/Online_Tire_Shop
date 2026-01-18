<?php
/**
 * Year/Make/Model API Endpoint
 * 
 * GET /api/ymm.php?action=year|make|model|trim
 * 
 * Examples:
 * - /api/ymm.php?action=year
 * - /api/ymm.php?action=make&year=2020
 * - /api/ymm.php?action=model&year=2020&make=Toyota
 * - /api/ymm.php?action=trim&year=2020&make=Toyota&model=Camry
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Models\VehicleFitment;
use App\Helpers\ResponseHelper;
use App\Helpers\InputHelper;

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

$action = InputHelper::get('action', '');

if (empty($action)) {
    ResponseHelper::error('Action parameter is required. Valid actions: year, make, model, trim');
}

try {
    $fitmentModel = new VehicleFitment();
    $result = [];

    switch ($action) {
        case 'year':
            $result = $fitmentModel->getYears();
            break;

        case 'make':
            $year = InputHelper::sanitizeInt(InputHelper::get('year'));
            if (!$year) {
                ResponseHelper::error('Year parameter is required for make lookup');
            }
            $result = $fitmentModel->getMakes($year);
            break;

        case 'model':
            $year = InputHelper::sanitizeInt(InputHelper::get('year'));
            $make = InputHelper::sanitizeString(InputHelper::get('make'));
            if (!$year || empty($make)) {
                ResponseHelper::error('Year and make parameters are required for model lookup');
            }
            $result = $fitmentModel->getModels($year, $make);
            break;

        case 'trim':
            $year = InputHelper::sanitizeInt(InputHelper::get('year'));
            $make = InputHelper::sanitizeString(InputHelper::get('make'));
            $model = InputHelper::sanitizeString(InputHelper::get('model'));
            if (!$year || empty($make) || empty($model)) {
                ResponseHelper::error('Year, make, and model parameters are required for trim lookup');
            }
            $result = $fitmentModel->getTrims($year, $make, $model);
            break;

        default:
            ResponseHelper::error('Invalid action. Valid actions: year, make, model, trim');
    }

    ResponseHelper::success($result);

} catch (Exception $e) {
    error_log("YMM lookup error: " . $e->getMessage());
    ResponseHelper::error('Failed to retrieve data: ' . $e->getMessage(), 500);
}
