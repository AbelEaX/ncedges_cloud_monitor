# Database Setup Guide

## Prerequisites

This application requires a SQL database with PDO support. The following setups are supported:

### SQLite (Development - Recommended for Local Testing)
```bash
# Enable SQLite PDO extension in php.ini:
# extension=pdo_sqlite
# or
# extension=sqlite3
```

### MySQL (Production)
```bash
# Enable MySQL PDO extension in php.ini:
# extension=pdo_mysql
```

### PostgreSQL
```bash
# Enable PostgreSQL PDO extension in php.ini:
# extension=pdo_pgsql
```

## Setup Instructions

### 1. Install Required PHP Extensions

**Windows (XAMPP/WAMP):**
- Edit `php.ini` 
- Uncomment `extension=pdo_sqlite` for SQLite
- Uncomment `extension=pdo_mysql` for MySQL
- Restart Apache/PHP

**Linux (Ubuntu/Debian):**
```bash
# For SQLite
sudo apt-get install php-sqlite3

# For MySQL
sudo apt-get install php-mysql

# For PostgreSQL
sudo apt-get install php-pgsql
```

**macOS (Homebrew):**
```bash
# PHP comes with SQLite support by default
# For MySQL:
brew install php@8.1-mysql
```

### 2. Configure Environment

Create a `.env` file from `.env.example`:
```bash
cp .env.example .env
```

Then edit `.env` to match your database setup:

**For SQLite:**
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/monitor.db
```

**For MySQL:**
```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=monitor
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. Run Migrations

```bash
# Run all pending migrations
php database/migrate.php up

# Check migration status
php database/migrate.php status

# Rollback last batch
php database/migrate.php down

# Refresh (rollback all and run all)
php database/migrate.php refresh
```

### 4. Seed Default Data

```bash
# Seed database with default data
php database/seed.php

# Or truncate first (fresh seed)
php database/seed.php --fresh
```

## Default Test Credentials

After seeding, you can login with:

| Username | Password | Role |
|----------|----------|------|
| admin | admin123 | Administrator |
| manager | manager123 | Manager |
| viewer | viewer123 | Viewer |

## Troubleshooting

### "could not find driver" Error
This means the PDO driver is not installed or enabled:
1. Check `php -m` for pdo_sqlite, pdo_mysql, or pdo_pgsql
2. Enable the extension in php.ini
3. Restart PHP/Apache

### "SQLSTATE[HY000]: General error"
Usually means database connection error:
1. Check DB_HOST, DB_USERNAME, DB_PASSWORD in .env
2. Ensure database server is running
3. Check database exists: `CREATE DATABASE monitor;` (for MySQL)

### "Migration file not found"
Ensure all migration files are in `database/migrations/` directory.

## Development with File-Based Storage (No Database)

If you cannot install database drivers, the application can still run in development mode using file-based storage:

```bash
# Set in .env
APP_ENV=development_nostore
```

This will use JSON files in `storage/database/` instead of a real database. This is **NOT recommended for production** but allows local development.

## Docker Setup (Alternative)

If you prefer Docker:

```bash
# Create a docker-compose.yml
docker-compose up -d

# Run migrations
docker-compose exec app php database/migrate.php up

# Seed data
docker-compose exec app php database/seed.php
```

See `docker-compose.yml` in project root for details.

---

**Status**: The migration and seeding scripts are ready. Complete the setup above and run the commands to initialize your database.
