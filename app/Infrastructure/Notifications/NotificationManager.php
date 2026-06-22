<?php

namespace App\Infrastructure\Notifications;

use App\Infrastructure\Mail\MailService;
use App\Infrastructure\Logging\Logger;
use App\Infrastructure\Database\Connection;

/**
 * Notification Manager
 * 
 * Centralized notification system supporting multiple channels.
 * Currently supports: Email, In-App
 * Future-ready for: SMS, Push notifications
 * 
 * Features:
 * - Channel-agnostic architecture
 * - Notification throttling
 * - Retry logic
 * - Event-driven notifications
 */
class NotificationManager
{
    /**
     * Notification configuration
     * 
     * @var array
     */
    protected array $config;
    
    /**
     * Mail service
     * 
     * @var MailService
     */
    protected MailService $mailService;
    
    /**
     * Logger service
     * 
     * @var Logger
     */
    protected Logger $logger;
    
    /**
     * Database connection
     * 
     * @var Connection
     */
    protected Connection $connection;
    
    /**
     * Constructor
     * 
     * @param array $config
     * @param MailService $mailService
     * @param Logger $logger
     * @param Connection $connection
     */
    public function __construct(array $config, MailService $mailService, Logger $logger, Connection $connection)
    {
        $this->config = $config;
        $this->mailService = $mailService;
        $this->logger = $logger;
        $this->connection = $connection;
    }
    
    /**
     * Send notification through email channel
     * 
     * @param string $to
     * @param string $subject
     * @param string $body
     * @param array $options
     * @return bool
     */
    public function sendEmail(string $to, string $subject, string $body, array $options = []): bool
    {
        if (!$this->config['channels']['email']['enabled']) {
            $this->logger->warning('Email channel is disabled', [], 'notifications');
            return false;
        }
        
        // Check throttling
        if ($this->config['throttle']['enabled'] && !$this->canSendNotification($to)) {
            $this->logger->info(
                "Notification throttled for {$to}",
                [],
                'notifications'
            );
            return false;
        }
        
        // Prepare email parameters
        $cc = $options['cc'] ?? [];
        $bcc = $options['bcc'] ?? $this->config['alerts']['bcc'] ?? [];
        
        // Send email
        $result = $this->mailService->send(
            $to,
            $subject,
            $body,
            null,
            null,
            $cc,
            $bcc
        );
        
        // Log notification sent
        if ($result) {
            $this->logNotificationSent($to, 'email', $subject);
        }
        
        return $result;
    }
    
    /**
     * Send SMS notification (future implementation)
     * 
     * @param string $phone
     * @param string $message
     * @param array $options
     * @return bool
     */
    public function sendSMS(string $phone, string $message, array $options = []): bool
    {
        if (!$this->config['channels']['sms']['enabled']) {
            return false;
        }
        
        $username = getenv('AT_USERNAME') ?: 'sandbox';
        $apiKey = getenv('AT_API_KEY') ?: '';
        
        if (empty($apiKey)) {
            $this->logger->warning("Africa's Talking API Key is missing. SMS not sent.", [], 'notifications');
            return false;
        }
        
        $env = getenv('APP_ENV') ?: 'development';
        $url = ($env === 'production' && $username !== 'sandbox') 
             ? 'https://api.africastalking.com/version1/messaging' 
             : 'https://api.sandbox.africastalking.com/version1/messaging';
             
        $postData = http_build_query([
            'username' => $username,
            'to' => $phone,
            'message' => $message
        ]);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
            'apiKey: ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 201 || $httpCode === 200) {
            $this->logNotificationSent($phone, 'sms', 'SMS Alert');
            return true;
        }
        
        $this->logger->error(
            "Failed to send SMS to {$phone}",
            ['response' => $response, 'code' => $httpCode],
            'notifications'
        );
        
        return false;
    }
    
    /**
     * Send push notification (future implementation)
     * 
     * @param string $userId
     * @param string $title
     * @param string $message
     * @param array $options
     * @return bool
     */
    public function sendPush(string $userId, string $title, string $message, array $options = []): bool
    {
        if (!$this->config['channels']['push']['enabled']) {
            return false;
        }
        
        // Push notifications are currently not required for this implementation phase.
        return false;
    }
    
    /**
     * Send in-app notification
     * 
     * @param string $userId
     * @param string $title
     * @param string $message
     * @param string $type
     * @param array $data
     * @return bool
     */
    public function sendInApp(string $userId, string $title, string $message, string $type = 'info', array $data = []): bool
    {
        if (!$this->config['channels']['in_app']['enabled']) {
            return false;
        }
        
        // Store in-app notification in database
        $this->connection->insert('notifications', [
            'recipient' => $userId,
            'subject' => $title,
            'message' => $message,
            'type' => $type,
            'channel' => 'in_app',
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->logger->info(
            "In-app notification created for user {$userId}",
            ['title' => $title, 'type' => $type],
            'notifications'
        );
        
        return true;
    }
    
    /**
     * Check if notification can be sent (throttling)
     * 
     * @param string $recipient
     * @return bool
     */
    protected function canSendNotification(string $recipient): bool
    {
        if (!$this->config['throttle']['enabled']) {
            return true;
        }
        
        // Check notification log for recent notifications to this recipient
        $minutes = $this->config['throttle']['minutes'] ?? 30;
        $timeAgo = date('Y-m-d H:i:s', strtotime("-{$minutes} minutes"));
        
        $count = $this->connection->fetchOne(
            'SELECT COUNT(*) as count FROM notifications WHERE recipient = ? AND created_at >= ?',
            [$recipient, $timeAgo]
        );
        
        return ($count['count'] ?? 0) === 0;
    }
    
    /**
     * Log a sent notification
     * 
     * @param string $recipient
     * @param string $channel
     * @param string $subject
     * @return void
     */
    protected function logNotificationSent(string $recipient, string $channel, string $subject): void
    {
        $this->logger->info(
            "Notification sent via {$channel}",
            [
                'recipient' => $recipient,
                'subject' => $subject,
            ],
            'notifications'
        );
    }
    
    /**
     * Get notification event templates
     * 
     * @return array
     */
    public function getEventTemplates(): array
    {
        return $this->config['events'] ?? [];
    }
}
