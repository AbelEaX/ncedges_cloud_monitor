<?php

namespace App\Domain\Entities;

/**
 * User Entity
 * 
 * Represents a user in the system.
 */
class User
{
    public function __construct(
        public ?int $id = null,
        public string $username = '',
        public string $email = '',
        public string $password = '',
        public string $role = 'viewer',
        public ?string $first_name = null,
        public ?string $last_name = null,
        public bool $is_active = true,
        public ?\DateTime $last_login_at = null,
        public ?\DateTime $created_at = null,
        public ?\DateTime $updated_at = null,
    ) {}
    
    /**
     * Get full name
     * 
     * @return string
     */
    public function getFullName(): string
    {
        return trim("{$this->first_name} {$this->last_name}") ?: $this->username;
    }
    
    /**
     * Check if user is admin
     * 
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
