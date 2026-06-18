<?php

namespace App\Presentation\Responses;

/**
 * API Response Helper
 * 
 * Standardized response format for all API endpoints.
 * 
 * Success Response:
 * {
 *   "success": true,
 *   "message": "Operation completed successfully",
 *   "data": {...}
 * }
 * 
 * Error Response:
 * {
 *   "success": false,
 *   "message": "Operation failed",
 *   "errors": {...}
 * }
 */
class ApiResponse
{
    /**
     * Send success response
     * 
     * @param mixed $data
     * @param string|null $message
     * @param int $code
     * @return void
     */
    public static function success($data = null, ?string $message = null, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        
        echo json_encode([
            'success' => true,
            'message' => $message ?? 'Operation completed successfully',
            'data' => $data,
        ]);
        
        exit;
    }
    
    /**
     * Send error response
     * 
     * @param string $message
     * @param array $errors
     * @param int $code
     * @return void
     */
    public static function error(string $message, array $errors = [], int $code = 400): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        
        echo json_encode([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ]);
        
        exit;
    }
    
    /**
     * Send validation error response
     * 
     * @param array $errors
     * @return void
     */
    public static function validationError(array $errors): void
    {
        self::error('Validation failed', $errors, 422);
    }
    
    /**
     * Send unauthorized response
     * 
     * @return void
     */
    public static function unauthorized(): void
    {
        self::error('Unauthorized', [], 401);
    }
    
    /**
     * Send forbidden response
     * 
     * @return void
     */
    public static function forbidden(): void
    {
        self::error('Access Denied', [], 403);
    }
    
    /**
     * Send not found response
     * 
     * @return void
     */
    public static function notFound(): void
    {
        self::error('Not Found', [], 404);
    }
}
