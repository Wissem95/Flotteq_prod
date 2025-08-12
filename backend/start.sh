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

# Create missing tables if needed
echo "🔧 Creating missing tables..."
php artisan db:seed --class=CreateMissingTablesSeeder --force

# Check if database is empty and seed if needed
USER_COUNT=$(php artisan tinker --execute="echo App\Models\User::count();")
if [ "$USER_COUNT" = "0" ]; then
    echo "🌱 Database is empty, running production seeders..."
    php artisan db:seed --class=ProductionDataSeeder --force
else
    echo "📊 Database already contains $USER_COUNT users, skipping seeders"
fi

# Create storage link
php artisan storage:link

# Cache configurations
echo "⚡ Caching configurations..."
php artisan config:cache
php artisan route:cache

# Start server
echo "🌐 Starting server on port $PORT..."
php artisan serve --host=0.0.0.0 --port=$PORT