#!/bin/bash

# Script de correction pour la base de données Railway
echo "🛠️ Correction base de données Railway - FlotteQ"
echo "==============================================="

# Couleurs
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${YELLOW}🔍 Vérification de la configuration Railway...${NC}"

# Vérifier Railway CLI
if ! command -v railway &> /dev/null; then
    echo -e "${RED}❌ Railway CLI non installé !${NC}"
    echo "Installation: npm install -g @railway/cli"
    echo "Puis: railway login"
    exit 1
fi

# Vérifier la connexion Railway
echo "Vérification de la connexion Railway..."
railway status || {
    echo -e "${RED}❌ Pas connecté à Railway ou projet non configuré${NC}"
    echo "Exécuter: railway login"
    echo "Puis: railway link (dans le dossier backend)"
    exit 1
}

echo -e "${GREEN}✅ Railway CLI configuré${NC}"

# Nouvelle méthode pour Railway CLI v3+
echo -e "\n${YELLOW}=== Méthodes de connexion à la base de données ===${NC}"

echo -e "\n${BLUE}📝 MÉTHODE 1: Railway Shell (Recommandée)${NC}"
echo "1. Exécuter: railway shell"
echo "2. Dans le shell Railway:"
echo "   psql \$DATABASE_URL"
echo "3. Dans psql, exécuter:"
echo "   \\i backend/fix-internal-users.sql"
echo ""

echo -e "${BLUE}📝 MÉTHODE 2: Variables d'environnement${NC}"
echo "1. Récupérer l'URL de la base:"
echo "   railway variables get DATABASE_URL"
echo "2. Se connecter directement:"
echo "   psql \"[URL_RÉCUPÉRÉE]\" -c \"\\i backend/fix-internal-users.sql\""
echo ""

echo -e "${BLUE}📝 MÉTHODE 3: Via Railway proxy${NC}"
echo "1. railway run bash"
echo "2. psql \$DATABASE_URL"
echo "3. \\i backend/fix-internal-users.sql"

# Tentative automatique si possible
echo -e "\n${YELLOW}=== Tentative de connexion automatique ===${NC}"

cd backend

echo "Tentative 1: Railway run avec psql..."
if railway run psql $DATABASE_URL -f fix-internal-users.sql 2>/dev/null; then
    echo -e "${GREEN}✅ Script SQL exécuté avec succès !${NC}"
    echo "Vérification des utilisateurs créés..."
    railway run psql $DATABASE_URL -c "SELECT id, email, is_internal, role_interne FROM users WHERE is_internal = true;" || echo "Requête de vérification échouée"
    
    echo -e "\n${GREEN}🎉 Base de données corrigée !${NC}"
else
    echo -e "${YELLOW}⚠️ Méthode automatique échouée${NC}"
    echo -e "${BLUE}Utiliser une des méthodes manuelles ci-dessus.${NC}"
    
    echo -e "\n${YELLOW}=== Instructions détaillées ===${NC}"
    echo "1. Se placer dans le dossier backend:"
    echo "   cd backend"
    echo ""
    echo "2. Lancer Railway shell:"
    echo "   railway shell"
    echo ""
    echo "3. Dans le shell Railway, se connecter à PostgreSQL:"
    echo "   psql \$DATABASE_URL"
    echo ""
    echo "4. Exécuter le script SQL:"
    echo "   \\i fix-internal-users.sql"
    echo ""
    echo "5. Vérifier les résultats:"
    echo "   SELECT * FROM users WHERE is_internal = true;"
    echo ""
    echo "6. Quitter psql:"
    echo "   \\q"
    echo ""
    echo "7. Quitter Railway shell:"
    echo "   exit"
fi

cd ..

echo -e "\n${BLUE}📄 Contenu du script SQL à exécuter:${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
cat backend/fix-internal-users.sql
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo -e "\n${GREEN}🎯 Objectif: Corriger is_internal = true pour les employés FlotteQ${NC}"