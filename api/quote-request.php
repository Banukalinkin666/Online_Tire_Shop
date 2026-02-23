<?php
/**
 * Quote Request API Endpoint
 *
 * POST /api/quote-request.php
 * Body: { "fullName": "", "email": "", "phone": "", "message": "", "vehicle": "", "frontTire": "", "rearTire": "" }
 */

error_reporting(0);
ini_set('display_errors', '0');
ob_start();

require_once __DIR__ . '/../app/bootstrap.php';

use App\Helpers\ResponseHelper;
use App\Helpers\InputHelper;

ob_end_clean();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ResponseHelper::error('Method not allowed. Use POST.', 405);
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    ResponseHelper::error('Invalid JSON input');
}

$fullName = InputHelper::sanitizeString($input['fullName'] ?? '');
$email = InputHelper::sanitizeString($input['email'] ?? '');
$phone = InputHelper::sanitizeString($input['phone'] ?? '');
$message = InputHelper::sanitizeString($input['message'] ?? '');
$vehicle = InputHelper::sanitizeString($input['vehicle'] ?? '');
$frontTire = InputHelper::sanitizeString($input['frontTire'] ?? '');
$rearTire = InputHelper::sanitizeString($input['rearTire'] ?? '');

$errors = [];

if ($fullName === '') {
    $errors['fullName'] = 'Full name is required.';
}
if ($email === '') {
    $errors['email'] = 'Email is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address.';
}

if (!empty($errors)) {
    ResponseHelper::error('Validation failed', 400, $errors);
}

$payload = [
    'fullName' => $fullName,
    'email' => $email,
    'phone' => $phone,
    'message' => $message,
    'vehicle' => $vehicle,
    'frontTire' => $frontTire,
    'rearTire' => $rearTire,
    'submittedAt' => date('c'),
];

$dataDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data';
if (!is_dir($dataDir)) {
    @mkdir($dataDir, 0755, true);
}
$file = $dataDir . DIRECTORY_SEPARATOR . 'quote-requests.json';
if (is_dir($dataDir) && is_writable($dataDir)) {
    $existing = [];
    if (file_exists($file) && is_readable($file)) {
        $content = file_get_contents($file);
        if ($content !== false) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $existing = $decoded;
            }
        }
    }
    $existing[] = $payload;
    @file_put_contents($file, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

ResponseHelper::success(['message' => 'Quote request received. We will get back to you soon.']);
