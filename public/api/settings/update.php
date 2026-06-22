<?php

/**
 * Update Settings API Endpoint
 *
 * POST /api/settings/update.php?section=general
 */

require dirname(__DIR__, 3) . '/bootstrap/app.php';

$auth = app(\App\Infrastructure\Authentication\AuthenticationService::class);
if (!$auth->isAuthenticated() || (!$auth->hasPermission('settings.edit') && !$auth->hasPermission('settings.update'))) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!$auth->validateCsrfToken($csrfToken)) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

try {
    $section = $_GET['section'] ?? 'general';
    $input = json_decode(file_get_contents('php://input'), true);

    $settingsRepo = app(\App\Infrastructure\Repositories\SettingsRepository::class);

    if ($section === 'general') {
        if (isset($input['app_name'])) {
            $settingsRepo->set('app.name', htmlspecialchars(trim($input['app_name']), ENT_QUOTES, 'UTF-8'), 'string', 'Application name');
        }
        if (isset($input['app_url'])) {
            $settingsRepo->set('app.url', filter_var($input['app_url'], FILTER_SANITIZE_URL), 'string', 'Application URL');
        }
        if (isset($input['timezone'])) {
            $settingsRepo->set('app.timezone', $input['timezone'], 'string', 'Application timezone');
        }
        if (isset($input['locale'])) {
            $settingsRepo->set('app.locale', $input['locale'], 'string', 'Application locale');
        }
    } elseif ($section === 'theme') {
        if (isset($input['theme'])) {
            $theme = $input['theme'];
            $settingsRepo->set('theme.default', $theme, 'string', 'Default theme');
            
            // Instantly apply the theme to current user session & cookie
            $_SESSION['theme'] = $theme;
            setcookie('theme', $theme, time() + (365 * 24 * 60 * 60), '/');
        }
        $userCanChange = isset($input['user_can_change_theme']) && ($input['user_can_change_theme'] === 'on' || $input['user_can_change_theme'] == 1);
        $settingsRepo->set('theme.user_can_change', $userCanChange, 'boolean', 'Allow user customization of themes');
    } elseif ($section === 'smtp') {
        $enabled = isset($input['enabled']) && ($input['enabled'] === 'on' || $input['enabled'] == 1);
        $settingsRepo->set('smtp.enabled', $enabled, 'boolean', 'Enable SMTP');
        if (isset($input['host'])) {
            $settingsRepo->set('smtp.smtp.host', htmlspecialchars(trim($input['host']), ENT_QUOTES, 'UTF-8'), 'string', 'SMTP Host');
        }
        if (isset($input['port'])) {
            $settingsRepo->set('smtp.smtp.port', (int) $input['port'], 'integer', 'SMTP Port');
        }
        if (isset($input['username'])) {
            $settingsRepo->set('smtp.smtp.username', htmlspecialchars(trim($input['username']), ENT_QUOTES, 'UTF-8'), 'string', 'SMTP Username');
        }
        // Only save password if it is provided (not empty)
        if (!empty($input['password'])) {
            $settingsRepo->set('smtp.smtp.password', $input['password'], 'string', 'SMTP Password', true);
        }
        if (isset($input['encryption'])) {
            $settingsRepo->set('smtp.smtp.encryption', htmlspecialchars(trim($input['encryption']), ENT_QUOTES, 'UTF-8'), 'string', 'SMTP Encryption');
        }
        if (isset($input['timeout'])) {
            $settingsRepo->set('smtp.smtp.timeout', (int) $input['timeout'], 'integer', 'SMTP Timeout');
        }
        if (isset($input['from_address'])) {
            $settingsRepo->set('smtp.from.address', filter_var($input['from_address'], FILTER_SANITIZE_EMAIL), 'string', 'SMTP From Address');
        }
        if (isset($input['from_name'])) {
            $settingsRepo->set('smtp.from.name', htmlspecialchars(trim($input['from_name']), ENT_QUOTES, 'UTF-8'), 'string', 'SMTP From Name');
        }
    } elseif ($section === 'notifications') {
        $enabled = isset($input['enabled']) && ($input['enabled'] === 'on' || $input['enabled'] == 1);
        $settingsRepo->set('notifications.enabled', $enabled, 'boolean', 'Enable notifications');
        
        $email = isset($input['email_enabled']) && ($input['email_enabled'] === 'on' || $input['email_enabled'] == 1);
        $settingsRepo->set('notifications.channels.email.enabled', $email, 'boolean', 'Enable email notifications');
        
        $sms = isset($input['sms_enabled']) && ($input['sms_enabled'] === 'on' || $input['sms_enabled'] == 1);
        $settingsRepo->set('notifications.channels.sms.enabled', $sms, 'boolean', 'Enable SMS notifications');
        
        $push = isset($input['push_enabled']) && ($input['push_enabled'] === 'on' || $input['push_enabled'] == 1);
        $settingsRepo->set('notifications.channels.push.enabled', $push, 'boolean', 'Enable push notifications');
        
        $inApp = isset($input['in_app_enabled']) && ($input['in_app_enabled'] === 'on' || $input['in_app_enabled'] == 1);
        $settingsRepo->set('notifications.channels.in_app.enabled', $inApp, 'boolean', 'Enable in-app notifications');
        
        $throttle = isset($input['throttle_enabled']) && ($input['throttle_enabled'] === 'on' || $input['throttle_enabled'] == 1);
        $settingsRepo->set('notifications.throttle.enabled', $throttle, 'boolean', 'Enable notification throttling');
        
        if (isset($input['throttle_minutes'])) {
            $settingsRepo->set('notifications.throttle.minutes', (int) $input['throttle_minutes'], 'integer', 'Notification throttle minutes');
        }
    } elseif ($section === 'monitoring') {
        $enabled = isset($input['enabled']) && ($input['enabled'] === 'on' || $input['enabled'] == 1);
        $settingsRepo->set('monitoring.health_check.enabled', $enabled, 'boolean', 'Enable health checks');
        
        if (isset($input['refresh_interval'])) {
            $settingsRepo->set('monitoring.refresh.interval', (int) $input['refresh_interval'], 'integer', 'Dashboard refresh interval');
        }
        if (isset($input['health_check_timeout'])) {
            $settingsRepo->set('monitoring.health_check.timeout', (int) $input['health_check_timeout'], 'integer', 'Health check connection timeout');
        }
        if (isset($input['alert_after_seconds'])) {
            $settingsRepo->set('monitoring.thresholds.alert_after_seconds', (int) $input['alert_after_seconds'], 'integer', 'Warning threshold duration');
        }
        if (isset($input['critical_after_seconds'])) {
            $settingsRepo->set('monitoring.thresholds.critical_after_seconds', (int) $input['critical_after_seconds'], 'integer', 'Critical threshold duration');
        }
    } elseif ($section === 'security') {
        if (isset($input['session_timeout'])) {
            $settingsRepo->set('security.auth.session_timeout', (int) $input['session_timeout'] * 60, 'integer', 'Auth session timeout');
        }
        if (isset($input['password_min_length'])) {
            $settingsRepo->set('security.password.min_length', (int) $input['password_min_length'], 'integer', 'Minimum password length');
        }
        
        $upper = isset($input['password_require_uppercase']) && ($input['password_require_uppercase'] === 'on' || $input['password_require_uppercase'] == 1);
        $settingsRepo->set('security.password.require_uppercase', $upper, 'boolean', 'Require uppercase in passwords');
        
        $num = isset($input['password_require_numbers']) && ($input['password_require_numbers'] === 'on' || $input['password_require_numbers'] == 1);
        $settingsRepo->set('security.password.require_numbers', $num, 'boolean', 'Require numbers in passwords');
        
        $spec = isset($input['password_require_special']) && ($input['password_require_special'] === 'on' || $input['password_require_special'] == 1);
        $settingsRepo->set('security.password.require_special', $spec, 'boolean', 'Require special characters in passwords');
        
        $auditLog = isset($input['audit_logging_enabled']) && ($input['audit_logging_enabled'] === 'on' || $input['audit_logging_enabled'] == 1);
        $settingsRepo->set('app.features.audit_logging_enabled', $auditLog, 'boolean', 'Enable security audit logging');
        
        $timeline = isset($input['activity_timeline_enabled']) && ($input['activity_timeline_enabled'] === 'on' || $input['activity_timeline_enabled'] == 1);
        $settingsRepo->set('app.features.activity_timeline_enabled', $timeline, 'boolean', 'Enable activity timeline logs');
    }

    // Log action
    $audit = app(\App\Infrastructure\Logging\AuditService::class);
    $audit->log('update', 'settings', null, $auth->user()->id, ['message' => "Updated $section settings", 'section' => $section, 'input' => $input], 'info');

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Settings updated successfully',
        'data' => ['section' => $section]
    ]);
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update settings',
        'errors' => [$e->getMessage()]
    ]);
}
