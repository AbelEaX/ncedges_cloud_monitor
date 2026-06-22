<?php

namespace App\Infrastructure\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Infrastructure\Logging\Logger;

/**
 * Mail Service
 * 
 * Handles sending emails via SMTP.
 * Uses PHPMailer as the underlying mail library.
 * Replaces SendGrid completely with native SMTP.
 */
class MailService
{
    /**
     * SMTP configuration
     * 
     * @var array
     */
    protected array $config;
    
    /**
     * Logger instance
     * 
     * @var Logger
     */
    protected Logger $logger;
    
    /**
     * Constructor
     * 
     * @param array $config
     * @param Logger $logger
     */
    public function __construct(array $config, Logger $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }
    
    /**
     * Send an email
     * 
     * @param string $to
     * @param string $subject
     * @param string $body
     * @param string|null $fromAddress
     * @param string|null $fromName
     * @param array $cc
     * @param array $bcc
     * @param array $attachments
     * @return bool
     */
    public function send(
        string $to,
        string $subject,
        string $body,
        ?string $fromAddress = null,
        ?string $fromName = null,
        array $cc = [],
        array $bcc = [],
        array $attachments = []
    ): bool {
        try {
            $mailer = $this->createMailer();
            
            // Set from
            $fromAddress = $fromAddress ?? $this->config['from']['address'];
            $fromName = $fromName ?? $this->config['from']['name'];
            $mailer->setFrom($fromAddress, $fromName);
            
            // Set reply to
            $replyTo = $this->config['reply_to'];
            $mailer->addReplyTo($replyTo['address'], $replyTo['name']);
            
            // Add recipients
            $mailer->addAddress($to);
            
            // Add CC recipients
            foreach ($cc as $ccAddress) {
                $mailer->addCC($ccAddress);
            }
            
            // Add BCC recipients
            foreach ($bcc as $bccAddress) {
                $mailer->addBCC($bccAddress);
            }
            
            // Set subject and body
            $mailer->Subject = $subject;
            $mailer->Body = $body;
            $mailer->isHTML(true);
            
            // Add attachments
            foreach ($attachments as $attachment) {
                $mailer->addAttachment($attachment);
            }
            
            // Send
            if (!$mailer->send()) {
                $this->logger->error(
                    "Failed to send email to {$to}",
                    ['error' => $mailer->ErrorInfo],
                    'notifications'
                );
                return false;
            }
            
            $this->logger->info(
                "Email sent successfully to {$to}",
                ['subject' => $subject],
                'notifications'
            );
            
            return true;
        } catch (Exception $e) {
            $this->logger->error(
                "Exception while sending email: " . $e->getMessage(),
                ['to' => $to],
                'notifications'
            );
            return false;
        }
    }
    
    /**
     * Create and configure PHPMailer instance
     * 
     * @return PHPMailer
     * @throws Exception
     */
    protected function createMailer(): PHPMailer
    {
        $mailer = new PHPMailer(true);
        
        // Set SMTP
        $mailer->isSMTP();
        $mailer->Host = $this->config['smtp']['host'];
        $mailer->Port = $this->config['smtp']['port'];
        $mailer->SMTPAuth = true;
        $mailer->Username = $this->config['smtp']['username'];
        $mailer->Password = $this->config['smtp']['password'];
        $mailer->SMTPSecure = $this->config['smtp']['encryption'];
        $mailer->Timeout = $this->config['smtp']['timeout'];
        
        // SSL/TLS verification
        $verifySsl = $this->config['smtp']['verify_ssl'] ?? true;
        if (!$verifySsl) {
            $mailer->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ]
            ];
        }
        
        return $mailer;
    }
    
    /**
     * Send a test email
     * 
     * @param string $to
     * @return bool
     */
    public function sendTest(string $to): bool
    {
        $subject = "Test Email - Monitor System";
        $body = $this->buildTestEmailBody();
        
        return $this->send($to, $subject, $body);
    }
    
    /**
     * Build test email body
     * 
     * @return string
     */
    protected function buildTestEmailBody(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #ffc107; color: #000; padding: 20px; text-align: center; }
        .content { padding: 20px; border: 1px solid #ddd; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>SMTP Test Successful</h1>
        </div>
        <div class="content">
            <p>This is a test email from the Monitor System.</p>
            <p>Your SMTP configuration is working correctly!</p>
            <p><strong>Timestamp:</strong> {$_SERVER['REQUEST_TIME']}</p>
        </div>
        <div class="footer">
            <p>© 2026 Nova Cloud Hosting. Monitor System.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
