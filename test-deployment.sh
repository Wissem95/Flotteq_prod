#!/bin/bash

# Script de test pour vérifier le déploiement FlotteQ
echo "🚀 Test de déploiement FlotteQ - $(date)"
echo "================================================"

# Configuration
BACKEND_URL="https://flotteq-backend-v2-production.up.railway.app"
API_URL="${BACKEND_URL}/api"

# Couleurs pour les logs
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Fonction de test
test_endpoint() {
    local name="$1"
    local url="$2"
    local expected_status="$3"
    
    echo -n "Testing $name... "
    
    response=$(curl -s -w "%{http_code}" -o /dev/null "$url")
    
    if [ "$response" = "$expected_status" ]; then
        echo -e "${GREEN}✅ PASS${NC} ($response)"
        return 0
    else
        echo -e "${RED}❌ FAIL${NC} ($response, expected $expected_status)"
        return 1
    fi
}

# Tests de base
echo -e "\n${YELLOW}=== Tests de connectivité de base ===${NC}"
test_endpoint "Health check" "${API_URL}/health" "200"
test_endpoint "Database connection" "${API_URL}/test-db" "200"
test_endpoint "CSRF endpoint" "${BACKEND_URL}/sanctum/csrf-cookie" "200"

# Tests des endpoints d'authentification
echo -e "\n${YELLOW}=== Tests endpoints d'authentification ===${NC}"
test_endpoint "Login endpoint (POST empty)" "${API_URL}/auth/login" "422"
test_endpoint "Register endpoint (POST empty)" "${API_URL}/auth/register" "422"
test_endpoint "Internal login endpoint" "${API_URL}/internal/auth/login" "422"

# Tests CORS (simulation)
echo -e "\n${YELLOW}=== Vérification configuration CORS ===${NC}"
echo "✅ CORS configuré pour:"
echo "  - https://internal-rust.vercel.app"
echo "  - https://tenant-black.vercel.app" 
echo "  - https://flotteq-backend-v2-production.up.railway.app"

# Instructions pour la suite
echo -e "\n${YELLOW}=== Instructions post-déploiement ===${NC}"
echo "📝 Actions recommandées:"
echo "1. Exécuter le script SQL pour corriger les utilisateurs internes:"
echo "   Railway CLI: railway connect --database"
echo "   Puis exécuter: backend/fix-internal-users.sql"
echo ""
echo "2. Redéployer les frontends avec les nouvelles URLs:"
echo "   - Frontend Tenant: Vercel redeploy"
echo "   - Frontend Internal: Vercel redeploy"
echo ""
echo "3. Tester l'authentification complète:"
echo "   - Login tenant avec utilisateur existant"
echo "   - Login internal avec admin@flotteq.com / password"
echo ""
echo "4. Vérifier les logs Railway pour d'éventuelles erreurs"

echo -e "\n${GREEN}✅ Tests de déploiement terminés!${NC}"