<?php

namespace App\Infrastructure\Repositories;

use App\Infrastructure\Database\Connection;

/**
 * Settings Repository
 * 
 * Manages configuration settings in the database.
 */
class SettingsRepository
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
     * Get a setting value by key
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        try {
            $row = $this->connection->fetchOne(
                'SELECT value, type FROM settings WHERE key = ?',
                [$key]
            );

            if (!$row) {
                return $default;
            }

            $value = $row['value'];
            $type = $row['type'];

            return match ($type) {
                'integer', 'int' => (int) $value,
                'boolean', 'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
                'float', 'double' => (float) $value,
                default => $value,
            };
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * Set/save a setting key-value pair
     * 
     * @param string $key
     * @param mixed $value
     * @param string|null $type
     * @param string|null $description
     * @return void
     */
    public function set(string $key, $value, ?string $type = null, ?string $description = null): void
    {
        if ($type === null) {
            if (is_bool($value)) {
                $type = 'boolean';
            } elseif (is_int($value)) {
                $type = 'integer';
            } elseif (is_float($value)) {
                $type = 'float';
            } else {
                $type = 'string';
            }
        }

        $stringValue = is_bool($value) ? ($value ? '1' : '0') : (string) $value;

        // Check if key exists
        $exists = $this->connection->fetchOne(
            'SELECT 1 FROM settings WHERE key = ?',
            [$key]
        );

        if ($exists) {
            $this->connection->update(
                'settings',
                [
                    'value' => $stringValue,
                    'type' => $type,
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                'key = ?',
                [$key]
            );
        } else {
            $this->connection->insert(
                'settings',
                [
                    'key' => $key,
                    'value' => $stringValue,
                    'type' => $type,
                    'description' => $description,
                ]
            );
        }
    }

    /**
     * Get all settings as an associative array
     * 
     * @return array
     */
    public function getAll(): array
    {
        try {
            $rows = $this->connection->fetchAll('SELECT key, value, type FROM settings');
            $settings = [];
            foreach ($rows as $row) {
                $value = $row['value'];
                $type = $row['type'];
                $settings[$row['key']] = match ($type) {
                    'integer', 'int' => (int) $value,
                    'boolean', 'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
                    'float', 'double' => (float) $value,
                    default => $value,
                };
            }
            return $settings;
        } catch (\Exception $e) {
            return [];
        }
    }
}
