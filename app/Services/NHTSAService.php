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
