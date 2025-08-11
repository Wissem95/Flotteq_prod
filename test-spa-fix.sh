#!/bin/bash

# Script de test pour vérifier le fix SPA routing
echo "🔧 Test de la correction SPA Routing - FlotteQ"
echo "============================================="

# Couleurs
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# URLs à tester
TENANT_URL="https://tenant-black.vercel.app"
INTERNAL_URL="https://internal-rust.vercel.app"

echo -e "${YELLOW}🚀 Vérification du redéploiement Vercel...${NC}"

# Fonction de test
test_spa_route() {
    local name="$1"
    local base_url="$2"
    local route="$3"
    local full_url="${base_url}${route}"
    
    echo -n "  $name ($route)... "
    
    # Test avec timeout et user-agent
    response=$(curl -s -w "%{http_code}" -o /dev/null \
        -H "User-Agent: Mozilla/5.0 (compatible; FlotteQ-Test)" \
        --max-time 15 "$full_url")
    
    if [ "$response" = "200" ]; then
        echo -e "${GREEN}✅ OK${NC} ($response)"
        return 0
    elif [ "$response" = "404" ]; then
        echo -e "${RED}❌ 404 - SPA config pas encore active${NC}"
        return 1
    else
        echo -e "${YELLOW}⚠️ Autre${NC} ($response)"
        return 1
    fi
}

# Test des pages principales
echo -e "\n${YELLOW}=== Test Frontend Tenant ===${NC}"
test_spa_route "Page d'accueil" "$TENANT_URL" "/"
test_spa_route "Page de login" "$TENANT_URL" "/login"
test_spa_route "Dashboard" "$TENANT_URL" "/dashboard"

echo -e "\n${YELLOW}=== Test Frontend Internal ===${NC}"
test_spa_route "Page d'accueil" "$INTERNAL_URL" "/"
test_spa_route "Page de login" "$INTERNAL_URL" "/login"  
test_spa_route "Dashboard admin" "$INTERNAL_URL" "/dashboard"

# Vérification des déploiements Vercel
echo -e "\n${YELLOW}=== Vérification des déploiements Vercel ===${NC}"
echo "📦 Vérification si vercel.json est déployé..."

# Check via CLI si disponible
if command -v vercel &> /dev/null; then
    echo "🔍 Status des déploiements via Vercel CLI:"
    cd frontend/tenant && vercel ls --limit=1
    cd ../internal && vercel ls --limit=1
    cd ../..
else
    echo "ℹ️  Vercel CLI non disponible - vérification manuelle"
fi

# Instructions
echo -e "\n${BLUE}=== Instructions si les tests échouent ===${NC}"
echo ""
echo "1. 🕒 Attendre 2-3 minutes supplémentaires (déploiement Vercel)"
echo "2. 🔄 Relancer ce script: ./test-spa-fix.sh"
echo "3. 🌐 Tester manuellement dans le navigateur:"
echo "   - Aller sur: $TENANT_URL/login"
echo "   - Recharger la page (F5 ou Ctrl+R)"
echo "   - Si pas d'erreur 404 = ✅ Corrigé !"
echo ""
echo "4. 📱 Dashboard Vercel pour forcer le redéploiement:"
echo "   - https://dashboard.vercel.com"
echo "   - Projets tenant et internal"
echo "   - Cliquer 'Redeploy' sur le dernier build"

echo -e "\n${YELLOW}🎯 Objectif: Plus d'erreurs 404 sur reload${NC}"
echo -e "${GREEN}✅ Une fois corrigé, FlotteQ sera 100% fonctionnel !${NC}"