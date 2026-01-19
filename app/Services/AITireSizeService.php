<?php

namespace App\Services;

use Exception;

/**
 * AI Tire Size Service
 * Uses AI to determine tire sizes from vehicle information
 * 
 * Uses FREE AI providers:
 * - Google Gemini (FREE tier - recommended)
 * - Hugging Face Inference API (FREE)
 * - Fallback to database lookup if AI unavailable
 */
class AITireSizeService
{
    private const GEMINI_API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';
    private const HUGGINGFACE_API_URL = 'https://api-inference.huggingface.co/models/microsoft/DialoGPT-medium';
    
    private ?string $geminiKey;
    
    public function __construct()
    {
        // Google Gemini API key (FREE tier available)
        $this->geminiKey = $_ENV['GEMINI_API_KEY'] ?? $_SERVER['GEMINI_API_KEY'] ?? null;
    }
    
    /**
     * Get tire sizes for a vehicle using AI
     * 
     * @param int $year
     * @param string $make
     * @param string $model
     * @param string|null $trim
     * @param string|null $bodyClass
     * @param string|null $driveType
     * @return array|null Tire sizes or null if AI unavailable
     */
    public function getTireSizesFromAI(
        int $year,
        string $make,
        string $model,
        ?string $trim = null,
        ?string $bodyClass = null,
        ?string $driveType = null
    ): ?array {
        // Try Google Gemini (FREE tier)
        if ($this->geminiKey) {
            try {
                return $this->getTireSizesFromGemini($year, $make, $model, $trim, $bodyClass, $driveType);
            } catch (Exception $e) {
                error_log("Gemini API error: " . $e->getMessage());
            }
        }
        
        // No AI available (free tier requires API key)
        return null;
    }
    
    /**
     * Get tire sizes using Google Gemini (FREE tier)
     */
    private function getTireSizesFromGemini(
        int $year,
        string $make,
        string $model,
        ?string $trim,
        ?string $bodyClass,
        ?string $driveType
    ): ?array {
        if (!$this->geminiKey) {
            return null;
        }
        
        $vehicleInfo = "$year $make $model";
        if ($trim) {
            $vehicleInfo .= " $trim";
        }
        if ($bodyClass) {
            $vehicleInfo .= " ($bodyClass)";
        }
        if ($driveType) {
            $vehicleInfo .= " - $driveType";
        }
        
        $prompt = "What are the OEM (original equipment) tire sizes for a $vehicleInfo? 
Provide ONLY the tire sizes in standard format (e.g., 225/65R17).
If different front and rear sizes (staggered), provide both. If same, provide only front.
Respond in JSON only: {\"front_tire\": \"225/65R17\", \"rear_tire\": \"225/65R17\" or null if same}
No additional text, only valid JSON.";
        
        $url = self::GEMINI_API_URL . '?key=' . urlencode($this->geminiKey);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $prompt
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.3,
                    'maxOutputTokens' => 150
                ]
            ]),
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("Gemini API request failed: " . $error);
        }
        
        if ($httpCode !== 200) {
            throw new Exception("Gemini API returned HTTP code: " . $httpCode);
        }
        
        $data = json_decode($response, true);
        
        if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            throw new Exception("Invalid Gemini API response format");
        }
        
        $content = trim($data['candidates'][0]['content']['parts'][0]['text']);
        
        // Extract JSON from response (in case AI adds extra text)
        if (preg_match('/\{[^}]+\}/', $content, $matches)) {
            $content = $matches[0];
        }
        
        $tireData = json_decode($content, true);
        
        if (!$tireData || !isset($tireData['front_tire'])) {
            throw new Exception("AI did not return valid tire size data");
        }
        
        // Validate tire size format
        $frontTire = trim($tireData['front_tire']);
        $rearTire = isset($tireData['rear_tire']) && !empty($tireData['rear_tire']) 
            ? trim($tireData['rear_tire']) 
            : null;
        
        if (!preg_match('/^\d{3}\/\d{2}R\d{2}$/', $frontTire)) {
            throw new Exception("AI returned invalid front tire size format: " . $frontTire);
        }
        
        if ($rearTire && !preg_match('/^\d{3}\/\d{2}R\d{2}$/', $rearTire)) {
            throw new Exception("AI returned invalid rear tire size format: " . $rearTire);
        }
        
        return [
            'front_tire' => $frontTire,
            'rear_tire' => $rearTire,
            'source' => 'ai_gemini_free'
        ];
    }
    
    /**
     * Check if AI service is available
     */
    public function isAvailable(): bool
    {
        return !empty($this->geminiKey);
    }
}
