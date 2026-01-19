<?php

namespace App\Services;

use Exception;

/**
 * AI Tire Size Service
 * Uses AI to determine tire sizes from vehicle information
 * 
 * Supports multiple AI providers:
 * - OpenAI (GPT models)
 * - Anthropic Claude (if API key provided)
 * - Fallback to database lookup
 */
class AITireSizeService
{
    private const OPENAI_API_URL = 'https://api.openai.com/v1/chat/completions';
    private const ANTHROPIC_API_URL = 'https://api.anthropic.com/v1/messages';
    
    private ?string $openaiKey;
    private ?string $anthropicKey;
    
    public function __construct()
    {
        $this->openaiKey = $_ENV['OPENAI_API_KEY'] ?? $_SERVER['OPENAI_API_KEY'] ?? null;
        $this->anthropicKey = $_ENV['ANTHROPIC_API_KEY'] ?? $_SERVER['ANTHROPIC_API_KEY'] ?? null;
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
        // Try OpenAI first
        if ($this->openaiKey) {
            try {
                return $this->getTireSizesFromOpenAI($year, $make, $model, $trim, $bodyClass, $driveType);
            } catch (Exception $e) {
                error_log("OpenAI API error: " . $e->getMessage());
            }
        }
        
        // Try Anthropic if OpenAI fails
        if ($this->anthropicKey) {
            try {
                return $this->getTireSizesFromAnthropic($year, $make, $model, $trim, $bodyClass, $driveType);
            } catch (Exception $e) {
                error_log("Anthropic API error: " . $e->getMessage());
            }
        }
        
        // No AI available
        return null;
    }
    
    /**
     * Get tire sizes using OpenAI
     */
    private function getTireSizesFromOpenAI(
        int $year,
        string $make,
        string $model,
        ?string $trim,
        ?string $bodyClass,
        ?string $driveType
    ): ?array {
        if (!$this->openaiKey) {
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
Please provide ONLY the tire sizes in the standard format (e.g., 225/65R17).
If the vehicle has different front and rear tire sizes (staggered setup), provide both.
If front and rear are the same, provide only one size.
Respond in JSON format: {\"front_tire\": \"225/65R17\", \"rear_tire\": \"225/65R17\" or null if same}
Only respond with valid JSON, no additional text.";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => self::OPENAI_API_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->openaiKey
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a tire fitment expert. Provide accurate OEM tire sizes for vehicles in JSON format only.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.3,
                'max_tokens' => 150
            ]),
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("OpenAI API request failed: " . $error);
        }
        
        if ($httpCode !== 200) {
            throw new Exception("OpenAI API returned HTTP code: " . $httpCode);
        }
        
        $data = json_decode($response, true);
        
        if (!isset($data['choices'][0]['message']['content'])) {
            throw new Exception("Invalid OpenAI API response format");
        }
        
        $content = trim($data['choices'][0]['message']['content']);
        
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
            'source' => 'ai_openai'
        ];
    }
    
    /**
     * Get tire sizes using Anthropic Claude
     */
    private function getTireSizesFromAnthropic(
        int $year,
        string $make,
        string $model,
        ?string $trim,
        ?string $bodyClass,
        ?string $driveType
    ): ?array {
        if (!$this->anthropicKey) {
            return null;
        }
        
        $vehicleInfo = "$year $make $model";
        if ($trim) {
            $vehicleInfo .= " $trim";
        }
        
        $prompt = "What are the OEM tire sizes for a $vehicleInfo? Provide in JSON: {\"front_tire\": \"225/65R17\", \"rear_tire\": null or size}. Only JSON response.";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => self::ANTHROPIC_API_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: ' . $this->anthropicKey,
                'anthropic-version: 2023-06-01'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => 'claude-3-haiku-20240307',
                'max_tokens' => 150,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ]
            ]),
            CURLOPT_TIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("Anthropic API returned HTTP code: " . $httpCode);
        }
        
        $data = json_decode($response, true);
        
        if (!isset($data['content'][0]['text'])) {
            throw new Exception("Invalid Anthropic API response");
        }
        
        $content = trim($data['content'][0]['text']);
        
        // Extract JSON
        if (preg_match('/\{[^}]+\}/', $content, $matches)) {
            $content = $matches[0];
        }
        
        $tireData = json_decode($content, true);
        
        if (!$tireData || !isset($tireData['front_tire'])) {
            throw new Exception("AI did not return valid tire size data");
        }
        
        return [
            'front_tire' => trim($tireData['front_tire']),
            'rear_tire' => isset($tireData['rear_tire']) && !empty($tireData['rear_tire']) 
                ? trim($tireData['rear_tire']) 
                : null,
            'source' => 'ai_anthropic'
        ];
    }
    
    /**
     * Check if AI service is available
     */
    public function isAvailable(): bool
    {
        return !empty($this->openaiKey) || !empty($this->anthropicKey);
    }
}
