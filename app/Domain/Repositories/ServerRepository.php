<?php

namespace App\Domain\Repositories;

/**
 * Server Repository Interface
 * 
 * Defines contract for Server data access operations.
 */
interface ServerRepository
{
    /**
     * Find server by ID
     * 
     * @param int $id
     * @return \App\Domain\Entities\Server|null
     */
    public function findById(int $id): ?\App\Domain\Entities\Server;
    
    /**
     * Find all active servers
     * 
     * @return array
     */
    public function findActive(): array;
    
    /**
     * Find all servers
     * 
     * @return array
     */
    public function findAll(): array;
    
    /**
     * Create a new server
     * 
     * @param \App\Domain\Entities\Server $server
     * @return int
     */
    public function create(\App\Domain\Entities\Server $server): int;
    
    /**
     * Update server
     * 
     * @param \App\Domain\Entities\Server $server
     * @return bool
     */
    public function update(\App\Domain\Entities\Server $server): bool;
    
    /**
     * Delete server
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
