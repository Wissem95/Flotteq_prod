#!/bin/bash

# Script de configuration des domaines personnalisés Vercel
echo "🌐 Configuration des domaines personnalisés FlotteQ"
echo "================================================"

# Couleurs
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${YELLOW}🎯 Objectif: Configurer internal-rust.vercel.app et tenant-black.vercel.app${NC}"

# Vérifier Vercel CLI
if ! command -v vercel &> /dev/null; then
    echo -e "${RED}❌ Vercel CLI non installé !${NC}"
    echo "Installation: npm install -g vercel"
    echo "Puis: vercel login"
    exit 1
fi

echo -e "${GREEN}✅ Vercel CLI disponible${NC}"

# Configuration Frontend Internal
echo -e "\n${YELLOW}=== Configuration Frontend Internal ===${NC}"
cd frontend/internal

echo "📝 Ajout du domaine internal-rust.vercel.app..."
if vercel domains add internal-rust.vercel.app; then
    echo -e "${GREEN}✅ Domaine internal-rust.vercel.app ajouté${NC}"
else
    echo -e "${YELLOW}⚠️ Domaine peut-être déjà configuré ou erreur${NC}"
fi

# Configuration Frontend Tenant  
echo -e "\n${YELLOW}=== Configuration Frontend Tenant ===${NC}"
cd ../tenant

echo "📝 Ajout du domaine tenant-black.vercel.app..."
if vercel domains add tenant-black.vercel.app; then
    echo -e "${GREEN}✅ Domaine tenant-black.vercel.app ajouté${NC}"
else
    echo -e "${YELLOW}⚠️ Domaine peut-être déjà configuré ou erreur${NC}"
fi

cd ../..

# Instructions manuelles si CLI échoue
echo -e "\n${BLUE}=== Si la commande CLI échoue, configuration manuelle ===${NC}"
echo ""
echo "📋 Via dashboard.vercel.com :"
echo ""
echo "1️⃣  Frontend Internal :"
echo "   - Aller sur https://dashboard.vercel.com"
echo "   - Ouvrir le projet 'internal'"
echo "   - Settings > Domains"
echo "   - Add Domain: internal-rust.vercel.app"
echo "   - Suivre les instructions DNS si nécessaire"
echo ""
echo "2️⃣  Frontend Tenant :"
echo "   - Aller sur le projet 'tenant'"
echo "   - Settings > Domains" 
echo "   - Add Domain: tenant-black.vercel.app"
echo "   - Suivre les instructions DNS si nécessaire"

echo -e "\n${YELLOW}=== Vérification des domaines ===${NC}"
echo "🔍 Test de connectivité..."

sleep 5  # Attendre quelques secondes

echo -n "Testing internal-rust.vercel.app... "
if curl -s -w "%{http_code}" -o /dev/null https://internal-rust.vercel.app --max-time 10 | grep -q "200\|401\|404"; then
    echo -e "${GREEN}✅ Accessible${NC}"
else
    echo -e "${RED}❌ Non accessible${NC}"
fi

echo -n "Testing tenant-black.vercel.app... "
if curl -s -w "%{http_code}" -o /dev/null https://tenant-black.vercel.app --max-time 10 | grep -q "200\|401\|404"; then
    echo -e "${GREEN}✅ Accessible${NC}"
else
    echo -e "${RED}❌ Non accessible${NC}"
fi

echo -e "\n${BLUE}=== Notes importantes ===${NC}"
echo "⏱️  Les domaines personnalisés peuvent prendre 5-10 minutes à se propager"
echo "🔄 Si les domaines ne fonctionnent pas immédiatement, attendre et réessayer"
echo "🌐 Les domaines .vercel.app sont gratuits et se configurent automatiquement"

echo -e "\n${GREEN}🎉 Configuration des domaines terminée !${NC}"
echo -e "${YELLOW}⏳ Attendre quelques minutes puis tester :${NC}"
echo "   - https://internal-rust.vercel.app/login"
echo "   - https://tenant-black.vercel.app/login"