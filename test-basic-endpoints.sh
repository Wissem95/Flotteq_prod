#!/bin/bash

# Script de validation basique des endpoints FlotteQ
# Test des endpoints critiques sans authentification

API_URL="https://flotteq-backend-v2-production.up.railway.app/api"
echo "🔍 BASIC ENDPOINT VALIDATION"
echo "API URL: $API_URL"
echo "================================================"

echo -e "\n✅ Health Check:"
curl -s "$API_URL/health" | jq '.' 2>/dev/null || curl -s "$API_URL/health"

echo -e "\n🔐 Tenant Resolution (should work):"
curl -s "$API_URL/auth/tenant-from-host" | jq '.' 2>/dev/null || curl -s "$API_URL/auth/tenant-from-host"

echo -e "\n🏢 Internal Endpoints (should return 401 or 500):"
echo "  - Internal tenants:"
curl -s -w " (HTTP: %{http_code})" "$API_URL/internal/tenants" | head -c 200
echo ""

echo "  - Internal tenant-users (NEW):"
curl -s -w " (HTTP: %{http_code})" "$API_URL/internal/tenant-users" | head -c 200
echo ""

echo "  - Internal employees:"
curl -s -w " (HTTP: %{http_code})" "$API_URL/internal/employees" | head -c 200
echo ""

echo -e "\n🏬 Tenant Endpoints (should return 401):"
echo "  - Users (with X-Tenant-ID):"
curl -s -w " (HTTP: %{http_code})" -H "X-Tenant-ID: 1" "$API_URL/users" | head -c 200
echo ""

echo "  - Vehicles (with X-Tenant-ID):"
curl -s -w " (HTTP: %{http_code})" -H "X-Tenant-ID: 1" "$API_URL/vehicles" | head -c 200
echo ""

echo -e "\n📋 SUMMARY:"
echo "✅ Health should return 200"
echo "🔒 Protected endpoints should return 401 (auth required)"
echo "❌ 500 errors indicate backend deployment issues"