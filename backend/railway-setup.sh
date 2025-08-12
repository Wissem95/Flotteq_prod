#!/bin/bash

echo "🚀 Setting up Laravel for Railway..."

# Create necessary storage directories
mkdir -p storage/framework/{views,cache,sessions}
mkdir -p storage/app/public
mkdir -p bootstrap/cache

# Set correct permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

echo "✅ Storage directories created"

# Clear and optimize caches
php artisan config:clear
php artisan view:clear
php artisan cache:clear
php artisan route:clear

echo "✅ Caches cleared"

# Link storage
php artisan storage:link

echo "✅ Storage linked"

# Run migrations
php artisan migrate --force

echo "✅ Migrations completed"

# Cache configurations for performance
php artisan config:cache
php artisan route:cache

echo "✅ Laravel setup complete!"