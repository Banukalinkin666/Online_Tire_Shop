<?php
/**
 * Health Check Endpoint
 * Used by Docker and Render to verify service is running
 */

header('Content-Type: application/json');
http_response_code(200);

// Basic health check - can be extended to check database connection
$status = [
    'status' => 'ok',
    'timestamp' => date('c'),
    'service' => 'tire-fitment-finder'
];

// Optional: Check database connection
try {
    require_once __DIR__ . '/../app/bootstrap.php';
    $db = App\Database\Connection::getInstance();
    $db->query('SELECT 1');
    $status['database'] = 'connected';
} catch (Exception $e) {
    $status['database'] = 'disconnected';
    // Don't fail health check if DB is down during startup
}

echo json_encode($status, JSON_PRETTY_PRINT);
