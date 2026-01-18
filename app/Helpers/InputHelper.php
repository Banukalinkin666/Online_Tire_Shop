<?php

namespace App\Helpers;

/**
 * Input Helper
 * Provides sanitization and validation utilities
 */
class InputHelper
{
    /**
     * Sanitize string input
     * 
     * @param string $input
     * @return string
     */
    public static function sanitizeString(string $input): string
    {
        return trim(strip_tags($input));
    }

    /**
     * Sanitize integer input
     * 
     * @param mixed $input
     * @return int|null
     */
    public static function sanitizeInt($input): ?int
    {
        if ($input === null || $input === '') {
            return null;
        }
        return filter_var($input, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1900, 'max_range' => 2100]]);
    }

    /**
     * Validate VIN format
     * 
     * @param string $vin
     * @return bool
     */
    public static function validateVIN(string $vin): bool
    {
        $vin = strtoupper(trim($vin));
        return strlen($vin) === 17 && preg_match('/^[A-HJ-NPR-Z0-9]{17}$/', $vin);
    }

    /**
     * Get and sanitize POST parameter
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function post(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Get and sanitize GET parameter
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }
}
