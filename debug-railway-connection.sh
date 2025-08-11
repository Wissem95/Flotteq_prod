#!/bin/bash

echo "🔍 Debug de la connexion Railway"
echo "================================"

# Dans le shell Railway, exécutez ces commandes une par une :

echo "1. Vérifier les variables d'environnement Railway :"
echo "   env | grep DATABASE"
echo ""

echo "2. Afficher l'URL complète de la base :"
echo "   echo \$DATABASE_URL"
echo ""

echo "3. Si DATABASE_URL est vide, essayer :"
echo "   echo \$POSTGRES_URL"
echo "   echo \$DB_URL"
echo ""

echo "4. Lister toutes les variables Railway :"
echo "   env | grep -i postgres"
echo "   env | grep -i db"
echo ""

echo "5. Si vous trouvez l'URL, se connecter avec :"
echo "   psql \"[URL_TROUVÉE]\""
echo ""

echo "6. MÉTHODE ALTERNATIVE - Via Railway CLI :"
echo "   exit  # Sortir du shell Railway"
echo "   railway connect  # Se connecter directement"
echo ""

echo "7. MÉTHODE ALTERNATIVE 2 - Variables Railway :"
echo "   railway variables  # Voir toutes les variables"
echo "   railway variables get DATABASE_URL  # Récupérer l'URL"
echo ""

echo "8. MÉTHODE ALTERNATIVE 3 - Dashboard Railway :"
echo "   - Aller sur dashboard.railway.app"
echo "   - Ouvrir le projet FlotteQ"
echo "   - Onglet 'Database'"
echo "   - Cliquer 'Connect'"
echo "   - Utiliser l'interface web"