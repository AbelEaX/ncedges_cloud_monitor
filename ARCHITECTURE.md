# Architecture Documentation

## System Overview

This is an enterprise-grade server monitoring platform built on clean architecture principles with strict separation of concerns, dependency injection, and a focus on code reusability and maintainability.

## Layer Architecture

### 1. **Core Layer** (`app/Core/`)
The foundation of the application.

**Components:**
- **Container.php** - Service Container for dependency injection
  - Manages service bindings and singleton instances
  - Automatic class wiring with constructor injection
  - Service registration and resolution

- **Router.php** - HTTP request routing
  - Pattern matching for URLs
  - Route registration for GET/POST requests
  - Handler invocation

- **Helpers/functions.php** - Global helper functions
  - `env()` - Get environment variables
  - `config()` - Get configuration values  
  - `app()` - Get service container or resolve services
  - `view()` - Render view templates
  - `component()` - Render UI components
  - `log_info()`, `log_error()` - Logging helpers

- **Exceptions/** - Custom exception classes

### 2. **Domain Layer** (`app/Domain/`)
Core business logic and data structure definitions.

**Components:**
- **Entities/** - Value objects representing domain concepts
  - `User` - User entity with properties and methods
  - `Server` - Server entity with status and utility methods
  
- **Repositories/** - Interfaces defining data access contracts
  - `UserRepository` - User data access interface
  - `ServerRepository` - Server data access interface

- **Events/** - Domain events (for future event-driven architecture)
  - `ServerCreated`, `ServerDeleted`, `ServerDown`, `ServerRecovered`
  - `UserCreated`, `UserDeleted`
  
- **Policies/** - Business logic authorization rules
  - Resource access policies

### 3. **Application Layer** (`app/Application/`)
Use cases and application-specific logic.

**Components:**
- **UseCases/** - Application use cases
- **Commands/** - Command objects for operations
- **Queries/** - Query objects for data retrieval
- **DTOs/** - Data Transfer Objects
- **Validators/** - Input validation rules
- **EventHandlers/** - Event listener implementations

### 4. **Infrastructure Layer** (`app/Infrastructure/`)
External integrations and technical implementations.

**Components:**

#### Database
- **Connection.php** - PDO database abstraction
  - SQLite, MySQL, PostgreSQL support
  - Query execution, insert, update, delete operations
  - Connection pooling ready

#### Repositories
- **UserRepository.php** - User data access implementation
- **ServerRepository.php** - Server data access implementation

#### Logging
- **Logger.php** - Centralized logging service
  - Multiple log channels
  - Log levels (INFO, DEBUG, WARNING, ERROR, CRITICAL)
  - File-based logging

- **AuditService.php** - Audit trail logging
  - User actions tracking
  - Settings changes
  - Security event logging
  - Audit log export (CSV, PDF, Excel)

- **ActivityTimelineService.php** - Activity timeline
  - User activity tracking
  - Event logging
  - Timeline display

- **ThemeService.php** - Theme management
  - Light/dark mode support
  - CSS variables generation
  - User preference persistence

#### Authentication
- **AuthenticationService.php** - User authentication
  - Login/logout handling
  - Session management
  - Password verification
  - Permission checking

#### Mail
- **MailService.php** - SMTP email service
  - PHPMailer integration
  - Template support
  - Test email functionality
  - Replaces SendGrid completely

#### Notifications
- **NotificationManager.php** - Centralized notification system
  - Email channel
  - SMS channel (Africa's Talking ready)
  - Push channel (Firebase ready)
  - In-app channel
  - Throttling support
  - Event-driven architecture

#### Monitoring
- **MonitoringService.php** - Server health monitoring
  - Server connectivity checks
  - Status tracking
  - Alert generation
  - Metrics collection
  - Automatic notification on status changes

### 5. **Presentation Layer** (`app/Presentation/`)
HTTP request handling and response formatting.

**Components:**
- **Controllers/** - Request handlers
- **Middleware/** - Request/response middleware
  - `AuthenticationMiddleware` - Checks user authentication
  - `AuthorizationMiddleware` - Enforces RBAC

- **Requests/** - Form request validation
- **Responses/** - Response formatting
  - `ApiResponse` - Standardized JSON API responses
  
- **ViewModels/** - View data containers

## Configuration System (Single Source of Truth)

All configuration is centralized in `config/` directory:

### `config/app.php`
- Application name, environment, debug mode
- URLs and timezone
- Session configuration
- Cache settings
- Feature flags

### `config/database.php`
- Database connection settings
- Multiple database support
- Migration configuration

### `config/smtp.php`
- SMTP server details
- Sender information
- Alert recipient configuration
- SSL/TLS settings

### `config/notifications.php`
- Enabled notification channels
- Throttling configuration
- Event definitions
- Template mappings
- Retry logic

### `config/monitoring.php`
- Refresh intervals
- Health check settings
- Alert thresholds
- Status definitions
- Server grouping

### `config/security.php`
- Authentication settings
- Password policy
- RBAC roles and permissions
- CORS configuration
- Encryption settings

### `config/theme.php`
- Default theme
- Color palettes
- Typography settings
- Layout dimensions
- User customization options

### `config/logging.php`
- Log channels
- Log levels
- Retention policies

## Dependency Injection Flow

```
bootstrap/app.php
    ↓
Creates Container instance
    ↓
Registers all services:
    - Database Connection
    - Logger
    - Authentication Service
    - Mail Service
    - Notification Manager
    - Monitoring Service
    - Audit Service
    - Theme Service
    ↓
Stores in global $container
    ↓
Helper function app() retrieves services
    ↓
Services use constructor injection
    ↓
Services are singletons (reused across requests)
```

## Database Schema

### users
- id (Primary Key)
- username (Unique)
- email (Unique)
- password (Hashed)
- role (Foreign Key to roles)
- first_name, last_name
- is_active
- last_login_at
- timestamps

### servers
- id (Primary Key)
- name
- host
- port
- description
- status
- group_name
- is_active
- last_check_at
- last_status_change_at
- alert_sent
- timestamps

### roles
- id
- name (Unique)
- description

### permissions
- id
- name (Unique)
- description
- category

### server_metrics
- id
- server_id (Foreign Key)
- response_time
- status
- checked_at

### notifications
- id
- type
- recipient
- subject
- message
- channel
- status
- sent_at, read_at
- created_at

### activities
- id
- user_id
- action
- entity_type
- entity_id
- description
- details (JSON)
- ip_address, user_agent
- created_at

### audit_logs
- id
- user_id
- action
- entity_type
- entity_id
- details (JSON)
- severity
- ip_address, user_agent
- created_at

### settings
- id
- key (Unique)
- value
- type
- description

## Authentication & Authorization Flow

```
User submits login form
    ↓
/api/auth/login endpoint
    ↓
Validates credentials via AuthenticationService
    ↓
Creates session
    ↓
Logs activity via AuditService
    ↓
Redirects to dashboard
    ↓
Subsequent requests include AuthenticationMiddleware
    ↓
Checks if session exists
    ↓
For protected routes, AuthorizationMiddleware
    ↓
Checks user permissions
    ↓
Allows or denies access
```

## Notification System Flow

```
Event occurs (e.g., server goes down)
    ↓
MonitoringService detects status change
    ↓
Triggers notification
    ↓
NotificationManager routes to channels
    ↓
EmailChannel sends via MailService
    ↓
SMSChannel queues for provider
    ↓
PushChannel sends via Firebase
    ↓
InAppChannel stores in database
    ↓
Throttling prevents duplicate notifications
    ↓
Logs notification in audit trail
```

## Component System

Components are reusable UI elements in `resources/components/`:

```
components/
├── buttons/
│   ├── primary.php
│   ├── danger.php
│   └── success.php
├── badges/
│   └── status.php
├── alerts/
│   └── base.php
├── cards/
│   └── server.php
├── modals/
│   └── confirmation.php
├── forms/
│   └── input.php
└── timeline/
    └── activity.php
```

Usage:
```php
<?= component('buttons.primary', ['text' => 'Save', 'onClick' => '...']) ?>
<?= component('badges.status', ['status' => 'online']) ?>
```

## Request/Response Cycle

```
User Request
    ↓
Browser routes to public/index.php or specific endpoint
    ↓
bootstrap/app.php initializes
    ↓
Services are registered in Container
    ↓
Authentication middleware checks login
    ↓
Authorization middleware checks permissions
    ↓
Route handler executes
    ↓
Handler queries repositories
    ↓
Services process business logic
    ↓
View/API response generated
    ↓
Response sent to browser/client
```

## Environment Configuration

Settings are configured via environment variables in `.env`:

```env
APP_NAME=Nova Cloud Hosting
APP_ENV=production
MAIL_HOST=mail.example.com
MAIL_PORT=465
MAIL_USERNAME=admin@example.com
MAIL_PASSWORD=secret
...
```

These are loaded via `env()` helper and injected into config files.

## Caching Strategy

- Configuration is cached after first load
- Themes are cached with CSS variables
- Session data persists across requests
- Future: Redis support for distributed caching

## Security Implementation

1. **Authentication**
   - Session-based with secure cookies
   - Password hashing with PHP's password_hash()
   - Session timeout configurable

2. **Authorization**
   - RBAC with role and permission system
   - Middleware enforcement
   - Fine-grained permissions

3. **SQL Injection Prevention**
   - Parameterized queries using PDO
   - No string concatenation for queries

4. **XSS Prevention**
   - HTML escaping with htmlspecialchars()
   - Output encoding in templates

5. **Audit Trail**
   - All actions logged to audit_logs table
   - IP address and user agent tracked
   - Exportable audit reports

6. **CSRF Protection**
   - Session-based (future: token-based)

## Extensibility

### Adding a New Service
1. Create class in `app/Infrastructure/`
2. Implement interface if needed
3. Register in bootstrap/app.php
4. Inject via constructor

### Adding a New Permission
1. Define in config/security.php
2. Check via `$auth->hasPermission('permission.name')`

### Adding a New Notification Channel
1. Extend NotificationManager
2. Implement channel interface
3. Register in configuration

### Adding a New Component
1. Create in resources/components/{category}/
2. Use component() helper to render
3. No duplication needed

## Performance Considerations

1. **Database Indexing**
   - Foreign keys indexed
   - Status and date columns indexed for quick queries

2. **Caching**
   - Configuration cached
   - Theme CSS variables cached
   - Session data stored efficiently

3. **Lazy Loading**
   - Services created only when needed
   - Repository queries optimized with indexes

4. **Throttling**
   - Notification throttling prevents flooding
   - Configurable intervals

5. **Log Rotation**
   - Logs rotated based on retention policy
   - Old logs automatically deleted

## Testing Strategy

- Unit tests for business logic (Services, Repositories, Entities)
- Integration tests for database operations
- API tests for endpoints
- UI component tests for rendering

## Deployment

1. Set up environment variables in .env
2. Run migrations to create database schema
3. Set file permissions for storage/logs and storage/cache
4. Configure web server to point to public/ directory
5. Enable SSL/TLS for production

## Monitoring & Logging

- Application logs in storage/logs/application.log
- Security events in storage/logs/security.log
- Audit trail in storage/logs/audit.log
- All configurable via config/logging.php

## Future Enhancements

1. **Microservices Extraction**
   - Monitoring engine as standalone service
   - Notification engine as standalone service
   - Authentication service as standalone service

2. **Advanced Features**
   - Real-time notifications with WebSockets
   - Advanced analytics and dashboards
   - Machine learning for anomaly detection
   - Webhook support for integrations

3. **API Development**
   - RESTful API with versioning
   - GraphQL endpoint
   - OpenAPI/Swagger documentation

4. **Additional Channels**
   - SMS via Africa's Talking
   - Slack integrations
   - PagerDuty integrations
