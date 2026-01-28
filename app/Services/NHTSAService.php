<?php

namespace App\Services;

use Exception;

/**
 * NHTSA VIN Decode Service
 * Handles communication with NHTSA API for VIN decoding
 * 
 * Note: VINs are NOT stored in the database per privacy requirements
 */
class NHTSAService
{
    private const API_BASE_URL = 'https://vpic.nhtsa.dot.gov/api/vehicles/decodevin/';
    private const API_FORMAT = 'json';

    /**
     * Decode VIN using NHTSA API
     * 
     * @param string $vin 17-character VIN
     * @return array Decoded vehicle information
     * @throws Exception If VIN is invalid or API call fails
     */
    public function decodeVIN(string $vin): array
    {
        // Validate VIN length
        $vin = strtoupper(trim($vin));
        if (strlen($vin) !== 17) {
            throw new Exception("Invalid VIN length. VIN must be exactly 17 characters.");
        }

        // Basic VIN validation (alphanumeric, no I, O, Q to avoid confusion)
        if (!preg_match('/^[A-HJ-NPR-Z0-9]{17}$/', $vin)) {
            throw new Exception("Invalid VIN format. VIN contains invalid characters.");
        }

        $url = self::API_BASE_URL . urlencode($vin) . '?format=' . self::API_FORMAT;

        // Initialize cURL
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'TireShopFitmentApp/1.0'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("API request failed: " . $error);
        }

        if ($httpCode !== 200) {
            throw new Exception("API returned HTTP code: " . $httpCode);
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Failed to parse API response: " . json_last_error_msg());
        }

        if (!isset($data['Results']) || !is_array($data['Results'])) {
            throw new Exception("Invalid API response format");
        }

        // Extract relevant vehicle information
        $vehicleInfo = $this->parseResults($data['Results']);

        if (empty($vehicleInfo['make']) || empty($vehicleInfo['model']) || empty($vehicleInfo['year'])) {
            // Log the actual response for debugging
            error_log("NHTSA API Response: " . json_encode($data['Results'], JSON_PRETTY_PRINT));
            error_log("Parsed vehicle info: " . json_encode($vehicleInfo, JSON_PRETTY_PRINT));
            throw new Exception("Unable to decode complete vehicle information from VIN. Make: " . ($vehicleInfo['make'] ?: 'missing') . ", Model: " . ($vehicleInfo['model'] ?: 'missing') . ", Year: " . ($vehicleInfo['year'] ?: 'missing'));
        }

        return $vehicleInfo;
    }

    /**
     * Get all makes for a specific year from NHTSA vPIC API
     * 
     * @param int $year Model year
     * @param int $timeoutSeconds Request timeout (seconds)
     * @return array List of makes
     * @throws Exception If API call fails
     */
    public function getMakesForYear(int $year, int $timeoutSeconds = 15): array
    {
        // NHTSA vPIC API endpoint for getting makes by year
        $url = "https://vpic.nhtsa.dot.gov/api/vehicles/GetMakesForVehicleType/car?format=json";
        
        // Note: The API doesn't filter by year directly, so we'll get all makes
        // and filter by year when getting models
        
        $ch = curl_init();
        $timeoutSeconds = max(1, min(60, $timeoutSeconds));
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeoutSeconds,
            CURLOPT_CONNECTTIMEOUT => min(10, $timeoutSeconds),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'TireShopFitmentApp/1.0'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("API request failed: " . $error);
        }
        
        if ($httpCode !== 200) {
            throw new Exception("API returned HTTP code: " . $httpCode);
        }
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Failed to parse API response: " . json_last_error_msg());
        }
        
        if (!isset($data['Results']) || !is_array($data['Results'])) {
            throw new Exception("Invalid API response format");
        }
        
        $makes = [];
        foreach ($data['Results'] as $result) {
            if (isset($result['MakeName']) && !empty($result['MakeName'])) {
                $makes[] = trim($result['MakeName']);
            }
        }
        
        // Remove duplicates and sort
        $makes = array_unique($makes);
        sort($makes);
        
        return array_values($makes);
    }
    
    /**
     * Get all models for a specific make and year from NHTSA vPIC API
     * 
     * @param string $make Vehicle make
     * @param int $year Model year
     * @param int $timeoutSeconds Request timeout (seconds)
     * @return array List of models
     * @throws Exception If API call fails
     */
    public function getModelsForMakeYear(string $make, int $year, int $timeoutSeconds = 20): array
    {
        $make = urlencode($make);
        $url = "https://vpic.nhtsa.dot.gov/api/vehicles/GetModelsForMakeYear/make/{$make}/modelyear/{$year}?format=json";
        
        $ch = curl_init();
        $timeoutSeconds = max(1, min(60, $timeoutSeconds));
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeoutSeconds,
            CURLOPT_CONNECTTIMEOUT => min(10, $timeoutSeconds),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'TireShopFitmentApp/1.0',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_TIMEOUT_MS => $timeoutSeconds * 1000, // hard limit
            CURLOPT_CONNECTTIMEOUT_MS => min(10, $timeoutSeconds) * 1000
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $curlErrno = curl_errno($ch);
        curl_close($ch);
        
        // Handle timeout errors gracefully
        if ($curlErrno === CURLE_OPERATION_TIMEDOUT || $curlErrno === CURLE_OPERATION_TIMEOUTED) {
            // Timeout occurred - return empty array instead of throwing (allows script to continue)
            return [];
        }
        
        if ($error) {
            // For other errors, return empty array instead of throwing to prevent script from stopping
            return [];
        }
        
        if ($httpCode !== 200) {
            // Some makes might not have models for certain years - that's okay
            // Return empty array for any non-200 response to allow script to continue
            return [];
        }
        
        // Check if response is HTML instead of JSON (API sometimes returns error pages)
        $responseTrimmed = trim($response);
        
        // Enhanced HTML detection - check for common HTML tags and patterns
        $isHtml = empty($responseTrimmed) || 
            stripos($responseTrimmed, '<!DOCTYPE') === 0 || 
            stripos($responseTrimmed, '<html') === 0 ||
            stripos($responseTrimmed, '<!doctype') === 0 ||
            stripos($responseTrimmed, '<body') !== false ||
            stripos($responseTrimmed, '<head') !== false ||
            (stripos($responseTrimmed, '<') === 0 && stripos($responseTrimmed, '<?xml') !== 0);
        
        if ($isHtml) {
            // Silently skip HTML responses - this is expected for some makes/years
            return []; // Return empty array - script continues
        }
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Double-check if it's HTML that wasn't caught above (case-insensitive)
            $responseLower = strtolower($responseTrimmed);
            if (strpos($responseLower, '<html') !== false || 
                strpos($responseLower, '<body') !== false || 
                strpos($responseLower, '<head') !== false ||
                strpos($responseLower, '<!doctype') !== false) {
                // HTML response - silently skip (no logging needed)
                return [];
            }
            
            // For actual JSON parse errors (not HTML), only log if response is suspiciously short
            // Many makes simply don't have data for certain years - this is normal
            if (strlen($responseTrimmed) < 50) {
                // Very short response might indicate an error page - skip silently
                return [];
            }
            
            // Only log if it looks like actual JSON that failed to parse (rare case)
            // Most parse errors are from HTML responses which we've already handled
            if (strpos($responseTrimmed, '{') === 0 || strpos($responseTrimmed, '[') === 0) {
                // Looks like JSON but failed to parse - log only in debug mode
                if (defined('DEBUG_NHTSA_ERRORS') && DEBUG_NHTSA_ERRORS) {
                    error_log("NHTSA API JSON parse error for make '{$make}' year {$year}: " . json_last_error_msg());
                }
            }
            // Return empty array instead of throwing - allows script to continue
            return [];
        }
        
        if (!isset($data['Results']) || !is_array($data['Results'])) {
            // Empty or invalid response structure - silently skip (expected for some makes/years)
            return [];
        }
        
        $models = [];
        foreach ($data['Results'] as $result) {
            if (isset($result['Model_Name']) && !empty($result['Model_Name'])) {
                $models[] = trim($result['Model_Name']);
            }
        }
        
        // Remove duplicates and sort
        $models = array_unique($models);
        sort($models);
        
        return array_values($models);
    }
    
    /**
     * Parse NHTSA API results into structured format
     * 
     * @param array $results Raw API results
     * @return array Parsed vehicle information
     */
    private function parseResults(array $results): array
    {
        $vehicleInfo = [
            'vin' => '',
            'year' => null,
            'make' => '',
            'model' => '',
            'trim' => '',
            'body_class' => '',
            'drive_type' => '',
            'fuel_type' => ''
        ];

        foreach ($results as $result) {
            if (!isset($result['Variable']) || !isset($result['Value'])) {
                continue;
            }

            $variable = $result['Variable'];
            $value = trim($result['Value']);

            // Skip empty or "Not Applicable" values
            if (empty($value) || $value === 'Not Applicable' || $value === 'Not provided') {
                continue;
            }

            switch ($variable) {
                case 'Model Year':
                    $vehicleInfo['year'] = (int)$value;
                    break;
                case 'Make':
                    $vehicleInfo['make'] = $value;
                    break;
                case 'Model':
                    $vehicleInfo['model'] = $value;
                    break;
                case 'Trim':
                    $vehicleInfo['trim'] = $value;
                    break;
                case 'Body Class':
                    $vehicleInfo['body_class'] = $value;
                    break;
                case 'Drive Type':
                    $vehicleInfo['drive_type'] = $value;
                    break;
                case 'Fuel Type - Primary':
                    $vehicleInfo['fuel_type'] = $value;
                    break;
                case 'VIN':
                    $vehicleInfo['vin'] = $value;
                    break;
            }
        }

        return $vehicleInfo;
    }
}
