#!/bin/bash

echo "======================================"
echo "Monitor Application - Structure Verification"
echo "======================================"
echo ""

# Check essential directories
echo "✓ Checking essential directories..."
dirs=("app" "bootstrap" "config" "database" "public" "resources" "storage")
for dir in "${dirs[@]}"; do
    if [ -d "$dir" ]; then
        echo "  ✓ $dir/"
    else
        echo "  ✗ $dir/ MISSING"
    fi
done
echo ""

# Check essential files
echo "✓ Checking essential files..."
files=(".env" "composer.json" "bootstrap/app.php" "public/index.php" "config/database.php")
for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        echo "  ✓ $file"
    else
        echo "  ✗ $file MISSING"
    fi
done
echo ""

# Check migrations
echo "✓ Checking migrations..."
migration_count=$(ls database/migrations/*.php 2>/dev/null | wc -l)
echo "  ✓ Found $migration_count migration files"
echo ""

# Check API endpoints
echo "✓ Checking API endpoints..."
endpoints=("public/api/servers/list.php" "public/api/settings/update.php" "public/api/reports/metrics.php")
for endpoint in "${endpoints[@]}"; do
    if [ -f "$endpoint" ]; then
        echo "  ✓ $endpoint"
    else
        echo "  ✗ $endpoint MISSING"
    fi
done
echo ""

# Check views
echo "✓ Checking view templates..."
views=("resources/views/auth/login.php" "resources/views/servers/index.php" "resources/views/reports/index.php")
for view in "${views[@]}"; do
    if [ -f "$view" ]; then
        echo "  ✓ $view"
    else
        echo "  ✗ $view MISSING"
    fi
done
echo ""

echo "======================================"
echo "Verification Complete"
echo "======================================"
echo ""
echo "To run the application:"
echo "1. Ensure PDO database driver is installed"
echo "2. Run: php database/migrate.php up"
echo "3. Run: php database/seed.php"
echo "4. Start server: php -S localhost:8000 -t public"
echo "5. Access: http://localhost:8000/"
echo ""
