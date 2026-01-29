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
    private ?AITireSizeService $aiService = null;

    public function __construct()
    {
        $this->fitmentModel = new VehicleFitment();
        $this->tireModel = new Tire();
        
        // Initialize AI service (will be null if not available)
        try {
            $this->aiService = new AITireSizeService();
            if (!$this->aiService->isAvailable()) {
                $this->aiService = null;
            }
        } catch (Exception $e) {
            // AI service not available - continue without it
            $this->aiService = null;
        }
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
        $tireSizesFromAI = false; // Track if we used AI to detect sizes
        
        // If tire sizes are "TBD", try to detect using AI
        if ($frontSize === 'TBD' || empty($frontSize) || trim($frontSize) === 'TBD') {
            // Try AI detection to get tire sizes
            if ($this->aiService) {
                try {
                    $aiTireSizes = $this->aiService->getTireSizesFromAI(
                        $year,
                        $make,
                        $model,
                        $trim,
                        $fitment['body_class'] ?? null,
                        $fitment['drive_type'] ?? null
                    );
                    
                    if ($aiTireSizes && isset($aiTireSizes['front_tire']) && !empty($aiTireSizes['front_tire']) && $aiTireSizes['front_tire'] !== 'TBD') {
                        // Use AI-detected tire sizes
                        $frontSize = $aiTireSizes['front_tire'];
                        $rearSize = $aiTireSizes['rear_tire'] ?? $frontSize;
                        $tireSizesFromAI = true;
                        
                        // Optionally update the database with AI-detected sizes (for future use)
                        // Note: We don't update here to avoid blocking the request, but you could add async update
                    }
                } catch (Exception $e) {
                    // AI detection failed - continue with TBD
                    error_log("AI tire size detection failed in TireMatchService: " . $e->getMessage());
                }
            }
        }

        $isStaggered = ($frontSize !== $rearSize && !empty($rearSize));

        // Find matching tires
        $tireSizes = array_unique([$frontSize, $rearSize]);
        $tiresBySize = $this->tireModel->findBySizes($tireSizes);

        // Determine if data is verified or estimated
        $verified = isset($fitment['verified']) ? (bool)$fitment['verified'] : true;
        
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
                'verified' => $verified && !$tireSizesFromAI, // true = exact match, false = estimated or AI-detected
                'ai_detected' => $tireSizesFromAI, // Flag to indicate AI was used
                'notes' => $tireSizesFromAI 
                    ? 'Tire sizes were determined using AI. Always verify on your vehicle\'s tire sidewall or door jamb before purchasing.'
                    : ($fitment['notes'] ?? '')
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
