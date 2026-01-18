<?php

namespace App\Models;

use App\Database\Connection;
use PDO;

/**
 * Tire Model
 * Handles tire inventory operations
 */
class Tire
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    /**
     * Find tires by exact tire size match
     * 
     * @param string $tireSize
     * @return array
     */
    public function findBySize(string $tireSize): array
    {
        $sql = "SELECT * FROM tires 
                WHERE tire_size = :tire_size 
                AND stock > 0
                ORDER BY brand ASC, price ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tire_size' => $tireSize]);

        return $stmt->fetchAll();
    }

    /**
     * Find tires by multiple sizes (for staggered setups)
     * 
     * @param array $tireSizes Array of tire sizes
     * @return array Array keyed by tire size
     */
    public function findBySizes(array $tireSizes): array
    {
        if (empty($tireSizes)) {
            return [];
        }

        $placeholders = [];
        foreach ($tireSizes as $index => $size) {
            $placeholders[] = ":size{$index}";
        }

        $sql = "SELECT * FROM tires 
                WHERE tire_size IN (" . implode(',', $placeholders) . ")
                AND stock > 0
                ORDER BY tire_size ASC, brand ASC, price ASC";

        $params = [];
        foreach ($tireSizes as $index => $size) {
            $params[":size{$index}"] = $size;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $results = [];
        foreach ($stmt->fetchAll() as $tire) {
            $size = $tire['tire_size'];
            if (!isset($results[$size])) {
                $results[$size] = [];
            }
            $results[$size][] = $tire;
        }

        return $results;
    }

    /**
     * Get tire by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM tires WHERE id = :id LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get all available tire sizes
     * 
     * @return array
     */
    public function getAllSizes(): array
    {
        $sql = "SELECT DISTINCT tire_size 
                FROM tires 
                WHERE stock > 0
                ORDER BY tire_size ASC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
