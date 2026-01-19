<?php

namespace App\Services;

use App\Database\Connection;
use PDO;
use Exception;

/**
 * Data Import Service
 * Helps import vehicle fitment and tire data from CSV or other sources
 */
class DataImportService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    /**
     * Import vehicle fitment data from array
     * 
     * @param array $data Array of arrays with keys: year, make, model, trim, front_tire, rear_tire, notes
     * @return array Results with success count and errors
     */
    public function importVehicleFitments(array $data): array
    {
        $success = 0;
        $errors = [];
        
        $sql = "INSERT INTO vehicle_fitment (year, make, model, trim, front_tire, rear_tire, notes) 
                VALUES (:year, :make, :model, :trim, :front_tire, :rear_tire, :notes)
                ON CONFLICT DO NOTHING";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($data as $index => $row) {
            try {
                $stmt->execute([
                    ':year' => (int)$row['year'],
                    ':make' => trim($row['make']),
                    ':model' => trim($row['model']),
                    ':trim' => !empty($row['trim']) ? trim($row['trim']) : null,
                    ':front_tire' => trim($row['front_tire']),
                    ':rear_tire' => !empty($row['rear_tire']) ? trim($row['rear_tire']) : null,
                    ':notes' => $row['notes'] ?? null
                ]);
                
                if ($stmt->rowCount() > 0) {
                    $success++;
                }
            } catch (Exception $e) {
                $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
            }
        }
        
        return [
            'success' => $success,
            'errors' => $errors,
            'total' => count($data)
        ];
    }

    /**
     * Import tire data from array
     * 
     * @param array $data Array of arrays with tire information
     * @return array Results with success count and errors
     */
    public function importTires(array $data): array
    {
        $success = 0;
        $errors = [];
        
        $sql = "INSERT INTO tires (brand, model, tire_size, load_index, speed_rating, season, price, stock, description) 
                VALUES (:brand, :model, :tire_size, :load_index, :speed_rating, :season, :price, :stock, :description)
                ON CONFLICT DO NOTHING";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($data as $index => $row) {
            try {
                $stmt->execute([
                    ':brand' => trim($row['brand']),
                    ':model' => trim($row['model']),
                    ':tire_size' => trim($row['tire_size']),
                    ':load_index' => !empty($row['load_index']) ? trim($row['load_index']) : null,
                    ':speed_rating' => !empty($row['speed_rating']) ? trim($row['speed_rating']) : null,
                    ':season' => $row['season'] ?? 'all-season',
                    ':price' => (float)$row['price'],
                    ':stock' => (int)($row['stock'] ?? 0),
                    ':description' => $row['description'] ?? null
                ]);
                
                if ($stmt->rowCount() > 0) {
                    $success++;
                }
            } catch (Exception $e) {
                $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
            }
        }
        
        return [
            'success' => $success,
            'errors' => $errors,
            'total' => count($data)
        ];
    }

    /**
     * Import from CSV file
     * 
     * @param string $filePath Path to CSV file
     * @param string $type 'vehicles' or 'tires'
     * @return array Results
     */
    public function importFromCSV(string $filePath, string $type = 'vehicles'): array
    {
        if (!file_exists($filePath)) {
            throw new Exception("CSV file not found: {$filePath}");
        }
        
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new Exception("Cannot open CSV file: {$filePath}");
        }
        
        $headers = fgetcsv($handle); // Read header row
        $data = [];
        
        while (($row = fgetcsv($handle)) !== false) {
            $data[] = array_combine($headers, $row);
        }
        
        fclose($handle);
        
        if ($type === 'vehicles') {
            return $this->importVehicleFitments($data);
        } else {
            return $this->importTires($data);
        }
    }
}
