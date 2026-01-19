<?php
/**
 * VIN Debug Tool
 * Helps diagnose why a VIN is not showing results
 * 
 * Usage: /debug-vin.php?vin=19XFC2F59GE123456
 * 
 * DELETE THIS FILE AFTER DEBUGGING FOR SECURITY!
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Services\NHTSAService;
use App\Services\TireMatchService;
use App\Helpers\InputHelper;
use App\Models\VehicleFitment;

header('Content-Type: text/html; charset=utf-8');

$vin = $_GET['vin'] ?? '19XFC2F59GE123456';
$vin = InputHelper::sanitizeString($vin);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VIN Debug Tool</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">VIN Debug Tool</h1>
        
        <form method="GET" class="mb-6">
            <div class="flex gap-2">
                <input 
                    type="text" 
                    name="vin" 
                    value="<?php echo htmlspecialchars($vin); ?>" 
                    placeholder="Enter VIN"
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-md"
                    maxlength="17"
                >
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                    Test VIN
                </button>
            </div>
        </form>

        <?php if ($vin): ?>
            <div class="bg-white rounded-lg shadow-md p-6 space-y-6">
                <h2 class="text-xl font-bold">Testing VIN: <?php echo htmlspecialchars($vin); ?></h2>
                
                <!-- Step 1: VIN Validation -->
                <div class="border-l-4 border-blue-500 pl-4">
                    <h3 class="font-bold text-lg mb-2">Step 1: VIN Validation</h3>
                    <?php
                    $isValid = InputHelper::validateVIN($vin);
                    $length = strlen($vin);
                    $pattern = '/^[A-HJ-NPR-Z0-9]{17}$/';
                    $patternMatch = preg_match($pattern, strtoupper(trim($vin)));
                    ?>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Length: <?php echo $length; ?> characters <?php echo $length === 17 ? '✅' : '❌'; ?></li>
                        <li>Pattern Match: <?php echo $patternMatch ? '✅ PASS' : '❌ FAIL'; ?></li>
                        <li>Overall Validation: <strong><?php echo $isValid ? '✅ PASS' : '❌ FAIL'; ?></strong></li>
                    </ul>
                </div>

                <!-- Step 2: NHTSA API Decode -->
                <div class="border-l-4 border-blue-500 pl-4">
                    <h3 class="font-bold text-lg mb-2">Step 2: NHTSA API Decode</h3>
                    <?php
                    try {
                        $nhtsaService = new NHTSAService();
                        $vehicleInfo = $nhtsaService->decodeVIN($vin);
                        ?>
                        <div class="bg-green-50 p-4 rounded mb-2">
                            <p class="text-green-800 font-bold">✅ VIN Decoded Successfully</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded">
                            <table class="w-full">
                                <tr><td class="font-bold pr-4">Year:</td><td><?php echo htmlspecialchars($vehicleInfo['year'] ?? 'N/A'); ?></td></tr>
                                <tr><td class="font-bold pr-4">Make:</td><td><?php echo htmlspecialchars($vehicleInfo['make'] ?? 'N/A'); ?></td></tr>
                                <tr><td class="font-bold pr-4">Model:</td><td><?php echo htmlspecialchars($vehicleInfo['model'] ?? 'N/A'); ?></td></tr>
                                <tr><td class="font-bold pr-4">Trim:</td><td><?php echo htmlspecialchars($vehicleInfo['trim'] ?? 'N/A'); ?></td></tr>
                                <tr><td class="font-bold pr-4">Body Class:</td><td><?php echo htmlspecialchars($vehicleInfo['body_class'] ?? 'N/A'); ?></td></tr>
                                <tr><td class="font-bold pr-4">Drive Type:</td><td><?php echo htmlspecialchars($vehicleInfo['drive_type'] ?? 'N/A'); ?></td></tr>
                            </table>
                        </div>
                        <?php
                    } catch (Exception $e) {
                        ?>
                        <div class="bg-red-50 p-4 rounded">
                            <p class="text-red-800 font-bold">❌ VIN Decode Failed</p>
                            <p class="text-red-700 mt-2">Error: <?php echo htmlspecialchars($e->getMessage()); ?></p>
                        </div>
                        <?php
                        $vehicleInfo = null;
                    }
                    ?>
                </div>

                <?php if (isset($vehicleInfo) && $vehicleInfo): ?>
                    <!-- Step 3: Database Lookup -->
                    <div class="border-l-4 border-blue-500 pl-4">
                        <h3 class="font-bold text-lg mb-2">Step 3: Database Lookup</h3>
                        <?php
                        $year = $vehicleInfo['year'] ?? null;
                        $make = $vehicleInfo['make'] ?? '';
                        $model = $vehicleInfo['model'] ?? '';
                        
                        if ($year && $make && $model) {
                            $vehicleFitment = new VehicleFitment();
                            
                            // Check if vehicle exists in database
                            $fitments = $vehicleFitment->getFitment($year, $make, $model, null);
                            
                            if (!empty($fitments)) {
                                ?>
                                <div class="bg-green-50 p-4 rounded mb-2">
                                    <p class="text-green-800 font-bold">✅ Vehicle Found in Database</p>
                                    <p class="text-sm text-green-700 mt-1">Found <?php echo count($fitments); ?> fitment(s)</p>
                                </div>
                                <div class="bg-gray-50 p-4 rounded mb-2">
                                    <h4 class="font-bold mb-2">Available Fitments:</h4>
                                    <ul class="list-disc list-inside space-y-1">
                                        <?php foreach ($fitments as $fitment): ?>
                                            <li>
                                                <?php echo htmlspecialchars($fitment['year']); ?> 
                                                <?php echo htmlspecialchars($fitment['make']); ?> 
                                                <?php echo htmlspecialchars($fitment['model']); ?>
                                                <?php if ($fitment['trim']): ?>
                                                    - <?php echo htmlspecialchars($fitment['trim']); ?>
                                                <?php endif; ?>
                                                (<?php echo htmlspecialchars($fitment['front_tire']); ?>)
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php
                                
                                // Get available trims
                                $trims = $vehicleFitment->getTrims($year, $make, $model);
                                if (!empty($trims)) {
                                    ?>
                                    <div class="bg-gray-50 p-4 rounded">
                                        <h4 class="font-bold mb-2">Available Trims:</h4>
                                        <ul class="list-disc list-inside">
                                            <?php foreach ($trims as $trim): ?>
                                                <li><?php echo htmlspecialchars($trim); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <?php
                                }
                            } else {
                                ?>
                                <div class="bg-yellow-50 p-4 rounded mb-2">
                                    <p class="text-yellow-800 font-bold">⚠️ Vehicle NOT Found in Database</p>
                                    <p class="text-sm text-yellow-700 mt-1">
                                        The VIN decoded successfully, but this vehicle (<?php echo htmlspecialchars("$year $make $model"); ?>) 
                                        is not in your database.
                                    </p>
                                </div>
                                
                                <!-- Check for similar vehicles -->
                                <div class="bg-gray-50 p-4 rounded">
                                    <h4 class="font-bold mb-2">Checking for Similar Vehicles:</h4>
                                    <?php
                                    // Check same make/model, different year
                                    $similar = $vehicleFitment->getModels($year, $make);
                                    if (!empty($similar)) {
                                        echo "<p>Found models for $make in $year: " . implode(', ', array_slice($similar, 0, 5)) . "</p>";
                                    } else {
                                        // Check if make exists
                                        $makes = $vehicleFitment->getMakes($year);
                                        if (in_array($make, $makes)) {
                                            echo "<p>Make '$make' exists for year $year, but model '$model' not found.</p>";
                                        } else {
                                            echo "<p>Make '$make' not found for year $year.</p>";
                                        }
                                    }
                                    ?>
                                </div>
                                <?php
                            }
                        } else {
                            ?>
                            <div class="bg-red-50 p-4 rounded">
                                <p class="text-red-800 font-bold">❌ Missing Vehicle Information</p>
                                <p class="text-red-700 mt-2">
                                    Cannot search database: Year, Make, or Model is missing from VIN decode.
                                </p>
                            </div>
                            <?php
                        }
                        ?>
                    </div>

                    <!-- Step 4: Tire Matching -->
                    <?php if (!empty($fitments) ?? false): ?>
                        <div class="border-l-4 border-blue-500 pl-4">
                            <h3 class="font-bold text-lg mb-2">Step 4: Tire Matching</h3>
                            <?php
                            try {
                                $tireMatchService = new TireMatchService();
                                $tireResults = $tireMatchService->getMatchingTires($year, strtolower($make), strtolower($model), null);
                                
                                if (!empty($tireResults['front']) || !empty($tireResults['rear'])) {
                                    ?>
                                    <div class="bg-green-50 p-4 rounded mb-2">
                                        <p class="text-green-800 font-bold">✅ Tires Found</p>
                                    </div>
                                    <div class="bg-gray-50 p-4 rounded">
                                        <p><strong>Front Tires:</strong> <?php echo count($tireResults['front'] ?? []); ?> found</p>
                                        <p><strong>Rear Tires:</strong> <?php echo count($tireResults['rear'] ?? []); ?> found</p>
                                    </div>
                                    <?php
                                } else {
                                    ?>
                                    <div class="bg-yellow-50 p-4 rounded">
                                        <p class="text-yellow-800 font-bold">⚠️ No Tires Found</p>
                                        <p class="text-sm text-yellow-700 mt-1">
                                            Vehicle found in database, but no matching tires in inventory.
                                        </p>
                                    </div>
                                    <?php
                                }
                            } catch (Exception $e) {
                                ?>
                                <div class="bg-red-50 p-4 rounded">
                                    <p class="text-red-800 font-bold">❌ Tire Matching Error</p>
                                    <p class="text-red-700 mt-2"><?php echo htmlspecialchars($e->getMessage()); ?></p>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Recommendations -->
                <div class="border-l-4 border-yellow-500 pl-4 bg-yellow-50 p-4 rounded">
                    <h3 class="font-bold text-lg mb-2">Recommendations</h3>
                    <ul class="list-disc list-inside space-y-1">
                        <?php if (!isset($vehicleInfo) || !$vehicleInfo): ?>
                            <li>VIN decode failed - check if VIN is valid and NHTSA API is accessible</li>
                        <?php elseif (empty($fitments)): ?>
                            <li>Add this vehicle to your database: <code><?php echo htmlspecialchars("$year $make $model"); ?></code></li>
                            <li>Import production data if not already done: <code>sql/production_data.sql</code></li>
                        <?php elseif (empty($tireResults['front'] ?? []) && empty($tireResults['rear'] ?? [])): ?>
                            <li>Add tires matching the vehicle's tire size to your inventory</li>
                        <?php else: ?>
                            <li>Everything looks good! The VIN should work in your application.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <div class="mt-6 bg-red-50 border-l-4 border-red-400 p-4">
            <p class="text-sm text-red-700">
                <strong>⚠️ Security Note:</strong> Delete this file after debugging!
            </p>
        </div>
    </div>
</body>
</html>
