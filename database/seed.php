<?php

/**
 * Database Seeder
 *
 * Usage: php database/seed.php [--fresh]
 *
 * Options:
 *   --fresh  - Truncate tables before seeding
 */

require dirname(__DIR__) . '/bootstrap/app.php';

class DatabaseSeeder
{
    private \App\Infrastructure\Database\Connection $connection;
    private \App\Infrastructure\Logging\Logger $logger;

    public function __construct()
    {
        $this->connection = app(\App\Infrastructure\Database\Connection::class);
        $this->logger = app(\App\Infrastructure\Logging\Logger::class);
    }

    /**
     * Run the seeder
     */
    public function run(bool $fresh = false): void
    {
        echo "[*] Starting database seeder...\n\n";

        try {
            if ($fresh) {
                $this->truncateAll();
            }

            $this->seedRoles();
            echo "\n";
            $this->seedPermissions();
            echo "\n";
            $this->seedUsers();
            echo "\n";
            $this->seedServers();
            echo "\n";
            $this->seedSettings();
            echo "\n";

            echo "[✓] Database seeding completed successfully!\n";
            $this->logger->info('Database seeding completed');
        } catch (Exception $e) {
            echo "[✗] Seeding failed: " . $e->getMessage() . "\n";
            $this->logger->error('Database seeding failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Seed roles table
     */
    private function seedRoles(): void
    {
        $roles = [
            ['name' => 'admin', 'description' => 'Administrator with full access'],
            ['name' => 'manager', 'description' => 'Manager with limited access'],
            ['name' => 'viewer', 'description' => 'Viewer with read-only access'],
        ];

        $pdo = $this->connection->getPDO();
        $stmt = $pdo->prepare('INSERT INTO roles (name, description, created_at) VALUES (?, ?, NOW())');

        foreach ($roles as $role) {
            try {
                $stmt->execute([$role['name'], $role['description']]);
                echo "[✓] Seeded role: {$role['name']}\n";
            } catch (Exception $e) {
                // Role might already exist, continue
                echo "[→] Role already exists: {$role['name']}\n";
            }
        }
    }

    /**
     * Seed permissions table
     */
    private function seedPermissions(): void
    {
        $permissions = [
            // Server permissions
            ['name' => 'server.view', 'description' => 'View servers'],
            ['name' => 'server.create', 'description' => 'Create servers'],
            ['name' => 'server.edit', 'description' => 'Edit servers'],
            ['name' => 'server.delete', 'description' => 'Delete servers'],

            // User permissions
            ['name' => 'user.view', 'description' => 'View users'],
            ['name' => 'user.create', 'description' => 'Create users'],
            ['name' => 'user.edit', 'description' => 'Edit users'],
            ['name' => 'user.delete', 'description' => 'Delete users'],

            // Settings permissions
            ['name' => 'settings.view', 'description' => 'View settings'],
            ['name' => 'settings.edit', 'description' => 'Edit settings'],

            // Reports permissions
            ['name' => 'reports.view', 'description' => 'View reports'],
            ['name' => 'audit.view', 'description' => 'View audit logs'],
        ];

        $pdo = $this->connection->getPDO();
        $stmt = $pdo->prepare('INSERT INTO permissions (name, description, created_at) VALUES (?, ?, NOW())');

        foreach ($permissions as $permission) {
            try {
                $stmt->execute([$permission['name'], $permission['description']]);
                echo "[✓] Seeded permission: {$permission['name']}\n";
            } catch (Exception $e) {
                // Permission might already exist, continue
                echo "[→] Permission already exists: {$permission['name']}\n";
            }
        }
    }

    /**
     * Seed users table
     */
    private function seedUsers(): void
    {
        $users = [
            [
                'username' => 'admin',
                'email' => 'admin@monitor.local',
                'password' => password_hash('admin123', PASSWORD_BCRYPT),
                'role' => 'admin',
                'first_name' => 'System',
                'last_name' => 'Administrator',
            ],
            [
                'username' => 'manager',
                'email' => 'manager@monitor.local',
                'password' => password_hash('manager123', PASSWORD_BCRYPT),
                'role' => 'manager',
                'first_name' => 'Manager',
                'last_name' => 'User',
            ],
            [
                'username' => 'viewer',
                'email' => 'viewer@monitor.local',
                'password' => password_hash('viewer123', PASSWORD_BCRYPT),
                'role' => 'viewer',
                'first_name' => 'Viewer',
                'last_name' => 'User',
            ],
        ];

        $pdo = $this->connection->getPDO();
        $stmt = $pdo->prepare(
            'INSERT INTO users (username, email, password, role, first_name, last_name, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())'
        );

        foreach ($users as $user) {
            try {
                $stmt->execute([
                    $user['username'],
                    $user['email'],
                    $user['password'],
                    $user['role'],
                    $user['first_name'],
                    $user['last_name'],
                ]);
                echo "[✓] Seeded user: {$user['username']} ({$user['email']})\n";
            } catch (Exception $e) {
                // User might already exist, continue
                echo "[→] User already exists: {$user['username']}\n";
            }
        }
    }

    /**
     * Seed servers table
     */
    private function seedServers(): void
    {
        $servers = [
            [
                'name' => 'Web Server 1',
                'host' => 'web1.ncedges.com',
                'port' => 443,
                'description' => 'Primary web server',
                'is_active' => 1,
            ],
            [
                'name' => 'Web Server 2',
                'host' => 'web2.ncedges.com',
                'port' => 443,
                'description' => 'Secondary web server',
                'is_active' => 1,
            ],
            [
                'name' => 'Database Server',
                'host' => 'db.ncedges.com',
                'port' => 3306,
                'description' => 'Primary database server',
                'is_active' => 1,
            ],
            [
                'name' => 'Mail Server',
                'host' => 'mail.ncedges.com',
                'port' => 25,
                'description' => 'Email server',
                'is_active' => 1,
            ],
        ];

        $pdo = $this->connection->getPDO();
        $stmt = $pdo->prepare(
            'INSERT INTO servers (name, host, port, description, is_active, created_at)
             VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)'
        );

        foreach ($servers as $server) {
            try {
                $stmt->execute([
                    $server['name'],
                    $server['host'],
                    $server['port'],
                    $server['description'],
                    $server['is_active'],
                ]);
                echo "[✓] Seeded server: {$server['name']}\n";
            } catch (Exception $e) {
                // Server might already exist, continue
                echo "[→] Server already exists: {$server['name']}\n";
            }
        }
    }

    /**
     * Seed settings table
     */
    private function seedSettings(): void
    {
        $settings = [
            ['key' => 'app.timezone', 'value' => 'UTC', 'description' => 'Application timezone'],
            ['key' => 'app.theme', 'value' => 'light', 'description' => 'Default theme'],
            ['key' => 'monitoring.enabled', 'value' => '1', 'description' => 'Enable monitoring'],
            ['key' => 'notifications.enabled', 'value' => '1', 'description' => 'Enable notifications'],
            ['key' => 'smtp.enabled', 'value' => '1', 'description' => 'Enable SMTP'],
        ];

        $pdo = $this->connection->getPDO();
        $stmt = $pdo->prepare(
            'INSERT INTO settings (key, value, description, created_at)
             VALUES (?, ?, ?, NOW())'
        );

        foreach ($settings as $setting) {
            try {
                $stmt->execute([
                    $setting['key'],
                    $setting['value'],
                    $setting['description'],
                ]);
                echo "[✓] Seeded setting: {$setting['key']}\n";
            } catch (Exception $e) {
                // Setting might already exist, continue
                echo "[→] Setting already exists: {$setting['key']}\n";
            }
        }
    }

    /**
     * Truncate all tables
     */
    private function truncateAll(): void
    {
        echo "[*] Truncating tables...\n\n";

        $pdo = $this->connection->getPDO();
        $tables = [
            'settings',
            'server_metrics',
            'audit_logs',
            'activities',
            'notifications',
            'servers',
            'users',
            'permissions',
            'roles',
        ];

        foreach ($tables as $table) {
            try {
                $pdo->exec("TRUNCATE TABLE $table");
                echo "[✓] Truncated: $table\n";
            } catch (Exception $e) {
                echo "[→] Table not found: $table\n";
            }
        }

        echo "\n";
    }
}

// Get options
$fresh = in_array('--fresh', $argv ?? []);

try {
    $seeder = new DatabaseSeeder();
    $seeder->run($fresh);
} catch (Exception $e) {
    exit(1);
}
