# Nova Cloud Hosting - Monitor System

Enterprise-grade server monitoring and health check system built with clean architecture principles.

## Architecture Overview

This application follows clean architecture patterns with strict separation of concerns:

```
app/
├── Core/              # Core infrastructure (Container, Router, Helpers, Exceptions)
├── Domain/            # Business logic (Entities, Repositories, Events, Policies)
├── Application/       # Use cases (Commands, Queries, DTOs, Validators)
├── Infrastructure/    # External integrations (Database, Mail, Notifications, Logging)
└── Presentation/      # HTTP layer (Controllers, Middleware, Requests, Responses)

resources/
├── views/             # View templates
├── components/        # Reusable UI components
├── themes/            # Theme files (light/dark)
├── emails/            # Email templates
└── assets/            # Static assets

config/                # Centralized configuration (SSOT)
database/              # Migrations and seeders
bootstrap/             # Application bootstrap
```

## Key Features

### 1. **Clean Architecture**
- Strict separation of concerns (Core, Domain, Application, Infrastructure, Presentation)
- Domain-driven design principles
- Repository pattern for data access
- Service layer for business logic

### 2. **Single Source of Truth (SSOT)**
All configuration values exist in only one location:
- `config/app.php` - Application settings
- `config/database.php` - Database configuration
- `config/smtp.php` - Email/SMTP settings
- `config/notifications.php` - Notification system
- `config/monitoring.php` - Server monitoring
- `config/security.php` - Security & RBAC
- `config/theme.php` - UI theme settings
- `config/logging.php` - Logging configuration

No hardcoded values anywhere in the codebase.

### 3. **Dependency Injection**
Service Container for managing service instances:
```php
// Services are registered and resolved through the container
$mailer = app(\App\Infrastructure\Mail\MailService::class);
$notifications = app(\App\Infrastructure\Notifications\NotificationManager::class);
```

### 4. **Notification System**
Channel-agnostic notification architecture supporting:
- **Email** - SMTP integration (replaces SendGrid)
- **SMS** - Ready for Africa's Talking integration
- **Push** - Firebase support placeholder
- **In-App** - Database-backed notifications

Includes throttling to prevent notification flooding.

### 5. **RBAC (Role-Based Access Control)**
Three default roles with permission-based access:
- **Administrator** - Full access
- **Operator** - Server management
- **Viewer** - Read-only access

Permissions enforced through middleware.

### 6. **Centralized Logging**
Multiple log channels:
- Application logs
- Security logs
- Audit logs
- Monitoring logs
- Notification logs
- Authentication logs

### 7. **Reusable UI Components**
Component-based architecture eliminating code duplication:
- Buttons (Primary, Danger, Success)
- Alerts (Success, Error, Warning, Info)
- Badges (Status badges with configurable colors)
- Modals (Confirmation dialogs)
- Cards (Server info cards)
- Forms (Input fields)
- Timeline (Activity timeline)

Usage:
```php
<?= component('buttons.primary', ['text' => 'Click Me']) ?>
<?= component('badges.status', ['status' => 'online']) ?>
<?= component('alerts.success', ['message' => 'Success!']) ?>
```

### 8. **Theme System**
Centralized theme management with support for:
- Dark and light modes
- CSS variables for easy customization
- User preference persistence
- Theme switching capability

### 9. **Activity Timeline**
Centralized activity stream tracking:
- User logins/logouts
- Server changes
- Settings modifications
- User management actions
- Permission changes

### 10. **Authentication & Sessions**
Secure authentication system:
- Session-based authentication
- Password hashing with PHP's password_hash
- Permission checking
- Audit logging

### 11. **Server Health Monitoring**
- Periodic server connectivity checks
- Status tracking
- Automatic alert generation
- Metrics collection
- Notification dispatch on status changes

## Getting Started

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/monitor.git
   cd monitor
   ```

2. **Initialize the project**
   ```bash
   bash init.sh
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your configuration
   ```

4. **Install dependencies**
   ```bash
   composer install
   ```

5. **Run migrations**
   ```bash
   php database/migrate.php
   ```

6. **Access the application**
   ```
   http://localhost/monitor/public
   ```

### Default Credentials
```
Username: admin
Password: admin
```

## Configuration

All configuration is centralized in the `config/` directory. Environment variables are used for sensitive values:

```php
// config/smtp.php
return [
    'smtp' => [
        'host' => env('MAIL_HOST', 'mail.example.com'),
        'port' => env('MAIL_PORT', 465),
        'username' => env('MAIL_USERNAME', ''),
        'password' => env('MAIL_PASSWORD', ''),
        'encryption' => env('MAIL_ENCRYPTION', 'ssl'),
    ],
];
```

## API Response Format

All API responses follow a standard format:

**Success Response:**
```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {
    "id": 1,
    "name": "Example"
  }
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Operation failed",
  "errors": {
    "field": "Error message"
  }
}
```

## Database Schema

### Tables
- `users` - User accounts
- `roles` - User roles
- `permissions` - Permission definitions
- `role_permissions` - Role-permission mapping
- `servers` - Monitored servers
- `server_metrics` - Server performance metrics
- `notifications` - Notification records
- `activities` - Activity timeline
- `audit_logs` - Audit trail
- `settings` - Application settings

## Development Workflow

### Adding a New Component

1. Create the component file in `resources/components/{category}/{name}.php`
2. Use the `component()` helper to render it:
   ```php
   <?= component('buttons.custom', ['text' => 'My Button']) ?>
   ```

### Adding a New Service

1. Create the service class in `app/Infrastructure/{Service}/`
2. Register it in `bootstrap/app.php`:
   ```php
   $container->singleton(
       MyService::class,
       fn($c) => new MyService(/* dependencies */)
   );
   ```
3. Use it via the container:
   ```php
   $service = app(MyService::class);
   ```

### Adding a New Permission

1. Define it in `config/security.php`
2. Check it in your code:
   ```php
   $auth = app(AuthenticationService::class);
   if ($auth->hasPermission('server.delete')) {
       // Allow deletion
   }
   ```

## Testing

### Running Tests
```bash
php vendor/bin/phpunit
```

### Test Coverage
```bash
php vendor/bin/phpunit --coverage-html coverage/
```

## Performance Optimization

- Database queries are optimized with indexes
- Log retention is configurable
- Notification throttling prevents flooding
- CSS variables for efficient theme switching
- Component system reduces code duplication

## Security Features

- CSRF protection (session-based)
- SQL injection prevention (parameterized queries)
- XSS prevention (output escaping)
- RBAC enforcement
- Audit logging
- Secure password hashing
- Session security

## Future Enhancements

- [ ] SMS notifications via Africa's Talking
- [ ] Push notifications via Firebase
- [ ] Advanced metrics and reporting
- [ ] Webhook support for custom integrations
- [ ] GraphQL API
- [ ] Real-time notifications with WebSockets
- [ ] Multi-tenancy support
- [ ] Performance analytics

## Support & Contributing

For issues, feature requests, or contributions, please submit through the project repository.

## License

Proprietary - Nova Cloud Hosting
