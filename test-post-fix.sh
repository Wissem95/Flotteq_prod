#!/bin/bash

# Test après corrections Railway
API_URL="https://flotteq-backend-v2-production.up.railway.app"

echo "🔍 DIAGNOSTIC POST-FIX RAILWAY"
echo "API: $API_URL"
echo "================================================"

# 1. Health Check (doit toujours marcher)
echo -e "\n✅ 1. Health Check:"
curl -s "$API_URL/api/health" | jq '.'

# 2. Database Health  
echo -e "\n✅ 2. Database Health:"
curl -s "$API_URL/api/internal/auth/health/database" | jq '.'

# 3. Test Login avec headers complets
echo -e "\n🔐 3. Test Login (headers complets):"
curl -i -X POST "$API_URL/api/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "User-Agent: FlotteQ-Test/1.0" \
  -H "Origin: https://tenant-black.vercel.app" \
  -d '{"login":"admin@flotteq.com","password":"password123"}' \
  2>/dev/null | head -20

# 4. Test Internal Login  
echo -e "\n🏢 4. Test Internal Login:"
curl -i -X POST "$API_URL/api/internal/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"admin@flotteq.com","password":"password123"}' \
  2>/dev/null | head -20

# 5. Test CORS Preflight
echo -e "\n🌐 5. Test CORS Preflight:"
curl -i -X OPTIONS "$API_URL/api/auth/login" \
  -H "Origin: https://tenant-black.vercel.app" \
  -H "Access-Control-Request-Method: POST" \
  -H "Access-Control-Request-Headers: Content-Type" \
  2>/dev/null | head -10

# 6. Test Tenant Resolution
echo -e "\n🔍 6. Test Tenant Resolution:"
curl -s "$API_URL/api/auth/tenant-from-host" \
  -H "Accept: application/json" | jq '.'

echo -e "\n================================================"
echo "🎯 DIAGNOSTIC COMPLÉTÉ"
echo ""
echo "💡 INDICES À CHERCHER :"
echo "- Status 200 = ✅ OK"  
echo "- Status 401 = 🔒 Auth requis (normal)"
echo "- Status 302 = ❌ Redirection (problème)"
echo "- Status 500 = ❌ Erreur serveur"
echo "- JSON response = ✅ API fonctionne" 
echo "- HTML response = ❌ Redirection vers web"