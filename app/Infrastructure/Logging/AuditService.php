<?php

namespace App\Infrastructure\Logging;

use App\Infrastructure\Database\Connection;

/**
 * Audit Service
 * 
 * Centralized audit logging for tracking all user actions and system events.
 * Maintains a complete audit trail for compliance and security.
 * 
 * Logged Events:
 * - User login/logout
 * - Server add/update/delete
 * - Settings changes
 * - User management
 * - Permission changes
 */
class AuditService
{
    /**
     * Database connection
     * 
     * @var Connection
     */
    protected Connection $connection;
    
    /**
     * Logger service
     * 
     * @var Logger
     */
    protected Logger $logger;
    
    /**
     * Constructor
     * 
     * @param Connection $connection
     * @param Logger $logger
     */
    public function __construct(Connection $connection, Logger $logger)
    {
        $this->connection = $connection;
        $this->logger = $logger;
    }
    
    /**
     * Log an audit event
     * 
     * @param string $action
     * @param string $entity_type
     * @param int|null $entity_id
     * @param int|null $user_id
     * @param array $details
     * @param string $severity
     * @return void
     */
    public function log(
        string $action,
        string $entity_type,
        ?int $entity_id = null,
        ?int $user_id = null,
        array $details = [],
        string $severity = 'info'
    ): void {
        if (!config('app.features.audit_logging_enabled', true)) {
            return;
        }
        // Get user ID from session if not provided
        if ($user_id === null && isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        // Insert into audit_logs table
        $this->connection->insert('audit_logs', [
            'action' => $action,
            'entity_type' => $entity_type,
            'entity_id' => $entity_id,
            'user_id' => $user_id,
            'details' => json_encode($details),
            'severity' => $severity,
            'ip_address' => $ip_address,
            'user_agent' => $user_agent,
            'created_at' => $timestamp,
        ]);
        
        // Also log to security log
        $this->logger->info(
            "Audit: {$action} on {$entity_type}",
            array_merge($details, [
                'entity_id' => $entity_id,
                'user_id' => $user_id,
                'ip_address' => $ip_address,
            ]),
            'audit'
        );
    }
    
    /**
     * Log user login
     * 
     * @param int $user_id
     * @param string $username
     * @return void
     */
    public function logLogin(int $user_id, string $username): void
    {
        $this->log(
            'login',
            'user',
            $user_id,
            $user_id,
            ['username' => $username],
            'info'
        );
        
        $this->logger->info(
            "User login: {$username}",
            ['user_id' => $user_id],
            'authentication'
        );
    }
    
    /**
     * Log user logout
     * 
     * @param int $user_id
     * @param string $username
     * @return void
     */
    public function logLogout(int $user_id, string $username): void
    {
        $this->log(
            'logout',
            'user',
            $user_id,
            $user_id,
            ['username' => $username],
            'info'
        );
        
        $this->logger->info(
            "User logout: {$username}",
            ['user_id' => $user_id],
            'authentication'
        );
    }
    
    /**
     * Log server action
     * 
     * @param string $action (create, update, delete)
     * @param int $server_id
     * @param string $server_name
     * @param array $changes
     * @param int|null $user_id
     * @return void
     */
    public function logServerAction(
        string $action,
        int $server_id,
        string $server_name,
        array $changes = [],
        ?int $user_id = null
    ): void {
        $this->log(
            $action,
            'server',
            $server_id,
            $user_id,
            ['server_name' => $server_name, 'changes' => $changes],
            'info'
        );
    }
    
    /**
     * Log settings change
     * 
     * @param string $setting_key
     * @param string $old_value
     * @param string $new_value
     * @param int|null $user_id
     * @return void
     */
    public function logSettingsChange(
        string $setting_key,
        string $old_value,
        string $new_value,
        ?int $user_id = null
    ): void {
        $this->log(
            'update',
            'setting',
            null,
            $user_id,
            [
                'setting' => $setting_key,
                'old_value' => '***' . substr($old_value, -2), // Mask sensitive values
                'new_value' => '***' . substr($new_value, -2),
            ],
            'warning'
        );
    }
    
    /**
     * Get audit log entries
     * 
     * @param int $limit
     * @param int $offset
     * @param array $filters
     * @return array
     */
    public function getAuditLogs(int $limit = 100, int $offset = 0, array $filters = []): array
    {
        // Build query
        $query = 'SELECT * FROM audit_logs';
        $params = [];
        $conditions = [];

        if (!empty($filters['user_id'])) {
            $conditions[] = 'user_id = ?';
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['action'])) {
            $conditions[] = 'action = ?';
            $params[] = $filters['action'];
        }
        
        if (!empty($filters['entity_type'])) {
            $conditions[] = 'entity_type = ?';
            $params[] = $filters['entity_type'];
        }

        if (!empty($conditions)) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $query .= ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;

        $pdo = $this->connection->getPDO();
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Export audit logs
     * 
     * @param string $format (csv, pdf, excel)
     * @param array $filters
     * @return string
     */
    public function exportAuditLogs(string $format, array $filters = []): string
    {
        $logs = $this->getAuditLogs(PHP_INT_MAX, 0, $filters);
        
        return match($format) {
            'csv' => $this->exportAsCSV($logs),
            'excel' => $this->exportAsExcel($logs),
            'pdf' => $this->exportAsPDF($logs),
            default => throw new \Exception("Unsupported export format: {$format}"),
        };
    }
    
    /**
     * Export logs as CSV
     * 
     * @param array $logs
     * @return string
     */
    protected function exportAsCSV(array $logs): string
    {
        ob_start();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="audit-logs.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Header row
        fputcsv($output, ['Timestamp', 'User', 'Action', 'Entity Type', 'Details', 'IP Address']);
        
        // Data rows
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['created_at'] ?? '',
                $log['user_id'] ?? '',
                $log['action'] ?? '',
                $log['entity_type'] ?? '',
                $log['details'] ?? '',
                $log['ip_address'] ?? '',
            ]);
        }
        
        fclose($output);
        return ob_get_clean();
    }
    
    /**
     * Export logs as Excel
     * 
     * @param array $logs
     * @return string
     */
    protected function exportAsExcel(array $logs): string
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
            // Fallback to CSV if library is not installed
            return $this->exportAsCSV($logs);
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Headers
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Action');
        $sheet->setCellValue('C1', 'Entity Type');
        $sheet->setCellValue('D1', 'Entity ID');
        $sheet->setCellValue('E1', 'User ID');
        $sheet->setCellValue('F1', 'Date');
        
        // Data
        $row = 2;
        foreach ($logs as $log) {
            $sheet->setCellValue('A' . $row, $log['id']);
            $sheet->setCellValue('B' . $row, $log['action']);
            $sheet->setCellValue('C' . $row, $log['entity_type']);
            $sheet->setCellValue('D' . $row, $log['entity_id']);
            $sheet->setCellValue('E' . $row, $log['user_id']);
            $sheet->setCellValue('F' . $row, $log['created_at']);
            $row++;
        }
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }
    
    /**
     * Export logs as PDF
     * 
     * @param array $logs
     * @return string
     */
    protected function exportAsPDF(array $logs): string
    {
        if (!class_exists(\TCPDF::class)) {
            return '';
        }

        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Monitor System');
        $pdf->SetTitle('Audit Logs Export');
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();
        
        $html = '<h2>Audit Logs</h2>';
        $html .= '<table border="1" cellpadding="4">';
        $html .= '<thead><tr>';
        $html .= '<th><b>Action</b></th>';
        $html .= '<th><b>Entity</b></th>';
        $html .= '<th><b>User ID</b></th>';
        $html .= '<th><b>Date</b></th>';
        $html .= '</tr></thead><tbody>';
        
        foreach ($logs as $log) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($log['action']) . '</td>';
            $html .= '<td>' . htmlspecialchars($log['entity_type']) . ' (' . htmlspecialchars($log['entity_id'] ?? '') . ')</td>';
            $html .= '<td>' . htmlspecialchars($log['user_id'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($log['created_at']) . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table>';
        
        $pdf->writeHTML($html, true, false, true, false, '');
        
        return $pdf->Output('audit_logs.pdf', 'S');
    }
}
