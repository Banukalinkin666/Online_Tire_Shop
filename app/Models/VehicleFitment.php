<?php

namespace App\Models;

use App\Database\Connection;
use PDO;

/**
 * Vehicle Fitment Model
 * Handles vehicle fitment data operations
 */
class VehicleFitment
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    /**
     * Get fitment by Year, Make, Model, and optional Trim
     * 
     * @param int $year
     * @param string $make
     * @param string $model
     * @param string|null $trim
     * @return array|null
     */
    public function getFitment(int $year, string $make, string $model, ?string $trim = null): ?array
    {
        $sql = "SELECT * FROM vehicle_fitment 
                WHERE year = :year 
                AND make = :make 
                AND model = :model";
        
        $params = [
            ':year' => $year,
            ':make' => $make,
            ':model' => $model
        ];

        if ($trim !== null && $trim !== '') {
            $sql .= " AND trim = :trim";
            $params[':trim'] = $trim;
        }

        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get all available trims for a Year/Make/Model combination
     * 
     * @param int $year
     * @param string $make
     * @param string $model
     * @return array
     */
    public function getTrims(int $year, string $make, string $model): array
    {
        $sql = "SELECT DISTINCT trim 
                FROM vehicle_fitment 
                WHERE year = :year 
                AND make = :make 
                AND model = :model 
                AND trim IS NOT NULL 
                AND trim != ''
                ORDER BY trim ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':year' => $year,
            ':make' => $make,
            ':model' => $model
        ]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get all makes for a given year
     * 
     * @param int $year
     * @return array
     */
    public function getMakes(int $year): array
    {
        $sql = "SELECT DISTINCT make 
                FROM vehicle_fitment 
                WHERE year = :year 
                ORDER BY make ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':year' => $year]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get all models for a Year/Make combination
     * 
     * @param int $year
     * @param string $make
     * @return array
     */
    public function getModels(int $year, string $make): array
    {
        $sql = "SELECT DISTINCT model 
                FROM vehicle_fitment 
                WHERE year = :year 
                AND make = :make 
                ORDER BY model ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':year' => $year,
            ':make' => $make
        ]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get all available years
     * 
     * @return array
     */
    public function getYears(): array
    {
        $sql = "SELECT DISTINCT year 
                FROM vehicle_fitment 
                ORDER BY year DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
