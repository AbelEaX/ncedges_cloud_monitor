<?php

/**
 * Reports Export API Endpoint
 *
 * GET /api/reports/export.php?format=pdf&range=7d
 */

require dirname(__DIR__, 3) . '/bootstrap/app.php';

$auth = app(\App\Infrastructure\Authentication\AuthenticationService::class);
if (!$auth->isAuthenticated() || !$auth->hasPermission('reports.view')) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
}

try {
    $format = $_GET['format'] ?? 'csv';
    $range = $_GET['range'] ?? '7d';

    if ($format === 'csv') {
        // Export as CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="report-' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // Write headers
        fputcsv($output, ['Server', 'Uptime 24h', 'Uptime 7d', 'Uptime 30d', 'Status']);

        // Write sample data
        $data = [
            ['Web Server 1', '99.9%', '99.95%', '99.87%', 'Online'],
            ['Web Server 2', '100%', '99.98%', '99.92%', 'Online'],
            ['Database Server', '99.95%', '99.99%', '99.98%', 'Online'],
            ['Mail Server', '100%', '99.99%', '99.95%', 'Online'],
        ];

        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
    } else if ($format === 'pdf') {
        // For PDF, we'd use a library like TCPDF or DomPDF
        // For now, just return a message
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'PDF export would be generated here',
            'note' => 'Install TCPDF or DomPDF for PDF support'
        ]);
    } else {
        throw new Exception('Invalid export format');
    }

    // Log action
    $audit = app(\App\Infrastructure\Logging\AuditService::class);
    $audit->log('export', 'reports', null, $auth->user()->id, "Exported report as $format");
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'success' => false,
        'message' => 'Failed to export report',
        'errors' => [$e->getMessage()]
    ]);
}
