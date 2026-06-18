<?php

namespace App\Presentation\Middleware;

/**
 * Authorization Middleware (RBAC)
 * 
 * Checks if user has required permission.
 */
class AuthorizationMiddleware
{
    /**
     * Required permission
     * 
     * @var string
     */
    protected string $permission;
    
    /**
     * Constructor
     * 
     * @param string $permission
     */
    public function __construct(string $permission)
    {
        $this->permission = $permission;
    }
    
    /**
     * Handle the request
     * 
     * @return bool
     */
    public function handle(): bool
    {
        $auth = app(\App\Infrastructure\Authentication\AuthenticationService::class);
        
        if (!$auth->hasPermission($this->permission)) {
            http_response_code(403);
            die('Access Denied');
        }
        
        return true;
    }
}
