# Quick Start Guide

**Last Updated:** June 18, 2026

## 5-Minute Setup

### 1️⃣ Database Setup (1 min)
```bash
# Run migrations
php database/migrate.php up

# Seed default data  
php database/seed.php
```

### 2️⃣ Start Server (1 min)
```bash
# From project root
php -S localhost:8000 -t public
```

### 3️⃣ Login (1 min)
Open http://localhost:8000/ in browser:
```
Username: admin
Password: admin123
```

### 4️⃣ Explore (2 min)
- Dashboard: Main overview
- Servers: Manage server list
- Settings: Configure application
- Reports: View analytics

---

## Troubleshooting

### Database Error: "could not find driver"
**Fix:** Install PDO driver for your database
```bash
# Windows (bundled with modern PHP)
# Just run the commands above

# Linux
sudo apt-get install php-sqlite3  # or php-mysql, php-pgsql
```

### Port 8000 Already in Use
```bash
# Use different port
php -S localhost:8080 -t public
# Access: http://localhost:8080/
```

### Permission Denied on storage/logs/
```bash
# Set permissions
chmod 755 storage/logs
chmod 755 storage/cache
```

---

## Project Structure

```
📁 app/              Application code (clean architecture)
📁 bootstrap/        Service initialization  
📁 config/           Configuration files
📁 database/         Migrations & seeds
📁 public/           Entry point & APIs
📁 resources/        Views & components
📁 storage/          Logs & cache
📄 .env              Configuration
```

---

## What to Do Next

### Try These Features:
1. ✅ Add a new server (Servers menu)
2. ✅ Change theme (Settings menu)
3. ✅ View reports (Reports menu)
4. ✅ Check audit logs
5. ✅ Test logout

### For Development:
- Modify views in `resources/views/`
- Add API endpoints in `public/api/`
- Create services in `app/Application/`
- Run migrations for schema changes

### For Deployment:
- See `PROJECT_EVALUATION.md` for production checklist
- Update `.env` with production values
- Use MySQL/PostgreSQL instead of SQLite
- Configure SMTP for emails
- Enable HTTPS

---

## Key Files

| File | Purpose |
|------|---------|
| `.env` | Configuration |
| `bootstrap/app.php` | Service setup |
| `public/index.php` | Entry point |
| `database/migrate.php` | Run migrations |
| `app/Core/Helpers/functions.php` | Global helpers |

---

## Common Commands

```bash
# Check migrations
php database/migrate.php status

# Rollback migrations
php database/migrate.php down

# Refresh database (danger!)
php database/migrate.php refresh

# View app config
cat .env
```

---

## API Endpoints

```
POST   /api/auth/login              Login
POST   /api/auth/logout             Logout
GET    /api/servers/list            List servers
POST   /api/servers/create          Create server
GET    /api/settings                Get settings
POST   /api/settings/update         Update settings
GET    /api/reports/metrics         Get metrics
```

---

## Support

- 📚 Read: `README.md` - Project overview
- 🏗️ Read: `ARCHITECTURE.md` - System design
- ✅ Read: `PROJECT_EVALUATION.md` - Detailed analysis
- 🧹 Read: `CLEANUP_SUMMARY.md` - What was removed
- ⚡ Run: `./verify.sh` or `verify.bat` - Check setup

---

**Everything is ready! Run the server and enjoy! 🚀**
