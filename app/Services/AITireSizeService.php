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
    // Try these models in order (fallback if ListModels fails)
    // Updated to use models that are more likely to be available
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
        
        $prompt = "What are the OEM tire sizes for a $vehicleInfo?

CRITICAL: You MUST return a COMPLETE, valid JSON object. Do NOT truncate or leave it incomplete.

Return ONLY this JSON format (complete the entire object):
{
  \"front_tire\": \"225/65R17\",
  \"rear_tire\": null
}

Rules:
1. Use format: WIDTH/ASPECTRATIO RIM (e.g., 225/65R17, 245/40R18)
2. If same size front/rear: set rear_tire to null
3. If different sizes: provide both values
4. MUST be complete valid JSON - close all quotes and braces
5. NO markdown code blocks, NO extra text, ONLY the JSON object";
        
        // First, try to get available models dynamically
        $availableModels = $this->listAvailableModels();
        $modelsToTry = !empty($availableModels) ? $availableModels : self::GEMINI_MODELS;
        
        if (!empty($availableModels)) {
            error_log("Using dynamically discovered models: " . implode(', ', $availableModels));
        } else {
            error_log("ListModels failed, using fallback models: " . implode(', ', self::GEMINI_MODELS));
        }
        
        // Try different models and endpoints until one works
        $lastError = null;
        $httpCode = 0;
        $response = null;
        
        foreach ($modelsToTry as $model) {
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
                        'temperature' => 0.1,
                        'maxOutputTokens' => 500,
                        'topP' => 0.8,
                        'topK' => 40
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
            
            $lastError = "HTTP $httpCode: " . substr($response ?? '', 0, 200);
            error_log("Model $model failed: $lastError");
        }
        
        // If we get here, all models failed
        if ($httpCode !== 200) {
            $errorMsg = $lastError ?? 'Unknown error - no models available';
            error_log("All Gemini models failed. Last error: $errorMsg");
            throw new Exception("Gemini API returned HTTP code: " . $httpCode . " - " . $errorMsg);
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
        error_log("Gemini API raw content (first 500 chars): " . substr($content, 0, 500));
        
        // Try multiple methods to extract JSON from AI response
        $tireData = null;
        
        // Method 1: Try parsing the entire content as JSON first
        $tireData = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($tireData['front_tire'])) {
            error_log("✓ Successfully parsed JSON from full content");
        } else {
            // Method 2: Extract JSON from markdown code blocks (```json ... ```)
            // Handle both complete and incomplete markdown blocks
            if (preg_match('/```(?:json)?\s*(\{.*?)(?:\}\s*```|$)/s', $content, $matches)) {
                $jsonStr = trim($matches[1]);
                // If incomplete, try to complete it
                if (!str_ends_with($jsonStr, '}')) {
                    // Check if front_tire value is incomplete
                    if (preg_match('/"front_tire"\s*:\s*"([^"]*)$/', $jsonStr, $tireMatch)) {
                        // If we have a partial value, try to extract it
                        if (!empty($tireMatch[1]) && preg_match('/^\d{3}\/\d{2}R\d{2}$/', $tireMatch[1])) {
                            error_log("⚠️ Incomplete markdown JSON, but extracted front_tire: " . $tireMatch[1]);
                            $tireData = ['front_tire' => $tireMatch[1], 'rear_tire' => null];
                        }
                    }
                } else {
                    // Complete JSON in markdown
                    $tireData = json_decode($jsonStr, true);
                    if (json_last_error() === JSON_ERROR_NONE && isset($tireData['front_tire'])) {
                        error_log("✓ Successfully extracted JSON from markdown code block");
                    }
                }
            }
            
            // Method 3: Extract JSON object (handles nested objects)
            if (!$tireData || !isset($tireData['front_tire'])) {
                // Find the first { and match until the closing } (handles nested objects)
                $startPos = strpos($content, '{');
                if ($startPos !== false) {
                    $braceCount = 0;
                    $endPos = $startPos;
                    for ($i = $startPos; $i < strlen($content); $i++) {
                        if ($content[$i] === '{') {
                            $braceCount++;
                        } elseif ($content[$i] === '}') {
                            $braceCount--;
                            if ($braceCount === 0) {
                                $endPos = $i + 1;
                                break;
                            }
                        }
                    }
                    if ($endPos > $startPos) {
                        $jsonStr = substr($content, $startPos, $endPos - $startPos);
                        $tireData = json_decode($jsonStr, true);
                        if (json_last_error() === JSON_ERROR_NONE && isset($tireData['front_tire'])) {
                            error_log("✓ Successfully extracted JSON using brace matching");
                        }
                    }
                }
            }
        }
        
        // If still no valid data, try to recover from incomplete JSON
        if (!$tireData || !isset($tireData['front_tire'])) {
            // Check if we got partial data (incomplete JSON)
            if (strpos($content, 'front_tire') !== false) {
                error_log("⚠️ Incomplete JSON detected. Attempting to extract partial data...");
                // Try to extract just the front_tire value using regex
                if (preg_match('/"front_tire"\s*:\s*"([^"]+)"/', $content, $matches)) {
                    $frontTire = trim($matches[1]);
                    if (!empty($frontTire) && preg_match('/^\d{3}\/\d{2}R\d{2}$/', $frontTire)) {
                        error_log("✓ Extracted front_tire from incomplete JSON: " . $frontTire);
                        $tireData = ['front_tire' => $frontTire, 'rear_tire' => null];
                    }
                }
            }
            
            // If still no valid data, throw error with full content
            if (!$tireData || !isset($tireData['front_tire'])) {
                error_log("❌ Failed to extract valid JSON. Full content: " . $content);
                error_log("Content length: " . strlen($content));
                error_log("JSON decode error: " . json_last_error_msg());
                throw new Exception("Failed to parse AI tire size JSON: " . json_last_error_msg() . ". Content received: " . substr($content, 0, 500));
            }
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
     * List available Gemini models for this API key
     * 
     * @return array List of available model names
     */
    private function listAvailableModels(): array
    {
        if (!$this->geminiKey) {
            return [];
        }
        
        try {
            $url = self::GEMINI_API_BASE . '/v1/models?key=' . urlencode($this->geminiKey);
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_CONNECTTIMEOUT => 3
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $data = json_decode($response, true);
                $models = [];
                if (isset($data['models']) && is_array($data['models'])) {
                    foreach ($data['models'] as $model) {
                        if (isset($model['name'])) {
                            // Extract model name (e.g., "models/gemini-1.5-flash" -> "gemini-1.5-flash")
                            $name = str_replace('models/', '', $model['name']);
                            // Only include models that support generateContent
                            if (isset($model['supportedGenerationMethods']) && 
                                in_array('generateContent', $model['supportedGenerationMethods'])) {
                                $models[] = $name;
                            }
                        }
                    }
                }
                error_log("Found " . count($models) . " available Gemini models: " . implode(', ', $models));
                return $models;
            } else {
                error_log("ListModels API returned HTTP $httpCode: " . substr($response ?? '', 0, 200));
            }
        } catch (Exception $e) {
            error_log("Failed to list Gemini models: " . $e->getMessage());
        }
        
        // Fallback to empty array - will use hardcoded GEMINI_MODELS
        return [];
    }
    
    /**
     * Get tire sizes from natural language query
     * Example: "What tire sizes fit my 2018 honda civic with 16 wheels"
     * 
     * @param string $query Natural language query
     * @return array Result with success, tire sizes, and vehicle info
     */
    public function getTireSizesFromNaturalLanguage(string $query): array
    {
        if (!$this->geminiKey) {
            return [
                'success' => false,
                'message' => 'AI service is not available. Please configure GEMINI_API_KEY.'
            ];
        }
        
        try {
            return $this->getTireSizesFromGeminiNaturalLanguage($query);
        } catch (Exception $e) {
            error_log("Gemini natural language API error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to process your query: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get tire sizes using Google Gemini with natural language query
     */
    private function getTireSizesFromGeminiNaturalLanguage(string $query): array
    {
        $prompt = "The user asked: \"$query\"

Extract the vehicle information and tire sizes from this query. The user wants to know what tire sizes fit their vehicle.

CRITICAL: You MUST return a COMPLETE, valid JSON object. Do NOT truncate or leave it incomplete.

Return ONLY this JSON format (complete the entire object):
{
  \"year\": 2018,
  \"make\": \"Honda\",
  \"model\": \"Civic\",
  \"trim\": null,
  \"wheel_size\": \"16\",
  \"front_tire\": \"205/55R16\",
  \"rear_tire\": null,
  \"explanation\": \"Based on your query, the recommended tire size for a 2018 Honda Civic with 16-inch wheels is 205/55R16.\"
}

Rules:
1. Extract year, make, model, and trim (if mentioned) from the query
2. Extract wheel size if mentioned (e.g., \"16 wheels\" = \"16\")
3. Determine the appropriate tire size based on the vehicle and wheel size
4. Use format: WIDTH/ASPECTRATIO RIM (e.g., 225/65R17, 245/40R18)
5. If same size front/rear: set rear_tire to null
6. If different sizes: provide both values
7. Provide a helpful explanation in the explanation field
8. MUST be complete valid JSON - close all quotes and braces
9. NO markdown code blocks, NO extra text, ONLY the JSON object";
        
        // Get available models
        $availableModels = $this->listAvailableModels();
        $modelsToTry = !empty($availableModels) ? $availableModels : self::GEMINI_MODELS;
        
        // Try different models until one works
        $lastError = null;
        $httpCode = 0;
        $response = null;
        
        foreach ($modelsToTry as $model) {
            $url = self::GEMINI_API_BASE . '/v1/models/' . $model . ':generateContent?key=' . urlencode($this->geminiKey);
            error_log("Trying Gemini model: $model for natural language query");
            
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
                        'temperature' => 0.1,
                        'maxOutputTokens' => 800,
                        'topP' => 0.8,
                        'topK' => 40
                    ]
                ]),
                CURLOPT_TIMEOUT => 15,
                CURLOPT_CONNECTTIMEOUT => 5
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                $lastError = "cURL error: $error";
                continue;
            }
            
            if ($httpCode !== 200) {
                $lastError = "HTTP $httpCode: " . substr($response ?? '', 0, 200);
                continue;
            }
            
            // Parse response
            $data = json_decode($response, true);
            if (!$data || !isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                $lastError = "Invalid API response structure";
                continue;
            }
            
            $content = $data['candidates'][0]['content']['parts'][0]['text'];
            error_log("Gemini natural language response: " . substr($content, 0, 500));
            
            // Extract JSON from response
            $jsonData = $this->extractJsonFromResponse($content);
            
            if ($jsonData && isset($jsonData['front_tire']) && !empty($jsonData['front_tire'])) {
                return [
                    'success' => true,
                    'year' => $jsonData['year'] ?? null,
                    'make' => $jsonData['make'] ?? null,
                    'model' => $jsonData['model'] ?? null,
                    'trim' => $jsonData['trim'] ?? null,
                    'wheel_size' => $jsonData['wheel_size'] ?? null,
                    'front_tire' => $jsonData['front_tire'],
                    'rear_tire' => $jsonData['rear_tire'] ?? null,
                    'explanation' => $jsonData['explanation'] ?? 'Tire sizes determined using AI.',
                    'is_staggered' => !empty($jsonData['rear_tire']) && $jsonData['front_tire'] !== $jsonData['rear_tire'],
                    'source' => 'ai_natural_language'
                ];
            }
            
            $lastError = "Failed to extract valid tire sizes from response";
        }
        
        return [
            'success' => false,
            'message' => 'Could not determine tire sizes from your query. Please try rephrasing or be more specific about the vehicle year, make, and model.'
        ];
    }
    
    /**
     * Extract JSON from AI response (handles markdown code blocks, etc.)
     */
    private function extractJsonFromResponse(string $content): ?array
    {
        // Try parsing the full content first
        $json = json_decode($content, true);
        if ($json !== null) {
            return $json;
        }
        
        // Try extracting from markdown code blocks
        if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $content, $matches)) {
            $json = json_decode($matches[1], true);
            if ($json !== null) {
                return $json;
            }
        }
        
        // Try finding JSON object with brace matching
        $startPos = strpos($content, '{');
        if ($startPos !== false) {
            $braceCount = 0;
            $endPos = $startPos;
            for ($i = $startPos; $i < strlen($content); $i++) {
                if ($content[$i] === '{') {
                    $braceCount++;
                } elseif ($content[$i] === '}') {
                    $braceCount--;
                    if ($braceCount === 0) {
                        $endPos = $i + 1;
                        break;
                    }
                }
            }
            
            if ($braceCount === 0) {
                $jsonStr = substr($content, $startPos, $endPos - $startPos);
                $json = json_decode($jsonStr, true);
                if ($json !== null) {
                    return $json;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Check if AI service is available
     */
    public function isAvailable(): bool
    {
        return !empty($this->geminiKey);
    }
}
