<?php

namespace App\Helpers;

/**
 * Response Helper
 * Standardizes JSON API responses
 */
class ResponseHelper
{
    /**
     * Send JSON success response
     * 
     * @param mixed $data
     * @param int $statusCode
     * @return void
     */
    public static function success($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $data
        ], JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Send JSON error response
     * 
     * @param string $message
     * @param int $statusCode
     * @param array $errors
     * @return void
     */
    public static function error(string $message, int $statusCode = 400, array $errors = []): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], JSON_PRETTY_PRINT);
        exit;
    }
}
