<?php
/**
 * Tire Size Lookup Service
 * Matches tire sizes from local database with fallback logic
 */

require_once __DIR__ . '/Database.php';

class TireLookup
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Find tire sizes for a vehicle
     * 
     * @param int $year
     * @param string $make
     * @param string $model
     * @param string|null $trim
     * @return array|null Tire sizes with verified flag
     */
    public function findTireSizes(int $year, string $make, string $model, ?string $trim = null): ?array
    {
        // Try exact match first (with trim)
        if ($trim) {
            $tireData = $this->findExactMatch($year, $make, $model, $trim);
            if ($tireData) {
                return $tireData;
            }
        }

        // Fallback: match without trim (most common tire size)
        $tireData = $this->findFallbackMatch($year, $make, $model);
        if ($tireData) {
            // Mark as estimated since trim didn't match
            $tireData['verified'] = false;
            return $tireData;
        }

        return null;
    }

    /**
     * Find exact match with trim
     */
    private function findExactMatch(int $year, string $make, string $model, string $trim): ?array
    {
        $sql = "SELECT front_tire, rear_tire, verified 
                FROM tire_specs 
                WHERE year = :year 
                AND LOWER(make) = LOWER(:make) 
                AND LOWER(model) = LOWER(:model) 
                AND LOWER(trim) = LOWER(:trim)
                AND verified = TRUE
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':year' => $year,
            ':make' => $make,
            ':model' => $model,
            ':trim' => $trim
        ]);

        $result = $stmt->fetch();
        return $result ? [
            'front_tire' => $result['front_tire'],
            'rear_tire' => $result['rear_tire'],
            'verified' => (bool)$result['verified']
        ] : null;
    }

    /**
     * Find fallback match (without trim)
     */
    private function findFallbackMatch(int $year, string $make, string $model): ?array
    {
        $sql = "SELECT front_tire, rear_tire, verified 
                FROM tire_specs 
                WHERE year = :year 
                AND LOWER(make) = LOWER(:make) 
                AND LOWER(model) = LOWER(:model) 
                AND trim IS NULL
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':year' => $year,
            ':make' => $make,
            ':model' => $model
        ]);

        $result = $stmt->fetch();
        return $result ? [
            'front_tire' => $result['front_tire'],
            'rear_tire' => $result['rear_tire'],
            'verified' => false // Always false for fallback
        ] : null;
    }

    /**
     * Cache vehicle data from VIN decode
     */
    public function cacheVehicle(array $vehicle): void
    {
        $sql = "INSERT INTO vehicle_cache (vin, year, make, model, trim, body_class) 
                VALUES (:vin, :year, :make, :model, :trim, :body_class)
                ON DUPLICATE KEY UPDATE 
                    year = VALUES(year),
                    make = VALUES(make),
                    model = VALUES(model),
                    trim = VALUES(trim),
                    body_class = VALUES(body_class)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':vin' => $vehicle['vin'],
            ':year' => $vehicle['year'],
            ':make' => $vehicle['make'],
            ':model' => $vehicle['model'],
            ':trim' => $vehicle['trim'],
            ':body_class' => $vehicle['body_class'] ?? null
        ]);
    }

    /**
     * Check if vehicle is cached
     */
    public function getCachedVehicle(string $vin): ?array
    {
        $sql = "SELECT vin, year, make, model, trim, body_class 
                FROM vehicle_cache 
                WHERE vin = :vin 
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':vin' => strtoupper($vin)]);

        $result = $stmt->fetch();
        return $result ? [
            'vin' => $result['vin'],
            'year' => (int)$result['year'],
            'make' => $result['make'],
            'model' => $result['model'],
            'trim' => $result['trim'],
            'body_class' => $result['body_class']
        ] : null;
    }
}
