<?php

namespace App\Infrastructure\Authentication;

use App\Infrastructure\Database\Connection;

/**
 * Authentication Service
 * 
 * Handles user authentication and session management.
 * Supports login, logout, and permission checking.
 */
class AuthenticationService
{
    /**
     * Database connection
     * 
     * @var Connection
     */
    protected Connection $connection;
    
    /**
     * Security configuration
     * 
     * @var array
     */
    protected array $config;
    
    /**
     * Current user
     * 
     * @var array|null
     */
    protected ?array $user = null;
    
    /**
     * Constructor
     * 
     * @param Connection $connection
     * @param array $config
     */
    public function __construct(Connection $connection, array $config)
    {
        $this->connection = $connection;
        $this->config = $config;
        $this->loadUserFromSession();
    }
    
    /**
     * Load user from session
     * 
     * @return void
     */
    protected function loadUserFromSession(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->user = $this->getUserById($_SESSION['user_id']);
        }
    }
    
    /**
     * Authenticate user
     * 
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function authenticate(string $username, string $password): bool
    {
        $user = $this->getUserByUsername($username);
        
        if (!$user) {
            return false;
        }
        
        if (!password_verify($password, $user['password'])) {
            return false;
        }
        
        // Store in session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['logged_in'] = true;
        $_SESSION['last_activity'] = time();
        
        $this->user = $user;
        
        return true;
    }
    
    /**
     * Check if user is authenticated
     * 
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            return false;
        }
        
        $timeout = $this->config['auth']['session_timeout'] ?? 7200;
        
        // Enforce session timeout if configured and > 0
        if ($timeout > 0 && isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > $timeout) {
                // Session expired
                $this->logout();
                return false;
            }
        }
        
        // Update last activity
        $_SESSION['last_activity'] = time();
        
        return true;
    }
    
    /**
     * Get current user
     * 
     * @return array|null
     */
    public function getUser(): ?array
    {
        return $this->user;
    }
    
    /**
     * Alias for getUser() for framework consistency
     * 
     * @return UserWrapper|null
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
     * 
     * @param int $id
     * @return array|null
     */
    public function getUserById(int $id): ?array
    {
        return $this->connection->fetchOne(
            'SELECT * FROM users WHERE id = ?',
            [$id]
        );
    }
    
    /**
     * Get user by username
     * 
     * @param string $username
     * @return array|null
     */
    public function getUserByUsername(string $username): ?array
    {
        return $this->connection->fetchOne(
            'SELECT * FROM users WHERE username = ?',
            [$username]
        );
    }
    
    /**
     * Check if user has permission
     * 
     * @param string $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->isAuthenticated()) {
            return false;
        }
        
        $role = $this->user['role'] ?? 'viewer';
        
        // Admin has all permissions
        if ($role === 'admin') {
            return true;
        }
        
        // Manager has management access (create/update servers, view reports)
        if ($role === 'manager') {
            if (in_array($permission, ['server.view', 'server.create', 'server.edit', 'reports.view', 'reports.export', 'audit.view', 'settings.view'])) {
                return true;
            }
            return false;
        }
        
        // Viewer has read-only access
        if ($role === 'viewer') {
            if (in_array($permission, ['server.view', 'reports.view', 'audit.view'])) {
                return true;
            }
            return false;
        }
        
        // Check user permissions table as fallback
        $result = $this->connection->fetchOne(
            'SELECT 1 FROM user_permissions WHERE user_id = ? AND permission = ?',
            [$this->user['id'], $permission]
        );
        
        return $result !== null;
    }
    
    /**
     * Logout user
     * 
     * @return void
     */
    public function logout(): void
    {
        session_destroy();
        $this->user = null;
    }
    
    /**
     * Generate and retrieve CSRF token
     * 
     * @return string
     */
    public function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate CSRF token
     * 
     * @param string $token
     * @return bool
     */
    public function validateCsrfToken(?string $token): bool
    {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
