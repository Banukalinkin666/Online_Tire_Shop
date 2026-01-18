<?php

namespace App\Services;

use App\Models\VehicleFitment;
use App\Models\Tire;

/**
 * Tire Matching Service
 * Handles logic for matching vehicle fitments with available tires
 */
class TireMatchService
{
    private VehicleFitment $fitmentModel;
    private Tire $tireModel;

    public function __construct()
    {
        $this->fitmentModel = new VehicleFitment();
        $this->tireModel = new Tire();
    }

    /**
     * Get matching tires for a vehicle
     * 
     * @param int $year
     * @param string $make
     * @param string $model
     * @param string|null $trim
     * @return array Matching tires organized by position (front/rear)
     */
    public function getMatchingTires(int $year, string $make, string $model, ?string $trim = null): array
    {
        // Get vehicle fitment
        $fitment = $this->fitmentModel->getFitment($year, $make, $model, $trim);

        if (!$fitment) {
            return [
                'success' => false,
                'message' => 'No fitment data found for this vehicle configuration.',
                'vehicle' => compact('year', 'make', 'model', 'trim'),
                'fitment' => null,
                'tires' => []
            ];
        }

        // Extract tire sizes
        $frontSize = $fitment['front_tire'];
        $rearSize = $fitment['rear_tire'] ?: $fitment['front_tire']; // Use front if rear is null

        $isStaggered = ($frontSize !== $rearSize);

        // Find matching tires
        $tireSizes = array_unique([$frontSize, $rearSize]);
        $tiresBySize = $this->tireModel->findBySizes($tireSizes);

        // Organize results
        $result = [
            'success' => true,
            'vehicle' => [
                'year' => (int)$fitment['year'],
                'make' => $fitment['make'],
                'model' => $fitment['model'],
                'trim' => $fitment['trim']
            ],
            'fitment' => [
                'front_tire' => $frontSize,
                'rear_tire' => $rearSize,
                'is_staggered' => $isStaggered,
                'notes' => $fitment['notes']
            ],
            'tires' => [
                'front' => $tiresBySize[$frontSize] ?? [],
                'rear' => $isStaggered ? ($tiresBySize[$rearSize] ?? []) : []
            ]
        ];

        return $result;
    }

    /**
     * Get available trims for a vehicle
     * 
     * @param int $year
     * @param string $make
     * @param string $model
     * @return array
     */
    public function getAvailableTrims(int $year, string $make, string $model): array
    {
        return $this->fitmentModel->getTrims($year, $make, $model);
    }
}
