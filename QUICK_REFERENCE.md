# Quick Reference Guide

## Configuration

### Get a Config Value
```php
$value = config('section.key');

// Examples
$appName = config('app.name');
$dbDriver = config('database.driver');
$smtpHost = config('smtp.host');
```

### Get Environment Variable
```php
$value = env('KEY', 'default');

// Examples
$dbPath = env('DATABASE_PATH', 'database.sqlite');
$apiKey = env('API_KEY');
```

## Services & Dependency Injection

### Get a Service from Container
```php
$service = app(\App\Infrastructure\Namespace\ServiceName::class);
```

### Common Services
```php
// Database
$db = app(\App\Infrastructure\Database\Connection::class);

// Logger
$logger = app(\App\Infrastructure\Logging\Logger::class);

// Authentication
$auth = app(\App\Infrastructure\Authentication\AuthenticationService::class);

// Mail
$mail = app(\App\Infrastructure\Mail\MailService::class);

// Notifications
$notify = app(\App\Infrastructure\Notifications\NotificationManager::class);

// Monitoring
$monitor = app(\App\Infrastructure\Monitoring\MonitoringService::class);

// Audit
$audit = app(\App\Infrastructure\Logging\AuditService::class);

// Theme
$theme = app(\App\Infrastructure\Logging\ThemeService::class);
```

## Logging

### Log Messages
```php
// Log to appropriate channel
log_info('User logged in', ['username' => 'john']);
log_error('Database connection failed', ['error' => $message]);

// Using logger service directly
$logger = app(\App\Infrastructure\Logging\Logger::class);
$logger->info('Message', ['context'], 'channel_name');
$logger->error('Error message', ['context'], 'channel_name');
```

### Log Channels
- `application` - General app logs
- `security` - Security-related events
- `audit` - Audit trail
- `monitoring` - Server monitoring
- `notifications` - Notification events
- `authentication` - Login/logout events

## Database

### Query Data
```php
$db = app(\App\Infrastructure\Database\Connection::class);

// Fetch one row
$user = $db->fetchOne(
    'SELECT * FROM users WHERE id = ?',
    [1]
);

// Fetch all rows
$users = $db->fetchAll(
    'SELECT * FROM users WHERE is_active = ?',
    [true]
);
```

### Insert Data
```php
$id = $db->insert('users', [
    'username' => 'john',
    'email' => 'john@example.com',
    'password' => password_hash('secret', PASSWORD_BCRYPT),
    'role' => 'user',
]);
```

### Update Data
```php
$affected = $db->update(
    'users',
    ['email' => 'newemail@example.com'],
    'id = ?',
    [1]
);
```

### Delete Data
```php
$deleted = $db->delete(
    'users',
    'id = ?',
    [1]
);
```

## Authentication & Authorization

### Check if User is Logged In
```php
$auth = app(\App\Infrastructure\Authentication\AuthenticationService::class);
if ($auth->isAuthenticated()) {
    // User is logged in
}
```

### Get Current User
```php
$user = $auth->getUser();
echo $user['username'];
```

### Check Permission
```php
if ($auth->hasPermission('server.delete')) {
    // User can delete servers
}
```

### User Roles
```php
if ($auth->getUser()['role'] === 'admin') {
    // Admin only
}
```

## Sending Notifications

### Send Email
```php
$notify = app(\App\Infrastructure\Notifications\NotificationManager::class);
$notify->sendEmail(
    'user@example.com',
    'Subject Line',
    'Email body content'
);
```

### Send SMS (Future)
```php
$notify->sendSMS(
    '+1234567890',
    'Message content'
);
```

### Check if Can Send (Throttling)
```php
if ($notify->canSendNotification('user@example.com')) {
    $notify->sendEmail('user@example.com', 'Subject', 'Body');
}
```

## Sending Email

### Basic Email
```php
$mail = app(\App\Infrastructure\Mail\MailService::class);
$mail->send(
    'recipient@example.com',
    'Subject',
    'Email body'
);
```

### Email with Options
```php
$mail->send(
    to: 'recipient@example.com',
    subject: 'Subject',
    body: 'Body text',
    fromAddress: 'sender@example.com',
    fromName: 'Sender Name',
    cc: ['cc@example.com'],
    bcc: ['bcc@example.com'],
);
```

### Test SMTP
```php
$mail->sendTest('test@example.com');
```

## UI Components

### Render a Component
```php
<?= component('category.component_name', ['param' => 'value']) ?>
```

### Available Components
```php
// Buttons
<?= component('buttons.primary', ['text' => 'Save']) ?>
<?= component('buttons.danger', ['text' => 'Delete']) ?>
<?= component('buttons.success', ['text' => 'Confirm']) ?>

// Badges
<?= component('badges.status', ['status' => 'online']) ?>

// Alerts
<?= component('alerts.base', ['type' => 'success', 'message' => 'Success!']) ?>

// Cards
<?= component('cards.server', ['server' => $serverData]) ?>

// Modals
<?= component('modals.confirmation', ['id' => 'confirm', 'title' => 'Are you sure?']) ?>

// Forms
<?= component('forms.input', ['name' => 'email', 'label' => 'Email', 'type' => 'email']) ?>

// Timeline
<?= component('timeline.activity', ['activities' => $activities]) ?>
```

## Views & Rendering

### Render a View
```php
echo view('path.to.view', ['variable' => 'value']);

// Example
echo view('dashboard.index', ['servers' => $servers]);
```

### View File Location
View files are in `resources/views/`. Dots in the path translate to slashes:
- `view('dashboard.index')` → `resources/views/dashboard/index.php`
- `view('servers.form')` → `resources/views/servers/form.php`

## Audit Logging

### Log an Action
```php
$audit = app(\App\Infrastructure\Logging\AuditService::class);
$audit->log(
    action: 'create',
    entity_type: 'server',
    entity_id: 123,
    user_id: 1,
    details: ['name' => 'Server 1', 'host' => '192.168.1.1']
);
```

### Log Login
```php
$audit->logLogin($userId, $username);
```

### Log Logout
```php
$audit->logLogout($userId, $username);
```

### Get Audit Logs
```php
$logs = $audit->getAuditLogs(limit: 50, offset: 0);
```

### Export Audit Logs
```php
$csv = $audit->exportAuditLogs('csv');
// CSV format: action,entity_type,entity_id,user_id,details,timestamp
```

## Activity Timeline

### Log Activity
```php
$timeline = app(\App\Infrastructure\Logging\ActivityTimelineService::class);
$timeline->log(
    action: 'updated',
    entity_type: 'server',
    entity_id: 1,
    user_id: 1,
    description: 'Server configuration updated',
    details: ['old_status' => 'online', 'new_status' => 'offline']
);
```

### Get Recent Activities
```php
$activities = $timeline->getRecent(limit: 20);
```

## Theme Management

### Get Current Theme
```php
$theme = app(\App\Infrastructure\Logging\ThemeService::class);
$current = $theme->getCurrentTheme();
```

### Set Theme
```php
$theme->setTheme('dark');
// or
$theme->setTheme('light');
```

### Get Theme Colors
```php
$colors = $theme->getColors();
```

### Generate CSS Variables
```php
$css = $theme->generateCSSVariables();
// Returns: :root { --primary: #ffc107; ... }
```

### Get Style Tag
```php
<?= $theme->getStyleTag(); ?>
```

## Monitoring

### Check Server Health
```php
$monitor = app(\App\Infrastructure\Monitoring\MonitoringService::class);
$isOnline = $monitor->checkServerHealth('example.com', 443);
```

### Monitor All Servers
```php
$monitor->monitorAllServers();
```

### Send Alerts
```php
$monitor->sendServerDownNotification($server);
$monitor->sendServerRecoveredNotification($server);
```

## API Responses

### Success Response
```php
\App\Presentation\Responses\ApiResponse::success(
    data: ['id' => 1, 'name' => 'Item'],
    message: 'Operation successful',
    code: 200
);
```

### Error Response
```php
\App\Presentation\Responses\ApiResponse::error(
    message: 'Operation failed',
    errors: ['field' => 'Error message'],
    code: 400
);
```

### Validation Error
```php
\App\Presentation\Responses\ApiResponse::validationError([
    'email' => 'Invalid email format',
    'password' => 'Password must be at least 8 characters'
]);
```

### Unauthorized
```php
\App\Presentation\Responses\ApiResponse::unauthorized();
```

### Forbidden
```php
\App\Presentation\Responses\ApiResponse::forbidden();
```

### Not Found
```php
\App\Presentation\Responses\ApiResponse::notFound();
```

## Middleware

### Authentication Middleware
```php
$auth = new \App\Presentation\Middleware\AuthenticationMiddleware();
$auth->handle(); // Checks if user is logged in, redirects if not
```

### Authorization Middleware
```php
$authz = new \App\Presentation\Middleware\AuthorizationMiddleware('server.delete');
$authz->handle(); // Checks if user has permission
```

## Common Patterns

### Check Auth Then Perform Action
```php
require __DIR__ . '/../../bootstrap/app.php';

// Check authentication
$auth = new \App\Presentation\Middleware\AuthenticationMiddleware();
$auth->handle();

// Check authorization
$authz = new \App\Presentation\Middleware\AuthorizationMiddleware('permission.name');
$authz->handle();

// Get services
$db = app(\App\Infrastructure\Database\Connection::class);
$logger = app(\App\Infrastructure\Logging\Logger::class);

// Do the work
try {
    // ... perform operation
    \App\Presentation\Responses\ApiResponse::success(['id' => $id], 'Success');
} catch (\Exception $e) {
    $logger->error($e->getMessage());
    \App\Presentation\Responses\ApiResponse::error($e->getMessage(), [], 500);
}
```

### Render a Page
```php
require __DIR__ . '/../bootstrap/app.php';

// Check auth
$auth = new \App\Presentation\Middleware\AuthenticationMiddleware();
$auth->handle();

// Get data
$data = [];

// Render view
echo view('page.name', $data);
```

## Debugging

### Dump and Die
```php
dd($variable);
```

### Log for Debugging
```php
log_info('Debug info', ['variable' => $value]);
```

### Check Logs
```bash
tail -f storage/logs/application.log
tail -f storage/logs/audit.log
tail -f storage/logs/security.log
```

## Environment Setup

### Load Environment Variables
Environment variables are loaded from `.env` file in the root:

```env
APP_NAME=Monitor
APP_ENV=production
MAIL_HOST=smtp.example.com
MAIL_PORT=465
MAIL_USERNAME=user@example.com
MAIL_PASSWORD=password
```

Access with:
```php
$host = env('MAIL_HOST');
$port = env('MAIL_PORT', 465); // Default value
```
