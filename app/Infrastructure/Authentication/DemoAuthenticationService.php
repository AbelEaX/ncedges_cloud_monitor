<?php

namespace App\Infrastructure\Authentication;

use App\Infrastructure\Database\Connection;

/**
 * Demo Authentication Service
 * 
 * Works without database for testing.
 * In production, uses the database.
 */
class DemoAuthenticationService
{
    /**
     * Demo users (in-memory for testing)
     */
    protected static array $demoUsers = [
        'admin' => [
            'id' => 1,
            'username' => 'admin',
            'password' => 'admin',  // In demo mode, plain text
            'email' => 'admin@ncedges.com',
            'role' => 'admin',
            'created_at' => '2026-01-01 00:00:00',
        ],
        'manager' => [
            'id' => 2,
            'username' => 'manager',
            'password' => 'manager',
            'email' => 'manager@ncedges.com',
            'role' => 'manager',
            'created_at' => '2026-01-01 00:00:00',
        ],
        'viewer' => [
            'id' => 3,
            'username' => 'viewer',
            'password' => 'viewer',
            'email' => 'viewer@ncedges.com',
            'role' => 'viewer',
            'created_at' => '2026-01-01 00:00:00',
        ],
    ];
    
    /**
     * Current user
     */
    protected ?array $user = null;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->loadUserFromSession();
    }
    
    /**
     * Load user from session
     */
    protected function loadUserFromSession(): void
    {
        if (isset($_SESSION['user_id'])) {
            // Find user by ID
            foreach (self::$demoUsers as $user) {
                if ($user['id'] === $_SESSION['user_id']) {
                    $this->user = $user;
                    break;
                }
            }
        }
    }
    
    /**
     * Authenticate user
     */
    public function authenticate(string $username, string $password): bool
    {
        $user = self::$demoUsers[$username] ?? null;
        
        if (!$user) {
            return false;
        }
        
        // In demo mode, plain text comparison
        if ($user['password'] !== $password) {
            return false;
        }
        
        // Store in session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['logged_in'] = true;
        
        $this->user = $user;
        
        return true;
    }
    
    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool
    {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Get current user
     */
    public function getUser(): ?array
    {
        return $this->user;
    }
    
    /**
     * Get user wrapper
     */
    public function user(): ?UserWrapper
    {
        if (!$this->user) {
            return null;
        }
        return new UserWrapper($this->user);
    }
    
    /**
     * Get user by ID
     */
    public function getUserById(int $id): ?array
    {
        foreach (self::$demoUsers as $user) {
            if ($user['id'] === $id) {
                return $user;
            }
        }
        return null;
    }
    
    /**
     * Get user by username
     */
    public function getUserByUsername(string $username): ?array
    {
        return self::$demoUsers[$username] ?? null;
    }
    
    /**
     * Check if user has permission
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->isAuthenticated()) {
            return false;
        }
        
        // Admin has all permissions
        if ($this->user['role'] === 'admin') {
            return true;
        }
        
        // Manager has most permissions
        if ($this->user['role'] === 'manager') {
            return $permission !== 'admin.settings.dangerous';
        }
        
        // Viewer has limited permissions
        return $permission === 'view.dashboard' || $permission === 'view.reports';
    }
    
    /**
     * Logout user
     */
    public function logout(): void
    {
        session_destroy();
        $this->user = null;
    }
}
