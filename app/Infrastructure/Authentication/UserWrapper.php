<?php

namespace App\Infrastructure\Authentication;

/**
 * User Wrapper
 * 
 * Provides a clean interface for accessing user data.
 * Acts as a wrapper around the user array data.
 */
class UserWrapper
{
    /**
     * User data
     * 
     * @var array
     */
    protected array $data;
    
    /**
     * Constructor
     * 
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }
    
    /**
     * Get user ID
     * 
     * @return int
     */
    public function getId(): int
    {
        return (int) $this->data['id'];
    }
    
    /**
     * Get username
     * 
     * @return string
     */
    public function getUsername(): string
    {
        return $this->data['username'] ?? '';
    }
    
    /**
     * Get user role
     * 
     * @return string
     */
    public function getRole(): string
    {
        return $this->data['role'] ?? 'viewer';
    }
    
    /**
     * Get email
     * 
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->data['email'] ?? null;
    }
    
    /**
     * Get user data as array
     * 
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }
    
    /**
     * Magic getter for property access
     * 
     * @param string $property
     * @return mixed
     */
    public function __get(string $property)
    {
        return $this->data[$property] ?? null;
    }
    
    /**
     * Check if property exists
     * 
     * @param string $property
     * @return bool
     */
    public function __isset(string $property): bool
    {
        return isset($this->data[$property]);
    }
}
