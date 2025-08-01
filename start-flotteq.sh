#!/bin/bash

# FlotteQ - Complete Deployment Script
# This script starts all services for the FlotteQ fleet management application

set -e

echo "🚀 Starting FlotteQ Fleet Management System"
echo "============================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_header() {
    echo -e "${BLUE}$1${NC}"
}

# Check if we're in the right directory
if [ ! -f "backend/artisan" ]; then
    print_error "Please run this script from the FLOTTEQ root directory"
    exit 1
fi

# Function to check if port is in use
check_port() {
    local port=$1
    if lsof -Pi :$port -sTCP:LISTEN -t >/dev/null ; then
        return 0
    else
        return 1
    fi
}

# Function to kill process on port
kill_port() {
    local port=$1
    local pids=$(lsof -Pi :$port -sTCP:LISTEN -t)
    if [ -n "$pids" ]; then
        print_warning "Killing existing process on port $port"
        echo $pids | xargs kill -9
        sleep 2
    fi
}

print_header "Step 1: Environment Setup"
print_status "Checking environment configuration..."

# Check if .env files exist
if [ ! -f "backend/.env" ]; then
    print_error "Backend .env file not found!"
    exit 1
fi

if [ ! -f "frontend/internal/.env" ]; then
    print_error "Internal frontend .env file not found!"
    exit 1
fi

if [ ! -f "frontend/tenant/.env" ]; then
    print_error "Tenant frontend .env file not found!"
    exit 1
fi

print_status "Environment files found ✓"

print_header "Step 2: Backend Setup (Laravel)"
print_status "Setting up Laravel backend..."

cd backend

# Check if vendor directory exists
if [ ! -d "vendor" ]; then
    print_status "Installing PHP dependencies..."
    composer install --no-dev --optimize-autoloader
fi

# Database setup
print_status "Setting up database..."
if [ ! -f "database/database.sqlite" ]; then
    touch database/database.sqlite
fi

# Run migrations
print_status "Running database migrations..."
php artisan migrate:fresh --force

# Seed database
print_status "Seeding database with production data..."
php artisan db:seed --class=InternalAdminSeeder
php artisan db:seed --class=ProductionDataSeeder

# Clear caches
print_status "Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Generate app key if needed
php artisan key:generate --force

print_status "Backend setup complete ✓"

# Start backend server
print_status "Starting Laravel backend on port 8000..."
kill_port 8000

# Start backend in background
php artisan serve --host=0.0.0.0 --port=8000 > ../logs/backend.log 2>&1 &
BACKEND_PID=$!

# Wait a moment for server to start
sleep 3

# Test backend health
if curl -s http://localhost:8000/api/health > /dev/null; then
    print_status "Backend server started successfully ✓"
else
    print_error "Backend server failed to start"
    kill $BACKEND_PID 2>/dev/null || true
    exit 1
fi

cd ..

print_header "Step 3: Frontend Setup"

# Create logs directory
mkdir -p logs

# Setup Internal Frontend (SuperAdmin)
print_status "Setting up Internal Frontend (SuperAdmin interface)..."
cd frontend/internal

if [ ! -d "node_modules" ]; then
    print_status "Installing Internal frontend dependencies..."
    npm install
fi

print_status "Building Internal frontend..."
npm run build

print_status "Starting Internal frontend on port 8080..."
kill_port 8080

# Start internal frontend
npm run dev > ../../logs/internal-frontend.log 2>&1 &
INTERNAL_PID=$!

cd ../..

# Setup Tenant Frontend (Client interface)
print_status "Setting up Tenant Frontend (Client interface)..."
cd frontend/tenant

if [ ! -d "node_modules" ]; then
    print_status "Installing Tenant frontend dependencies..."
    npm install
fi

print_status "Building Tenant frontend..."
npm run build

print_status "Starting Tenant frontend on port 9092..."
kill_port 9092

# Start tenant frontend
npm run dev > ../../logs/tenant-frontend.log 2>&1 &
TENANT_PID=$!

cd ../..

# Wait for frontends to start
print_status "Waiting for frontends to initialize..."
sleep 10

print_header "Step 4: Health Checks"

# Test all endpoints
print_status "Testing API endpoints..."

# Test backend health
if curl -s http://localhost:8000/api/health | grep -q "ok"; then
    print_status "✓ Backend API health check passed"
else
    print_error "✗ Backend API health check failed"
fi

# Test internal auth
AUTH_RESULT=$(curl -s -X POST http://localhost:8000/api/internal/auth/login \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"email": "admin@flotteq.com", "password": "demo123"}')

if echo "$AUTH_RESULT" | grep -q "token"; then
    print_status "✓ Internal authentication working"
else
    print_error "✗ Internal authentication failed"
fi

# Test tenant auth
TENANT_AUTH_RESULT=$(curl -s -X POST http://localhost:8000/api/auth/login \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"login": "wissemkarboubbb@gmail.com", "password": "demo123"}')

if echo "$TENANT_AUTH_RESULT" | grep -q "token"; then
    print_status "✓ Tenant authentication working"
else
    print_error "✗ Tenant authentication failed"
fi

# Test frontend accessibility
if curl -s http://localhost:8080 > /dev/null; then
    print_status "✓ Internal frontend accessible"
else
    print_warning "⚠ Internal frontend may still be starting..."
fi

if curl -s http://localhost:9092 > /dev/null; then
    print_status "✓ Tenant frontend accessible"
else
    print_warning "⚠ Tenant frontend may still be starting..."
fi

print_header "🎉 FlotteQ Deployment Complete!"
echo
print_status "All services are now running:"
echo
echo "Backend (Laravel API):     http://localhost:8000"
echo "Internal Frontend (Admin): http://localhost:8080"  
echo "Tenant Frontend (Client):  http://localhost:9092"
echo
print_header "📋 Login Credentials"
echo
print_status "INTERNAL ADMIN LOGIN (http://localhost:8080):"
echo "Email:    admin@flotteq.com"
echo "Password: demo123"
echo
print_status "TENANT CLIENT LOGIN (http://localhost:9092):"
echo "Email:    wissemkarboubbb@gmail.com"
echo "Password: demo123"
echo "Company:  FlotteQ Demo"
echo
print_header "📊 Process Information"
echo "Backend PID:  $BACKEND_PID"
echo "Internal PID: $INTERNAL_PID"  
echo "Tenant PID:   $TENANT_PID"
echo
print_status "Logs are available in the ./logs/ directory"
echo "- backend.log (Laravel API logs)"
echo "- internal-frontend.log (Internal admin interface logs)"
echo "- tenant-frontend.log (Client interface logs)"
echo
print_header "🛑 To Stop All Services:"
echo "kill $BACKEND_PID $INTERNAL_PID $TENANT_PID"
echo "or run: pkill -f 'php artisan serve|vite'"
echo
print_status "FlotteQ is ready for use! 🚀"

# Save PIDs for easy cleanup
echo "$BACKEND_PID $INTERNAL_PID $TENANT_PID" > .flotteq-pids

# Keep script running and monitor
print_status "Monitoring services... (Press Ctrl+C to stop all services)"

trap 'echo -e "\n${YELLOW}Stopping all FlotteQ services...${NC}"; kill $BACKEND_PID $INTERNAL_PID $TENANT_PID 2>/dev/null || true; rm -f .flotteq-pids; exit 0' INT

# Monitor processes
while true; do
    if ! kill -0 $BACKEND_PID 2>/dev/null; then
        print_error "Backend process died, restarting..."
        cd backend
        php artisan serve --host=0.0.0.0 --port=8000 > ../logs/backend.log 2>&1 &
        BACKEND_PID=$!
        cd ..
    fi
    
    if ! kill -0 $INTERNAL_PID 2>/dev/null; then
        print_error "Internal frontend process died, restarting..."
        cd frontend/internal
        npm run dev > ../../logs/internal-frontend.log 2>&1 &
        INTERNAL_PID=$!
        cd ../..
    fi
    
    if ! kill -0 $TENANT_PID 2>/dev/null; then
        print_error "Tenant frontend process died, restarting..."
        cd frontend/tenant
        npm run dev > ../../logs/tenant-frontend.log 2>&1 &
        TENANT_PID=$!
        cd ../..
    fi
    
    sleep 30
done