#!/bin/bash

echo "🚀 Starting FLOTTEQ Backend..."

# Create necessary storage directories first
mkdir -p storage/framework/{views,cache,sessions}
mkdir -p storage/app/public
mkdir -p bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Clear caches first
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# Run migrations
echo "📦 Running database migrations..."
php artisan migrate --force

# Skip table creation as they're now in Supabase
echo "📊 Tables already exist in Supabase"

# Skip seeders in production - database should be populated with real data
echo "📊 Production mode - skipping seeders"

# Create storage link
php artisan storage:link

# Cache configurations
echo "⚡ Caching configurations..."
php artisan config:cache
php artisan route:cache

# Start server
echo "🌐 Starting server on port $PORT..."
php artisan serve --host=0.0.0.0 --port=$PORT