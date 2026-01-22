<?php

namespace App\Models;

use App\Database\Connection;
use PDO;
use PDOException;

/**
 * Vehicle Cache Model
 * Caches decoded VIN data to reduce NHTSA API calls
 */
class VehicleCache
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    /**
     * Get cached vehicle data by VIN
     * 
     * @param string $vin
     * @return array|null
     */
    public function getCachedVehicle(string $vin): ?array
    {
        try {
            $sql = "SELECT vin, year, make, model, trim, body_class, created_at 
                    FROM vehicle_cache 
                    WHERE vin = :vin 
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':vin' => strtoupper($vin)]);

            $result = $stmt->fetch();
            return $result ? [
                'year' => (int)$result['year'],
                'make' => $result['make'],
                'model' => $result['model'],
                'trim' => $result['trim'],
                'body_class' => $result['body_class']
            ] : null;
        } catch (PDOException $e) {
            // Table might not exist yet - that's okay, just return null
            // The system will work without caching
            error_log("Vehicle cache table not available (getCachedVehicle): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Cache vehicle data from VIN decode
     * 
     * @param string $vin
     * @param array $vehicleData
     * @return bool
     */
    public function cacheVehicle(string $vin, array $vehicleData): bool
    {
        // Check if table exists (for backward compatibility)
        try {
            // Detect database type
            $dbType = $_ENV['DB_TYPE'] ?? $_SERVER['DB_TYPE'] ?? 'mysql';
            
            if ($dbType === 'pgsql' || $dbType === 'postgresql') {
                // PostgreSQL syntax
                $sql = "INSERT INTO vehicle_cache (vin, year, make, model, trim, body_class) 
                        VALUES (:vin, :year, :make, :model, :trim, :body_class)
                        ON CONFLICT (vin) DO UPDATE 
                            SET year = EXCLUDED.year,
                                make = EXCLUDED.make,
                                model = EXCLUDED.model,
                                trim = EXCLUDED.trim,
                                body_class = EXCLUDED.body_class";
            } else {
                // MySQL syntax
                $sql = "INSERT INTO vehicle_cache (vin, year, make, model, trim, body_class) 
                        VALUES (:vin, :year, :make, :model, :trim, :body_class)
                        ON DUPLICATE KEY UPDATE 
                            year = VALUES(year),
                            make = VALUES(make),
                            model = VALUES(model),
                            trim = VALUES(trim),
                            body_class = VALUES(body_class)";
            }

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':vin' => strtoupper($vin),
                ':year' => $vehicleData['year'],
                ':make' => $vehicleData['make'],
                ':model' => $vehicleData['model'],
                ':trim' => $vehicleData['trim'] ?? null,
                ':body_class' => $vehicleData['body_class'] ?? null
            ]);
        } catch (PDOException $e) {
            // Table might not exist yet - that's okay, just log it
            error_log("Vehicle cache table not available: " . $e->getMessage());
            return false;
        }
    }
}
