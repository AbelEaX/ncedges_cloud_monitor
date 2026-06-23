<?php

/**
 * Reports Export API Endpoint
 *
 * GET /api/reports/export.php?format=pdf&range=7d
 */

require dirname(__DIR__, 3) . '/bootstrap/app.php';

$auth = app(\App\Infrastructure\Authentication\AuthenticationService::class);
if (!$auth->isAuthenticated() || !$auth->hasPermission('reports.export')) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
}

try {
    $format = $_GET['format'] ?? 'csv';
    $range = $_GET['range'] ?? '7d';

    // Fetch actual server metrics data from Database
    $connection = app(\App\Infrastructure\Database\Connection::class);
    $sql = "
    SELECT 
        s.name as server_name,
        s.status as current_status,
        (SUM(CASE WHEN sm.status = 'online' AND sm.checked_at >= datetime('now', '-1 day') THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(CASE WHEN sm.checked_at >= datetime('now', '-1 day') THEN 1 END), 0)) as uptime_24h,
        (SUM(CASE WHEN sm.status = 'online' AND sm.checked_at >= datetime('now', '-7 days') THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(CASE WHEN sm.checked_at >= datetime('now', '-7 days') THEN 1 END), 0)) as uptime_7d,
        (SUM(CASE WHEN sm.status = 'online' AND sm.checked_at >= datetime('now', '-30 days') THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(CASE WHEN sm.checked_at >= datetime('now', '-30 days') THEN 1 END), 0)) as uptime_30d
    FROM servers s
    LEFT JOIN server_metrics sm ON s.id = sm.server_id
    GROUP BY s.id, s.name, s.status
    ORDER BY s.name ASC
    ";
    
    $uptime = $connection->fetchAll($sql);
    
    $data = [];
    foreach ($uptime as $row) {
        $data[] = [
            $row['server_name'],
            $row['uptime_24h'] !== null ? number_format((float)$row['uptime_24h'], 2) . '%' : '100.00%',
            $row['uptime_7d'] !== null ? number_format((float)$row['uptime_7d'], 2) . '%' : '100.00%',
            $row['uptime_30d'] !== null ? number_format((float)$row['uptime_30d'], 2) . '%' : '100.00%',
            ucfirst($row['current_status'])
        ];
    }

    // Log action BEFORE streaming the response (as stream() and exit() will stop execution)
    $audit = app(\App\Infrastructure\Logging\AuditService::class);
    $userId = $auth->user() ? $auth->user()->getId() : 0;
    $audit->log('export', 'reports', null, $userId, ['message' => "Exported report as $format"]);

    if ($format === 'csv') {
        // Export as CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="report-' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Server', 'Uptime 24h', 'Uptime 7d', 'Uptime 30d', 'Status'], ',', '"', '\\');

        foreach ($data as $row) {
            fputcsv($output, $row, ',', '"', '\\');
        }

        // Do NOT fclose($output) as it can cause ERR_INVALID_RESPONSE in some PHP SAPIs
        exit;
        
    } else if ($format === 'xls' || $format === 'xlsx' || $format === 'excel') {
        // Export as Excel using PhpSpreadsheet
        require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
        
        if (!class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
            throw new Exception('PhpSpreadsheet library is not installed');
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('System Health Report');

        // Headers
        $sheet->setCellValue('A1', 'Server');
        $sheet->setCellValue('B1', 'Uptime 24h');
        $sheet->setCellValue('C1', 'Uptime 7d');
        $sheet->setCellValue('D1', 'Uptime 30d');
        $sheet->setCellValue('E1', 'Status');

        // Data
        $rowIdx = 2;
        foreach ($data as $row) {
            $sheet->setCellValue('A' . $rowIdx, $row[0]);
            $sheet->setCellValue('B' . $rowIdx, $row[1]);
            $sheet->setCellValue('C' . $rowIdx, $row[2]);
            $sheet->setCellValue('D' . $rowIdx, $row[3]);
            $sheet->setCellValue('E' . $rowIdx, $row[4]);
            $rowIdx++;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="server-report-' . date('Y-m-d') . '.xlsx"');
        
        $writer->save('php://output');
        exit;
        
    } else if ($format === 'pdf') {
        // Export as PDF using Dompdf
        require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
        
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isHtml5ParserEnabled', true);
        
        $dompdf = new \Dompdf\Dompdf($options);
        
        // Table HTML
        $html = '<div style="text-align: center; font-family: Helvetica, sans-serif;">
            <h2>System Health Report</h2>
            <p>Generated on: ' . date('Y-m-d H:i:s') . '</p>
            <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr style="background-color:#f1f5f9;">
                    <th style="width: 30%; text-align: left;">Server</th>
                    <th style="width: 15%;">Uptime 24h</th>
                    <th style="width: 15%;">Uptime 7d</th>
                    <th style="width: 15%;">Uptime 30d</th>
                    <th style="width: 25%;">Status</th>
                </tr>
            </thead>
            <tbody>';
            
        foreach ($data as $row) {
            $statusColor = strtolower($row[4]) === 'online' ? '#10b981' : '#ef4444';
            $html .= '<tr>
                <td style="text-align:left;">' . htmlspecialchars($row[0]) . '</td>
                <td>' . htmlspecialchars($row[1]) . '</td>
                <td>' . htmlspecialchars($row[2]) . '</td>
                <td>' . htmlspecialchars($row[3]) . '</td>
                <td style="color:' . $statusColor . '; font-weight:bold;">' . htmlspecialchars($row[4]) . '</td>
            </tr>';
        }
        
        $html .= '</tbody></table></div>';
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $dompdf->stream('server-report-' . date('Y-m-d') . '.pdf', array("Attachment" => true));
        exit;
        
    } else {
        throw new Exception('Invalid export format');
    }
    
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'success' => false,
        'message' => 'Failed to export report',
        'errors' => [$e->getMessage()]
    ]);
}
