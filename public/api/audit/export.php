<?php
/**
 * API: Export Audit Logs
 * 
 * Exports audit logs in requested format (CSV, PDF, Excel)
 */
require __DIR__ . '/../../bootstrap/app.php';

use App\Presentation\Responses\ApiResponse;
use App\Presentation\Middleware\AuthenticationMiddleware;

// Check authentication
$auth = new AuthenticationMiddleware();
$auth->handle();

// Check authorization
$auth_service = app(\App\Infrastructure\Authentication\AuthenticationService::class);
if (!$auth_service->hasPermission('audit.export')) {
    ApiResponse::forbidden();
}

// Get export format
$format = $_GET['format'] ?? 'csv';

if (!in_array($format, ['csv', 'pdf', 'excel'])) {
    ApiResponse::validationError(['format' => 'Invalid format. Use csv, pdf, or excel.']);
}

// Get filters
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$action = $_GET['action'] ?? '';
$severity = $_GET['severity'] ?? '';

$filters = [];
if ($dateFrom) $filters['date_from'] = $dateFrom;
if ($dateTo) $filters['date_to'] = $dateTo;
if ($action) $filters['action'] = $action;
if ($severity) $filters['severity'] = $severity;

$auditService = app(\App\Infrastructure\Logging\AuditService::class);
$logger = app(\App\Infrastructure\Logging\Logger::class);

try {
    // For now, return CSV by default
    // TODO: Implement PDF and Excel exports
    
    $logs = $auditService->getAuditLogs(PHP_INT_MAX, 0, $filters);
    $csv = $auditService->exportAuditLogs($format, $filters);
    
    // Log the export
    $logger->info(
        "Audit logs exported in {$format} format",
        ['filters' => $filters],
        'audit'
    );
    
    // Return the exported data
    header('Content-Type: text/csv; charset=utf-8');
    header("Content-Disposition: attachment; filename=\"audit-logs-{$format}.csv\"");
    echo $csv;
    
} catch (\Exception $e) {
    $logger->error("Audit export error: " . $e->getMessage(), [], 'audit');
    ApiResponse::error('Error exporting audit logs: ' . $e->getMessage(), [], 500);
}
