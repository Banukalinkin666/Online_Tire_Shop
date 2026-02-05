<?php
/**
 * VIN Decoder Service
 * Uses free NHTSA API only
 */

class VINDecoder
{
    private const NHTSA_API_URL = 'https://vpic.nhtsa.dot.gov/api/vehicles/DecodeVinValues/';

    /**
     * Validate VIN format
     * 
     * @param string $vin
     * @return bool
     */
    public static function validate(string $vin): bool
    {
        // Exactly 17 characters, alphanumeric only
        if (strlen($vin) !== 17) {
            return false;
        }

        // Alphanumeric only (no I, O, Q to avoid confusion)
        if (!preg_match('/^[A-HJ-NPR-Z0-9]{17}$/i', $vin)) {
            return false;
        }

        return true;
    }

    /**
     * Decode VIN using NHTSA API
     * 
     * @param string $vin
     * @return array Decoded vehicle data
     * @throws Exception
     */
    public function decode(string $vin): array
    {
        // Validate VIN first
        if (!self::validate($vin)) {
            throw new Exception('Invalid VIN format. VIN must be exactly 17 alphanumeric characters.');
        }

        // Call NHTSA API
        $url = self::NHTSA_API_URL . urlencode($vin) . '?format=json';

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception('Failed to connect to VIN decoder service. Please try again later.');
        }

        if ($httpCode !== 200) {
            throw new Exception('VIN decoder service returned an error. Please try again later.');
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid response from VIN decoder. Please try again later.');
        }

        // Extract vehicle information
        if (!isset($data['Results'][0])) {
            throw new Exception('VIN not found in database. Please verify the VIN and try again.');
        }

        $result = $data['Results'][0];

        // Check if VIN decode was successful
        if ($result['ErrorCode'] !== '0' && $result['ErrorCode'] !== '') {
            throw new Exception('VIN decode failed: ' . ($result['ErrorText'] ?? 'Unknown error'));
        }

        // Extract and normalize data
        $vehicle = [
            'vin' => strtoupper($vin),
            'year' => (int)($result['ModelYear'] ?? 0),
            'make' => trim($result['Make'] ?? ''),
            'model' => trim($result['Model'] ?? ''),
            'trim' => !empty($result['Trim']) ? trim($result['Trim']) : null,
            'body_class' => !empty($result['BodyClass']) ? trim($result['BodyClass']) : null,
        ];

        // Validate required fields
        if (empty($vehicle['year']) || empty($vehicle['make']) || empty($vehicle['model'])) {
            throw new Exception('Incomplete vehicle data from VIN decoder. VIN may be invalid or not in database.');
        }

        return $vehicle;
    }
}
