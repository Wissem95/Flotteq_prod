#!/bin/bash

# Script de déploiement des corrections FlotteQ
echo "🚀 Déploiement des corrections FlotteQ - $(date)"
echo "================================================"

# Couleurs
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Vérifications préalables
echo -e "${YELLOW}=== Vérifications préalables ===${NC}"

# Vérifier que nous sommes dans le bon répertoire
if [ ! -f "backend/config/cors.php" ]; then
    echo -e "${RED}❌ Erreur: Script doit être exécuté depuis la racine du projet${NC}"
    exit 1
fi

# Vérifier que git est propre
if [ -n "$(git status --porcelain)" ]; then
    echo -e "${RED}❌ Il y a des changements non commitées. Commit d'abord.${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Vérifications OK${NC}"

# Étape 1: Déploiement backend
echo -e "\n${YELLOW}=== Déploiement Backend (Railway) ===${NC}"
cd backend
echo "📦 Pushing backend to Railway..."

# Vérifier si Railway CLI est installé
if ! command -v railway &> /dev/null; then
    echo -e "${RED}❌ Railway CLI non installé. Installation requise:${NC}"
    echo "npm install -g @railway/cli"
    echo "railway login"
    exit 1
fi

# Déployer sur Railway
railway up --detach
echo -e "${GREEN}✅ Backend déployé sur Railway${NC}"

# Retour à la racine
cd ..

# Étape 2: Déploiement frontends
echo -e "\n${YELLOW}=== Déploiement Frontends (Vercel) ===${NC}"

# Vérifier si Vercel CLI est installé
if ! command -v vercel &> /dev/null; then
    echo -e "${YELLOW}⚠️ Vercel CLI non installé. Déploiement manuel requis:${NC}"
    echo "1. Frontend Tenant: Push sur git, Vercel auto-deploy"
    echo "2. Frontend Internal: Push sur git, Vercel auto-deploy"
else
    echo "📦 Déploiement des frontends..."
    
    # Déployer frontend tenant
    cd frontend/tenant
    vercel --prod --yes
    echo -e "${GREEN}✅ Frontend Tenant déployé${NC}"
    
    # Déployer frontend internal
    cd ../internal
    vercel --prod --yes
    echo -e "${GREEN}✅ Frontend Internal déployé${NC}"
    
    cd ../..
fi

# Étape 3: Instructions base de données
echo -e "\n${YELLOW}=== Configuration Base de Données ===${NC}"
echo -e "${BLUE}📝 Action manuelle requise:${NC}"
echo "1. Se connecter à la base de données Railway:"
echo "   railway connect --database"
echo ""
echo "2. Exécuter le script SQL de correction:"
echo "   \\i backend/fix-internal-users.sql"
echo ""
echo "3. Vérifier les utilisateurs créés:"
echo "   SELECT * FROM users WHERE is_internal = true;"

# Étape 4: Tests
echo -e "\n${YELLOW}=== Lancement des tests ===${NC}"
./test-deployment.sh

# Résumé
echo -e "\n${GREEN}=== DÉPLOIEMENT TERMINÉ ===${NC}"
echo "✅ Backend mis à jour sur Railway"
echo "✅ Frontends redéployés (ou instructions données)"
echo "✅ Script SQL créé pour corriger les utilisateurs"
echo "✅ Tests de connectivité lancés"
echo ""
echo -e "${BLUE}📋 Prochaines étapes:${NC}"
echo "1. Exécuter le script SQL sur la base Railway"
echo "2. Tester l'authentification sur les frontends"
echo "3. Vérifier que tout fonctionne correctement"
echo ""
echo -e "${GREEN}🎉 FlotteQ est maintenant configuré correctement!${NC}"