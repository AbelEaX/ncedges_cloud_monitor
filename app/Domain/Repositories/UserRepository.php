<?php

namespace App\Domain\Repositories;

/**
 * User Repository Interface
 * 
 * Defines contract for User data access operations.
 */
interface UserRepository
{
    /**
     * Find user by ID
     * 
     * @param int $id
     * @return \App\Domain\Entities\User|null
     */
    public function findById(int $id): ?\App\Domain\Entities\User;
    
    /**
     * Find user by username
     * 
     * @param string $username
     * @return \App\Domain\Entities\User|null
     */
    public function findByUsername(string $username): ?\App\Domain\Entities\User;
    
    /**
     * Find all users
     * 
     * @return array
     */
    public function findAll(): array;
    
    /**
     * Create a new user
     * 
     * @param \App\Domain\Entities\User $user
     * @return int
     */
    public function create(\App\Domain\Entities\User $user): int;
    
    /**
     * Update user
     * 
     * @param \App\Domain\Entities\User $user
     * @return bool
     */
    public function update(\App\Domain\Entities\User $user): bool;
    
    /**
     * Delete user
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
