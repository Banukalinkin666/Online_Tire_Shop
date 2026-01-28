<?php
/**
 * Quick Population Script for 2018-2025
 * 
 * This script automatically runs population for years 2018-2025
 * 
 * Usage: Visit https://your-domain.com/populate-2018-2025.php?key=YOUR_SECRET
 */

// Security check
$importAllowed = $_ENV['IMPORT_ALLOWED'] ?? $_SERVER['IMPORT_ALLOWED'] ?? 'false';
$secretKey = $_GET['key'] ?? '';
$expectedKey = $_ENV['IMPORT_SECRET'] ?? $_SERVER['IMPORT_SECRET'] ?? 'change-this-secret-key';

$allowed = ($importAllowed === 'true' || $secretKey === $expectedKey);

if (!$allowed) {
    http_response_code(403);
    die('Access denied. Set IMPORT_ALLOWED=true in environment variables or provide correct secret key (?key=your-secret)');
}

// Redirect to main script with 2018-2025 parameters
$startYear = 2018;
$endYear = 2025;
$batch = isset($_GET['batch']) ? (int)$_GET['batch'] : 1;
$keyParam = $secretKey !== '' ? '&key=' . urlencode($secretKey) : '';

// Build redirect URL
$redirectUrl = "/populate-ymm-from-nhtsa.php?run=1&start_year={$startYear}&end_year={$endYear}&batch={$batch}{$keyParam}";

// Auto-start the population
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Populate 2018-2025 - Auto Starting...</title>
    <meta charset="utf-8">
    <meta http-equiv="refresh" content="1;url=<?php echo htmlspecialchars($redirectUrl); ?>">
</head>
<body style="font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f0f0f0;">
    <div style="background: white; padding: 30px; border-radius: 8px; max-width: 600px; margin: 0 auto; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h1 style="color: #333; margin-bottom: 20px;">🚀 Starting Population for 2018-2025</h1>
        <p style="color: #666; margin-bottom: 20px;">Redirecting to population script...</p>
        <p style="color: #999; font-size: 14px;">If you are not redirected automatically, <a href="<?php echo htmlspecialchars($redirectUrl); ?>">click here</a>.</p>
        <div style="margin-top: 30px; padding: 15px; background: #e3f2fd; border-radius: 5px; color: #1976d2;">
            <strong>Year Range:</strong> 2018 - 2025<br>
            <strong>Batch Size:</strong> <?php echo $batch; ?> make(s) per request<br>
            <strong>Status:</strong> Auto-starting...
        </div>
    </div>
    <script>
        // Immediate redirect (don't wait for meta refresh)
        setTimeout(function() {
            window.location.href = <?php echo json_encode($redirectUrl); ?>;
        }, 500);
    </script>
</body>
</html>
