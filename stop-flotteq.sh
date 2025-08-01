#!/bin/bash

# FlotteQ - Stop All Services Script

echo "🛑 Stopping FlotteQ services..."

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

# Kill processes by PID file if exists
if [ -f .flotteq-pids ]; then
    print_status "Stopping services using saved PIDs..."
    PIDS=$(cat .flotteq-pids)
    for pid in $PIDS; do
        if kill -0 $pid 2>/dev/null; then
            kill $pid
            print_status "Stopped process $pid"
        fi
    done
    rm .flotteq-pids
fi

# Kill by process patterns
print_status "Stopping Laravel backend..."
pkill -f "php artisan serve" && print_status "Backend stopped" || print_warning "No backend process found"

print_status "Stopping frontend services..."
pkill -f "vite.*internal" && print_status "Internal frontend stopped" || print_warning "No internal frontend process found"
pkill -f "vite.*tenant" && print_status "Tenant frontend stopped" || print_warning "No tenant frontend process found"

# Alternative: kill all vite processes
pkill -f "vite" && print_status "All Vite processes stopped" || true

# Kill any npm dev processes
pkill -f "npm.*dev" && print_status "NPM dev processes stopped" || true

# Kill by port if still running
for port in 8000 8080 9092; do
    PID=$(lsof -ti:$port 2>/dev/null)
    if [ -n "$PID" ]; then
        kill $PID
        print_status "Killed process on port $port"
    fi
done

print_status "All FlotteQ services have been stopped! 🛑"