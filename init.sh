#!/bin/bash

# Storage directory structure initialization
mkdir -p storage/logs
mkdir -p storage/cache
mkdir -p database

# Set permissions
chmod 775 storage
chmod 775 storage/logs
chmod 775 storage/cache
chmod 775 database

# Create .env file from example if it doesn't exist
if [ ! -f .env ]; then
    cp .env.example .env
    echo ".env file created. Please update it with your configuration."
fi

echo "Project initialized successfully!"
echo ""
echo "Next steps:"
echo "1. Copy .env.example to .env and configure your settings"
echo "2. Run migrations: php artisan migrate"
echo "3. Start the development server"
