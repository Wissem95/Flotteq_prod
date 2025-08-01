# 🚀 FlotteQ - Production Deployment Guide

**FlotteQ** is a comprehensive fleet management system with multi-tenant architecture.

## 🏗️ **CORRECT ARCHITECTURE**

**Backend:** Laravel 12 PHP Framework (Port 8000)
- **Database:** SQLite (development), PostgreSQL (production recommended)
- **Authentication:** Laravel Sanctum + JWT
- **ORM:** Eloquent (not Prisma)

**Frontend Internal:** React + TypeScript (Port 8080)
- Super-admin interface for FlotteQ employees
- Manages tenants, partners, analytics, support

**Frontend Tenant:** React + TypeScript (Port 9092)  
- Client interface for fleet owners
- Manages vehicles, maintenance, finances

## 🚀 **QUICK START**

### 1. Automated Deployment
```bash
cd /path/to/FLOTTEQ
./start-flotteq.sh
```

This script will:
- ✅ Setup all dependencies
- ✅ Run database migrations  
- ✅ Seed production data
- ✅ Start all services
- ✅ Perform health checks
- ✅ Monitor processes

### 2. Manual Setup (if needed)

#### Backend Setup
```bash
cd backend
composer install
php artisan migrate:fresh
php artisan db:seed --class=InternalAdminSeeder
php artisan db:seed --class=ProductionDataSeeder
php artisan serve --host=0.0.0.0 --port=8000
```

#### Internal Frontend Setup
```bash
cd frontend/internal
npm install
npm run dev  # Runs on port 8080
```

#### Tenant Frontend Setup
```bash
cd frontend/tenant
npm install  
npm run dev  # Runs on port 9092
```

## 🔐 **LOGIN CREDENTIALS**

### Internal Admin Interface (http://localhost:8080)
```
Email: admin@flotteq.com
Password: demo123
Role: Super Administrator
```

### Tenant Client Interface (http://localhost:9092)
```
Email: wissemkarboubbb@gmail.com
Password: demo123
Company: FlotteQ Demo
Role: Fleet Administrator
```

## 🌐 **SERVICE URLs**

| Service | URL | Purpose |
|---------|-----|---------|
| Backend API | http://localhost:8000 | Laravel REST API |
| Internal Admin | http://localhost:8080 | Super-admin interface |
| Tenant Client | http://localhost:9092 | Fleet management interface |

## 📋 **API Endpoints**

### Authentication
```bash
# Internal Admin Login
POST /api/internal/auth/login
{
  "email": "admin@flotteq.com",
  "password": "demo123"
}

# Tenant Login  
POST /api/auth/login
{
  "login": "wissemkarboubbb@gmail.com",
  "password": "demo123"
}

# Health Check
GET /api/health
```

### Protected Endpoints
- `/api/internal/*` - Internal admin routes (requires internal auth)
- `/api/vehicles` - Vehicle management (requires tenant auth)
- `/api/maintenances` - Maintenance records (requires tenant auth)
- `/api/analytics/*` - Analytics data (both internal/tenant)

## 🗄️ **DATABASE STRUCTURE**

### Key Tables
- `users` - Both internal and tenant users
- `tenants` - Multi-tenant companies
- `vehicles` - Fleet vehicles  
- `maintenances` - Maintenance records
- `partners` - Service providers (garages, insurance)
- `subscriptions` - Subscription plans

### Sample Data Includes
- ✅ 5 Internal FlotteQ employees
- ✅ 5 Tenant companies with realistic data
- ✅ 72+ vehicles across different companies
- ✅ Partners: garages, technical control centers, insurance
- ✅ Support tickets and analytics events

## 🔧 **CONFIGURATION FILES**

### Backend (.env)
```env
APP_URL=http://localhost:8000
DB_CONNECTION=sqlite
FRONTEND_INTERNAL_URL=http://localhost:8080
FRONTEND_TENANT_URL=http://localhost:9092
```

### Internal Frontend (.env)
```env
VITE_API_URL=http://localhost:8000/api
VITE_INTERNAL_API_URL=http://localhost:8000/api/internal
VITE_BACKEND_URL=http://localhost:8000
```

### Tenant Frontend (.env)
```env
VITE_API_URL=http://localhost:8000/api
VITE_AUTH_API_URL=http://localhost:8000/api/auth
VITE_BACKEND_URL=http://localhost:8000
```

## 🧪 **TESTING THE SYSTEM**

### Backend API Tests
```bash
# Health check
curl http://localhost:8000/api/health

# Internal login
curl -X POST http://localhost:8000/api/internal/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@flotteq.com","password":"demo123"}'

# Tenant login  
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"login":"wissemkarboubbb@gmail.com","password":"demo123"}'
```

### Frontend Access
1. **Internal Admin**: Navigate to http://localhost:8080
2. **Tenant Client**: Navigate to http://localhost:9092

## 🔧 **CRITICAL FIX APPLIED**

### Double Prefix Route Issue ✅ **RESOLVED**
The frontend was incorrectly making requests to `/api/internal/internal/auth/login` instead of `/api/internal/auth/login`. This has been fixed by updating the internal auth service to use correct route paths relative to the API base URL.

**Files Modified:**
- `frontend/internal/src/services/internalAuthService.ts`
- `frontend/internal/src/services/partnersService.ts`

## 🚨 **TROUBLESHOOTING**

### Common Issues

#### Port Already in Use
```bash
# Kill processes on specific ports
lsof -ti:8000 | xargs kill -9  # Backend
lsof -ti:8080 | xargs kill -9  # Internal frontend
lsof -ti:9092 | xargs kill -9  # Tenant frontend
```

#### Database Issues
```bash
cd backend
rm database/database.sqlite
touch database/database.sqlite
php artisan migrate:fresh
php artisan db:seed --class=InternalAdminSeeder
php artisan db:seed --class=ProductionDataSeeder
```

#### Frontend Build Issues
```bash
cd frontend/internal
rm -rf node_modules package-lock.json
npm install

cd ../tenant  
rm -rf node_modules package-lock.json
npm install
```

#### Authentication Not Working
- Ensure CORS is properly configured
- Check that Sanctum cookies are being set
- Verify `.env` files have correct URLs
- Clear browser cookies and try again

### Log Files
```bash
tail -f logs/backend.log          # Backend logs
tail -f logs/internal-frontend.log # Internal frontend logs  
tail -f logs/tenant-frontend.log   # Tenant frontend logs
```

## 🔄 **STOPPING SERVICES**

### Using Deployment Script
Press `Ctrl+C` in the terminal running `start-flotteq.sh`

### Manual Cleanup
```bash
# Kill all FlotteQ processes
pkill -f "php artisan serve"
pkill -f "vite"
pkill -f "npm.*dev"

# Or use saved PIDs
if [ -f .flotteq-pids ]; then
  kill $(cat .flotteq-pids)
  rm .flotteq-pids
fi
```

## 🌟 **FEATURES AVAILABLE**

### Internal Admin Features
- ✅ Tenant management
- ✅ User management
- ✅ Partner network management
- ✅ Global analytics and reporting
- ✅ Support ticket system
- ✅ Subscription management

### Tenant Client Features
- ✅ Vehicle fleet management
- ✅ Maintenance scheduling and tracking
- ✅ Financial reporting and analytics
- ✅ Partner service booking
- ✅ User and role management
- ✅ Document management

## 📞 **SUPPORT**

If you encounter any issues:

1. Check the logs in `./logs/` directory
2. Verify all services are running with `ps aux | grep -E "(artisan|vite|npm)"`
3. Test API endpoints with curl commands above
4. Restart the entire system with `./start-flotteq.sh`

---

**🎉 FlotteQ is now ready for production deployment!**

*Last updated: July 31, 2025*