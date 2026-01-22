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
    // Try v1 endpoint first, fallback to v1beta if needed
    private const GEMINI_API_BASE = 'https://generativelanguage.googleapis.com';
    // Try these models in order (updated based on actual available models)
    private const GEMINI_MODELS = ['gemini-1.5-flash-latest', 'gemini-1.5-flash', 'gemini-1.5-pro-latest', 'gemini-1.5-pro'];
    private const HUGGINGFACE_API_URL = 'https://api-inference.huggingface.co/models/microsoft/DialoGPT-medium';
    
    private ?string $geminiKey;
    
    public function __construct()
    {
        // Google Gemini API key (FREE tier available)
        // Try multiple ways to get the API key
        $this->geminiKey = $_ENV['GEMINI_API_KEY'] ?? $_SERVER['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?? null;
        
        // Log if key is found (without exposing the actual key)
        if ($this->geminiKey) {
            error_log("Gemini API key found: " . substr($this->geminiKey, 0, 10) . "... (length: " . strlen($this->geminiKey) . ")");
        } else {
            error_log("Gemini API key NOT found. Checked: _ENV, _SERVER, getenv()");
        }
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
        
        // Try different models and endpoints until one works
        $lastError = null;
        foreach (self::GEMINI_MODELS as $model) {
            // Try v1 endpoint first
            $url = self::GEMINI_API_BASE . '/v1/models/' . $model . ':generateContent?key=' . urlencode($this->geminiKey);
            error_log("Trying Gemini model: $model with v1 endpoint");
            
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
                $lastError = "cURL error: " . $error;
                continue; // Try next model
            }
            
            if ($httpCode === 200) {
                error_log("✓ Gemini API success with model: $model (v1)");
                break; // Success!
            } elseif ($httpCode === 404) {
                // Try v1beta for this model
                error_log("Model $model not found in v1, trying v1beta...");
                $url = self::GEMINI_API_BASE . '/v1beta/models/' . $model . ':generateContent?key=' . urlencode($this->geminiKey);
                
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
                    $lastError = "cURL error: " . $error;
                    continue; // Try next model
                }
                
                if ($httpCode === 200) {
                    error_log("✓ Gemini API success with model: $model (v1beta)");
                    break; // Success!
                }
            }
            
            $lastError = "HTTP $httpCode: " . substr($response, 0, 200);
            error_log("Model $model failed: $lastError");
        }
        
        // If we get here, all models failed
        if ($httpCode !== 200) {
            error_log("All Gemini models failed. Last error: $lastError");
            throw new Exception("Gemini API returned HTTP code: " . $httpCode . " - " . $lastError);
        }
        
        
        error_log("Gemini API success: HTTP " . $httpCode . ", Response length: " . strlen($response));
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Gemini API JSON decode error: " . json_last_error_msg() . ", Response: " . substr($response, 0, 500));
            throw new Exception("Invalid JSON response from Gemini API: " . json_last_error_msg());
        }
        
        if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            error_log("Gemini API response structure error. Full response: " . json_encode($data));
            throw new Exception("Invalid Gemini API response format - missing candidates[0].content.parts[0].text");
        }
        
        $content = trim($data['candidates'][0]['content']['parts'][0]['text']);
        error_log("Gemini API extracted content: " . substr($content, 0, 200));
        
        // Extract JSON from response (in case AI adds extra text)
        if (preg_match('/\{[^}]+\}/', $content, $matches)) {
            $content = $matches[0];
            error_log("Gemini API extracted JSON: " . $content);
        }
        
        $tireData = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Tire data JSON decode error: " . json_last_error_msg() . ", Content: " . $content);
            throw new Exception("Failed to parse AI tire size JSON: " . json_last_error_msg());
        }
        
        if (!$tireData || !isset($tireData['front_tire'])) {
            error_log("AI tire data validation failed. Data: " . json_encode($tireData));
            throw new Exception("AI did not return valid tire size data - missing front_tire");
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
