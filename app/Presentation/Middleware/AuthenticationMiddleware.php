<?php

namespace App\Presentation\Middleware;

/**
 * Authentication Middleware
 * 
 * Checks if user is authenticated before allowing access.
 */
class AuthenticationMiddleware
{
    /**
     * Handle the request
     * 
     * @return bool
     */
    public function handle(): bool
    {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header('Location: /login');
            exit;
        }
        
        return true;
    }
}
