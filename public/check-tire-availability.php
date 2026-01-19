<?php
/**
 * Tire Availability Checker
 * Helps diagnose why tires aren't showing for a vehicle
 * 
 * Usage: /check-tire-availability.php?year=2015&make=Toyota&model=RAV4
 * 
 * DELETE THIS FILE AFTER DEBUGGING FOR SECURITY!
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Models\VehicleFitment;
use App\Models\Tire;

header('Content-Type: text/html; charset=utf-8');

$year = $_GET['year'] ?? '2015';
$make = $_GET['make'] ?? 'Toyota';
$model = $_GET['model'] ?? 'RAV4';
$trim = $_GET['trim'] ?? null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tire Availability Checker</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Tire Availability Checker</h1>
        
        <form method="GET" class="mb-6 bg-white p-4 rounded-lg shadow">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Year</label>
                    <input type="number" name="year" value="<?php echo htmlspecialchars($year); ?>" class="w-full px-3 py-2 border rounded">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Make</label>
                    <input type="text" name="make" value="<?php echo htmlspecialchars($make); ?>" class="w-full px-3 py-2 border rounded">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Model</label>
                    <input type="text" name="model" value="<?php echo htmlspecialchars($model); ?>" class="w-full px-3 py-2 border rounded">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Trim (Optional)</label>
                    <input type="text" name="trim" value="<?php echo htmlspecialchars($trim ?? ''); ?>" class="w-full px-3 py-2 border rounded">
                </div>
            </div>
            <button type="submit" class="mt-4 bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                Check Availability
            </button>
        </form>

        <?php
        try {
            $vehicleFitment = new VehicleFitment();
            $tireModel = new Tire();
            
            // Step 1: Check if vehicle exists
            ?>
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-bold mb-4">Step 1: Vehicle Lookup</h2>
                <?php
                $fitment = $vehicleFitment->getFitment((int)$year, $make, $model, $trim);
                
                if ($fitment) {
                    ?>
                    <div class="bg-green-50 p-4 rounded mb-4">
                        <p class="text-green-800 font-bold">✅ Vehicle Found in Database</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded">
                        <table class="w-full">
                            <tr><td class="font-bold pr-4">Year:</td><td><?php echo htmlspecialchars($fitment['year']); ?></td></tr>
                            <tr><td class="font-bold pr-4">Make:</td><td><?php echo htmlspecialchars($fitment['make']); ?></td></tr>
                            <tr><td class="font-bold pr-4">Model:</td><td><?php echo htmlspecialchars($fitment['model']); ?></td></tr>
                            <?php if ($fitment['trim']): ?>
                                <tr><td class="font-bold pr-4">Trim:</td><td><?php echo htmlspecialchars($fitment['trim']); ?></td></tr>
                            <?php endif; ?>
                            <tr><td class="font-bold pr-4">Front Tire:</td><td class="font-mono text-lg"><?php echo htmlspecialchars($fitment['front_tire']); ?></td></tr>
                            <?php if ($fitment['rear_tire']): ?>
                                <tr><td class="font-bold pr-4">Rear Tire:</td><td class="font-mono text-lg"><?php echo htmlspecialchars($fitment['rear_tire']); ?></td></tr>
                            <?php endif; ?>
                        </table>
                    </div>
                    <?php
                    
                    // Step 2: Check tire availability
                    $frontSize = $fitment['front_tire'];
                    $rearSize = $fitment['rear_tire'] ?: $fitment['front_tire'];
                    $sizesToCheck = array_unique([$frontSize, $rearSize]);
                    
                    ?>
                    <h2 class="text-xl font-bold mb-4 mt-6">Step 2: Tire Inventory Check</h2>
                    <?php
                    
                    foreach ($sizesToCheck as $size) {
                        ?>
                        <div class="mb-4">
                            <h3 class="font-bold text-lg mb-2">Tire Size: <span class="font-mono"><?php echo htmlspecialchars($size); ?></span></h3>
                            <?php
                            
                            // Check all tires (including out of stock)
                            $allTires = $tireModel->findBySize($size);
                            
                            // Check in-stock tires only
                            $db = \App\Database\Connection::getInstance();
                            $stmt = $db->prepare("SELECT * FROM tires WHERE tire_size = :size AND stock > 0 ORDER BY brand ASC");
                            $stmt->execute([':size' => $size]);
                            $inStockTires = $stmt->fetchAll();
                            
                            // Check out of stock
                            $stmt = $db->prepare("SELECT * FROM tires WHERE tire_size = :size AND stock = 0 ORDER BY brand ASC");
                            $stmt->execute([':size' => $size]);
                            $outOfStockTires = $stmt->fetchAll();
                            
                            if (empty($allTires)) {
                                ?>
                                <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
                                    <p class="text-red-800 font-bold">❌ No Tires Found for This Size</p>
                                    <p class="text-sm text-red-700 mt-2">
                                        There are <strong>no tires</strong> in the database with size <code><?php echo htmlspecialchars($size); ?></code>
                                    </p>
                                    <p class="text-sm text-red-700 mt-2">
                                        <strong>Solution:</strong> Add tires to inventory with this size.
                                    </p>
                                </div>
                                <?php
                            } elseif (empty($inStockTires)) {
                                ?>
                                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                                    <p class="text-yellow-800 font-bold">⚠️ Tires Exist But Out of Stock</p>
                                    <p class="text-sm text-yellow-700 mt-2">
                                        Found <strong><?php echo count($outOfStockTires); ?> tire(s)</strong> with size <code><?php echo htmlspecialchars($size); ?></code> but all have <code>stock = 0</code>
                                    </p>
                                    <div class="mt-3">
                                        <p class="text-sm font-semibold mb-2">Out of Stock Tires:</p>
                                        <ul class="list-disc list-inside space-y-1 text-sm">
                                            <?php foreach ($outOfStockTires as $tire): ?>
                                                <li><?php echo htmlspecialchars($tire['brand'] . ' ' . $tire['model']); ?> - Stock: <?php echo $tire['stock']; ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <p class="text-sm text-yellow-700 mt-2">
                                        <strong>Solution:</strong> Update stock levels for these tires.
                                    </p>
                                </div>
                                <?php
                            } else {
                                ?>
                                <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded">
                                    <p class="text-green-800 font-bold">✅ Tires Available in Stock</p>
                                    <p class="text-sm text-green-700 mt-2">
                                        Found <strong><?php echo count($inStockTires); ?> tire(s)</strong> with size <code><?php echo htmlspecialchars($size); ?></code> and stock > 0
                                    </p>
                                    <div class="mt-3">
                                        <table class="w-full text-sm">
                                            <thead class="bg-gray-100">
                                                <tr>
                                                    <th class="text-left p-2">Brand</th>
                                                    <th class="text-left p-2">Model</th>
                                                    <th class="text-left p-2">Price</th>
                                                    <th class="text-left p-2">Stock</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($inStockTires as $tire): ?>
                                                    <tr class="border-b">
                                                        <td class="p-2"><?php echo htmlspecialchars($tire['brand']); ?></td>
                                                        <td class="p-2"><?php echo htmlspecialchars($tire['model']); ?></td>
                                                        <td class="p-2">$<?php echo number_format($tire['price'], 2); ?></td>
                                                        <td class="p-2">
                                                            <span class="<?php echo $tire['stock'] > 10 ? 'text-green-600' : 'text-yellow-600'; ?> font-bold">
                                                                <?php echo $tire['stock']; ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    }
                    
                } else {
                    ?>
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
                        <p class="text-red-800 font-bold">❌ Vehicle Not Found in Database</p>
                        <p class="text-red-700 mt-2">
                            No vehicle found matching: <?php echo htmlspecialchars("$year $make $model" . ($trim ? " $trim" : "")); ?>
                        </p>
                        <p class="text-sm text-red-700 mt-2">
                            <strong>Solution:</strong> Add this vehicle to the database first.
                        </p>
                    </div>
                    <?php
                }
                ?>
            </div>
            
            <!-- Database Statistics -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Database Statistics</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <?php
                    $db = \App\Database\Connection::getInstance();
                    
                    // Total vehicles
                    $stmt = $db->query("SELECT COUNT(*) FROM vehicle_fitment");
                    $totalVehicles = $stmt->fetchColumn();
                    
                    // Total tires
                    $stmt = $db->query("SELECT COUNT(*) FROM tires");
                    $totalTires = $stmt->fetchColumn();
                    
                    // Tires in stock
                    $stmt = $db->query("SELECT COUNT(*) FROM tires WHERE stock > 0");
                    $tiresInStock = $stmt->fetchColumn();
                    
                    // Unique tire sizes
                    $stmt = $db->query("SELECT COUNT(DISTINCT tire_size) FROM tires WHERE stock > 0");
                    $uniqueSizes = $stmt->fetchColumn();
                    ?>
                    <div class="bg-blue-50 p-4 rounded">
                        <p class="text-sm text-gray-600">Total Vehicles</p>
                        <p class="text-2xl font-bold"><?php echo number_format($totalVehicles); ?></p>
                    </div>
                    <div class="bg-green-50 p-4 rounded">
                        <p class="text-sm text-gray-600">Tires in Stock</p>
                        <p class="text-2xl font-bold"><?php echo number_format($tiresInStock); ?></p>
                        <p class="text-xs text-gray-500">of <?php echo number_format($totalTires); ?> total</p>
                    </div>
                    <div class="bg-purple-50 p-4 rounded">
                        <p class="text-sm text-gray-600">Unique Tire Sizes</p>
                        <p class="text-2xl font-bold"><?php echo number_format($uniqueSizes); ?></p>
                        <p class="text-xs text-gray-500">with stock > 0</p>
                    </div>
                </div>
            </div>
            
        <?php
        } catch (Exception $e) {
            ?>
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
                <p class="text-red-800 font-bold">❌ Error</p>
                <p class="text-red-700 mt-2"><?php echo htmlspecialchars($e->getMessage()); ?></p>
            </div>
            <?php
        }
        ?>
        
        <div class="mt-6 bg-red-50 border-l-4 border-red-400 p-4">
            <p class="text-sm text-red-700">
                <strong>⚠️ Security Note:</strong> Delete this file after debugging!
            </p>
        </div>
    </div>
</body>
</html>
