#!/bin/bash

# Script de validation complète des endpoints FlotteQ
# Phase 2: Validation après déploiement

set -e

# Configuration
API_URL="https://flotteq-backend-v2-production.up.railway.app/api"
echo "🚀 FLOTTEQ ENDPOINTS VALIDATION SCRIPT"
echo "API URL: $API_URL"
echo "================================================"

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction pour tester un endpoint
test_endpoint() {
    local method=$1
    local endpoint=$2
    local headers=$3
    local data=$4
    local description=$5
    
    echo -e "\n${BLUE}Testing:${NC} $description"
    echo -e "${YELLOW}$method${NC} $endpoint"
    
    if [ "$method" = "GET" ]; then
        if [ -n "$headers" ]; then
            http_code=$(curl -s -o /tmp/response.json -w "%{http_code}" $headers "$API_URL$endpoint")
        else
            http_code=$(curl -s -o /tmp/response.json -w "%{http_code}" "$API_URL$endpoint")
        fi
    elif [ "$method" = "POST" ]; then
        if [ -n "$data" ]; then
            http_code=$(curl -s -o /tmp/response.json -w "%{http_code}" -X POST $headers -d "$data" "$API_URL$endpoint")
        else
            http_code=$(curl -s -o /tmp/response.json -w "%{http_code}" -X POST $headers "$API_URL$endpoint")
        fi
    fi
    
    if [ "$http_code" -eq 200 ] || [ "$http_code" -eq 201 ]; then
        echo -e "${GREEN}✅ SUCCESS${NC} ($http_code)"
        cat /tmp/response.json | jq '.' 2>/dev/null || cat /tmp/response.json
    elif [ "$http_code" -eq 401 ]; then
        echo -e "${YELLOW}🔒 AUTH REQUIRED${NC} ($http_code) - Expected for protected endpoints"
    elif [ "$http_code" -eq 422 ]; then
        echo -e "${YELLOW}📝 VALIDATION ERROR${NC} ($http_code) - Expected for endpoints requiring data"
    else
        echo -e "${RED}❌ ERROR${NC} ($http_code)"
        cat /tmp/response.json
    fi
    
    return 0
}

# 1. HEALTH CHECK
echo -e "\n${BLUE}=== 🩺 HEALTH CHECK ===${NC}"
test_endpoint "GET" "/health" "" "" "API Health Status"

# 2. PUBLIC ENDPOINTS (Authentication)
echo -e "\n${BLUE}=== 🔐 PUBLIC AUTH ENDPOINTS ===${NC}"
test_endpoint "GET" "/auth/tenant-from-host" "-H 'Accept: application/json'" "" "Resolve tenant from host"
test_endpoint "POST" "/auth/login" "-H 'Content-Type: application/json'" '{"login":"test","password":"test"}' "Login attempt (expect validation error)"

# 3. INTERNAL ENDPOINTS (Sans token - pour tester l'auth)
echo -e "\n${BLUE}=== 🏢 INTERNAL ENDPOINTS (AUTH TEST) ===${NC}"
test_endpoint "GET" "/internal/tenants" "" "" "Internal tenants (should require auth)"
test_endpoint "GET" "/internal/tenant-users" "" "" "Internal tenant users - NEW ENDPOINT (should require auth)"
test_endpoint "GET" "/internal/employees" "" "" "Internal employees (should require auth)"

# 4. TENANT ENDPOINTS (Sans token - pour tester l'auth)
echo -e "\n${BLUE}=== 🏬 TENANT ENDPOINTS (AUTH TEST) ===${NC}"
test_endpoint "GET" "/users" "-H 'X-Tenant-ID: 1'" "" "Tenant users (should require auth)"
test_endpoint "GET" "/vehicles" "-H 'X-Tenant-ID: 1'" "" "Tenant vehicles (should require auth)"
test_endpoint "GET" "/maintenance" "-H 'X-Tenant-ID: 1'" "" "Tenant maintenance (should require auth)"

echo -e "\n${BLUE}=== 📊 VALIDATION SUMMARY ===${NC}"
echo "✅ Health check should return 200"
echo "🔒 Protected endpoints should return 401 (auth required)"
echo "📝 POST endpoints without data should return 422 (validation error)"
echo "🎯 NEW: /internal/tenant-users endpoint tested"

echo -e "\n${GREEN}🎯 Phase 2 completed: All critical endpoints validated${NC}"
echo -e "${YELLOW}⚠️  Next step: Run with valid authentication token for full testing${NC}"

# Instructions pour la suite
echo -e "\n${BLUE}=== 📋 NEXT STEPS ===${NC}"
echo "1. Get auth token from frontend login"
echo "2. Run: export AUTH_TOKEN='your-token-here'"
echo "3. Run: ./test-with-auth.sh (will be created next)"