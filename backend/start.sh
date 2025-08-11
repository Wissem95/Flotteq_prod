#!/bin/bash

# Run migrations
php artisan migrate --force

# Cache config
php artisan config:cache
php artisan route:cache

# Start server
php artisan serve --host=0.0.0.0 --port=$PORT