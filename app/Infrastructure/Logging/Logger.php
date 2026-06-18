<?php

namespace App\Infrastructure\Logging;

/**
 * Logger Service
 * 
 * Centralized logging service for the application.
 * Supports multiple log channels: application, security, audit, monitoring, etc.
 */
class Logger
{
    /**
     * Log configuration
     * 
     * @var array
     */
    protected array $config;
    
    /**
     * Constructor
     * 
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->ensureLogDirectories();
    }
    
    /**
     * Ensure log directories exist
     * 
     * @return void
     */
    protected function ensureLogDirectories(): void
    {
        $logPath = dirname(__DIR__, 2) . '/storage/logs';
        
        if (!is_dir($logPath)) {
            mkdir($logPath, 0755, true);
        }
    }
    
    /**
     * Log an info message
     * 
     * @param string $message
     * @param array $context
     * @param string $channel
     * @return void
     */
    public function info(string $message, array $context = [], string $channel = 'application'): void
    {
        $this->log('INFO', $message, $context, $channel);
    }
    
    /**
     * Log a debug message
     * 
     * @param string $message
     * @param array $context
     * @param string $channel
     * @return void
     */
    public function debug(string $message, array $context = [], string $channel = 'application'): void
    {
        if (config('app.debug')) {
            $this->log('DEBUG', $message, $context, $channel);
        }
    }
    
    /**
     * Log a warning message
     * 
     * @param string $message
     * @param array $context
     * @param string $channel
     * @return void
     */
    public function warning(string $message, array $context = [], string $channel = 'application'): void
    {
        $this->log('WARNING', $message, $context, $channel);
    }
    
    /**
     * Log an error message
     * 
     * @param string $message
     * @param array $context
     * @param string $channel
     * @return void
     */
    public function error(string $message, array $context = [], string $channel = 'application'): void
    {
        $this->log('ERROR', $message, $context, $channel);
    }
    
    /**
     * Log a critical message
     * 
     * @param string $message
     * @param array $context
     * @param string $channel
     * @return void
     */
    public function critical(string $message, array $context = [], string $channel = 'application'): void
    {
        $this->log('CRITICAL', $message, $context, $channel);
    }
    
    /**
     * Write log message
     * 
     * @param string $level
     * @param string $message
     * @param array $context
     * @param string $channel
     * @return void
     */
    protected function log(string $level, string $message, array $context = [], string $channel = 'application'): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? json_encode($context) : '';
        
        $logMessage = "[{$timestamp}] [{$level}] {$message}";
        if ($contextStr) {
            $logMessage .= " | Context: {$contextStr}";
        }
        $logMessage .= PHP_EOL;
        
        // Get log file path based on channel
        $logFile = $this->getLogFile($channel);
        
        // Ensure directory exists
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Append to log file
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Get log file path for channel
     * 
     * @param string $channel
     * @return string
     */
    protected function getLogFile(string $channel): string
    {
        $logPath = dirname(__DIR__, 2) . '/storage/logs';
        $filename = $this->config['channels'][$channel]['path'] ?? "{$logPath}/{$channel}.log";
        
        return $filename;
    }
}
