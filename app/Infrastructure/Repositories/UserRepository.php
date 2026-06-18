<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepository as UserRepositoryInterface;
use App\Infrastructure\Database\Connection;

/**
 * User Repository Implementation
 * 
 * Implements UserRepository interface using PDO.
 */
class UserRepository implements UserRepositoryInterface
{
    /**
     * Database connection
     * 
     * @var Connection
     */
    protected Connection $connection;
    
    /**
     * Constructor
     * 
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    
    /**
     * Find user by ID
     * 
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User
    {
        $row = $this->connection->fetchOne(
            'SELECT * FROM users WHERE id = ?',
            [$id]
        );
        
        return $row ? $this->hydrate($row) : null;
    }
    
    /**
     * Find user by username
     * 
     * @param string $username
     * @return User|null
     */
    public function findByUsername(string $username): ?User
    {
        $row = $this->connection->fetchOne(
            'SELECT * FROM users WHERE username = ?',
            [$username]
        );
        
        return $row ? $this->hydrate($row) : null;
    }
    
    /**
     * Find all users
     * 
     * @return array
     */
    public function findAll(): array
    {
        $rows = $this->connection->fetchAll('SELECT * FROM users ORDER BY username');
        return array_map(fn($row) => $this->hydrate($row), $rows);
    }
    
    /**
     * Create a new user
     * 
     * @param User $user
     * @return int
     */
    public function create(User $user): int
    {
        return $this->connection->insert('users', [
            'username' => $user->username,
            'email' => $user->email,
            'password' => $user->password,
            'role' => $user->role,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'is_active' => $user->is_active ? 1 : 0,
        ]);
    }
    
    /**
     * Update user
     * 
     * @param User $user
     * @return bool
     */
    public function update(User $user): bool
    {
        $count = $this->connection->update(
            'users',
            [
                'username' => $user->username,
                'email' => $user->email,
                'password' => $user->password,
                'role' => $user->role,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'is_active' => $user->is_active ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            'id = ?',
            [$user->id]
        );
        
        return $count > 0;
    }
    
    /**
     * Delete user
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $count = $this->connection->delete('users', 'id = ?', [$id]);
        return $count > 0;
    }
    
    /**
     * Hydrate User entity from database row
     * 
     * @param array $row
     * @return User
     */
    protected function hydrate(array $row): User
    {
        return new User(
            id: (int) $row['id'],
            username: $row['username'],
            email: $row['email'],
            password: $row['password'],
            role: $row['role'],
            first_name: $row['first_name'],
            last_name: $row['last_name'],
            is_active: (bool) $row['is_active'],
            last_login_at: $row['last_login_at'] ? new \DateTime($row['last_login_at']) : null,
            created_at: $row['created_at'] ? new \DateTime($row['created_at']) : null,
            updated_at: $row['updated_at'] ? new \DateTime($row['updated_at']) : null,
        );
    }
}
