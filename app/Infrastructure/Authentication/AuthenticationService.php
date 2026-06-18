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
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
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
        
        // Admin has all permissions
        if ($this->user['role'] === 'admin') {
            return true;
        }
        
        // Check user permissions
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
}
