<?php

namespace App\Domain\Entities;

/**
 * Server Entity
 * 
 * Represents a server in the monitoring system.
 */
class Server implements \JsonSerializable
{
    public function __construct(
        public ?int $id = null,
        public string $name = '',
        public string $host = '',
        public int $port = 443,
        public ?string $description = null,
        public string $status = 'unknown',
        public ?string $group_name = null,
        public bool $is_active = true,
        public ?\DateTime $last_check_at = null,
        public ?\DateTime $last_status_change_at = null,
        public bool $alert_sent = false,
        public ?\DateTime $created_at = null,
        public ?\DateTime $updated_at = null,
    ) {}

    /**
     * Specify data which should be serialized to JSON
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'host' => $this->host,
            'port' => $this->port,
            'description' => $this->description,
            'status' => $this->status,
            'group_name' => $this->group_name,
            'is_active' => $this->is_active,
            'last_check_at' => $this->last_check_at?->format(\DateTimeInterface::ATOM),
            'last_status_change_at' => $this->last_status_change_at?->format(\DateTimeInterface::ATOM),
            'alert_sent' => $this->alert_sent,
            'created_at' => $this->created_at?->format(\DateTimeInterface::ATOM),
            'updated_at' => $this->updated_at?->format(\DateTimeInterface::ATOM),
        ];
    }
    
    /**
     * Check if server is online
     * 
     * @return bool
     */
    public function isOnline(): bool
    {
        return $this->status === 'online';
    }
    
    /**
     * Check if server is offline
     * 
     * @return bool
     */
    public function isOffline(): bool
    {
        return $this->status === 'offline';
    }
    
    /**
     * Get status badge color
     * 
     * @return string
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'online' => '#66bb6a',
            'offline' => '#ef5350',
            'warning' => '#ffc107',
            'maintenance' => '#29b6f6',
            default => '#757575',
        };
    }
}
