#!/bin/bash

# Script de redéploiement forcé des frontends FlotteQ
echo "🚀 Redéploiement forcé des frontends FlotteQ"
echo "============================================"

# Couleurs
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuration
NEW_API_URL="https://flotteq-backend-v2-production.up.railway.app/api"

echo -e "${YELLOW}🔧 Configuration API URL: ${NEW_API_URL}${NC}"

# Étape 1: Commit des changements
echo -e "\n${YELLOW}=== Commit des derniers changements ===${NC}"
git add .
git commit -m "🔧 Force redeploy avec nouvelles URLs API

🤖 Generated with [Claude Code](https://claude.ai/code)

Co-Authored-By: Claude <noreply@anthropic.com>" || echo "Rien à commiter"

# Étape 2: Push pour déclencher les redéploiements auto
echo -e "\n${YELLOW}=== Push vers GitHub pour déclencher auto-deploy ===${NC}"
git push origin main

# Étape 3: Instructions pour Vercel Dashboard
echo -e "\n${BLUE}=== IMPORTANT: Actions manuelles requises sur Vercel Dashboard ===${NC}"
echo ""
echo -e "${RED}⚠️  Les frontends Vercel utilisent peut-être encore l'ancienne URL !${NC}"
echo ""
echo "📝 Actions à effectuer sur dashboard.vercel.com :"
echo ""
echo "1️⃣  FRONTEND TENANT (tenant-black.vercel.app):"
echo "   - Aller sur le projet tenant"
echo "   - Settings > Environment Variables"
echo "   - Modifier VITE_API_URL = ${NEW_API_URL}"
echo "   - Cliquer 'Redeploy' sur le dernier déploiement"
echo ""
echo "2️⃣  FRONTEND INTERNAL (internal-rust.vercel.app):"
echo "   - Aller sur le projet internal"
echo "   - Settings > Environment Variables" 
echo "   - Modifier VITE_API_URL = ${NEW_API_URL}"
echo "   - Cliquer 'Redeploy' sur le dernier déploiement"
echo ""

# Étape 4: Vérification CLI Vercel si disponible
if command -v vercel &> /dev/null; then
    echo -e "${YELLOW}=== Tentative de mise à jour via Vercel CLI ===${NC}"
    
    echo "Mise à jour des variables d'environnement..."
    
    # Frontend Tenant
    echo "📦 Configuration Frontend Tenant..."
    cd frontend/tenant
    vercel env add VITE_API_URL production <<< "${NEW_API_URL}"
    vercel --prod
    cd ../..
    
    # Frontend Internal  
    echo "📦 Configuration Frontend Internal..."
    cd frontend/internal
    vercel env add VITE_API_URL production <<< "${NEW_API_URL}"
    vercel --prod
    cd ..
    
    echo -e "${GREEN}✅ Frontends redéployés via CLI${NC}"
else
    echo -e "${YELLOW}ℹ️  Vercel CLI non disponible - utiliser le dashboard manuel${NC}"
fi

# Étape 5: Tests
echo -e "\n${YELLOW}=== Attendre 2-3 minutes puis tester ===${NC}"
echo "🔗 URLs à tester après redéploiement:"
echo "   - Tenant: https://tenant-black.vercel.app"
echo "   - Internal: https://internal-rust.vercel.app"
echo ""
echo "✅ Vérifier que les erreurs CORS ont disparu"
echo "✅ Tester l'authentification"

echo -e "\n${GREEN}🎉 Script de redéploiement terminé !${NC}"
echo -e "${BLUE}⏳ Attendre quelques minutes pour que Vercel redéploie...${NC}"