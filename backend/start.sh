#!/bin/bash

echo "🚀 Starting FLOTTEQ Backend..."

# Clear caches first
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# Run migrations
echo "📦 Running database migrations..."
php artisan migrate --force

# Create storage link
php artisan storage:link

# Cache configurations
echo "⚡ Caching configurations..."
php artisan config:cache
php artisan route:cache

# Start server
echo "🌐 Starting server on port $PORT..."
php artisan serve --host=0.0.0.0 --port=$PORT