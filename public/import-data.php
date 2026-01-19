<?php
/**
 * Data Import Tool
 * 
 * Access this file to import additional vehicle and tire data
 * DELETE THIS FILE AFTER IMPORTING FOR SECURITY!
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Services\DataImportService;
use App\Helpers\InputHelper;

// Security: Only allow with secret key
$allowed = false;
$secretKey = $_GET['key'] ?? '';
$expectedKey = $_ENV['IMPORT_SECRET'] ?? $_SERVER['IMPORT_SECRET'] ?? 'change-this-secret-key';
$importAllowed = $_ENV['IMPORT_ALLOWED'] ?? $_SERVER['IMPORT_ALLOWED'] ?? 'false';

if ($secretKey === $expectedKey || $importAllowed === 'true') {
    $allowed = true;
}

if (!$allowed) {
    http_response_code(403);
    die('Access denied. Set IMPORT_SECRET in environment variables or IMPORT_ALLOWED=true');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Import Tool</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Data Import Tool</h1>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_type'])) {
            try {
                $importService = new DataImportService();
                $importType = $_POST['import_type'];
                
                if ($importType === 'comprehensive') {
                    // Import comprehensive vehicle data
                    $sqlFile = __DIR__ . '/../sql/import_vehicle_data.sql';
                    if (file_exists($sqlFile)) {
                        $sql = file_get_contents($sqlFile);
                        
                        // Execute SQL
                        $db = \App\Database\Connection::getInstance();
                        $db->exec($sql);
                        
                        echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">';
                        echo '<strong>Success!</strong> Comprehensive vehicle data imported.';
                        echo '</div>';
                    } else {
                        throw new Exception("SQL file not found: {$sqlFile}");
                    }
                } else {
                    echo '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">';
                    echo 'Other import types coming soon.';
                    echo '</div>';
                }
            } catch (Exception $e) {
                echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">';
                echo '<strong>Error:</strong> ' . htmlspecialchars($e->getMessage());
                echo '</div>';
            }
        }
        ?>
        
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">Import Options</h2>
            
            <form method="POST">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Import Type
                    </label>
                    <select name="import_type" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                        <option value="comprehensive">Comprehensive Vehicle Data (100+ vehicles)</option>
                    </select>
                </div>
                
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                    Import Data
                </button>
            </form>
        </div>
        
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <p class="text-sm text-yellow-700">
                <strong>⚠️ Important:</strong> Delete this file after importing for security!
            </p>
        </div>
    </div>
</body>
</html>
