#!/bin/bash

# Script complet : add, commit et push vers les deux repositories

echo "🚀 Déploiement automatique vers GitHub et Gitea..."

# Vérifier s'il y a des changements
if [[ -z $(git status --porcelain) ]]; then
    echo "📝 Aucun changement détecté. Tentative de push des commits existants..."
else
    echo "📝 Changements détectés. Add et commit en cours..."
    
    # Add tous les fichiers
    echo "📦 Git add..."
    git add .
    
    # Demander le message de commit ou utiliser un message par défaut
    if [ -z "$1" ]; then
        COMMIT_MSG="Auto-deploy: $(date '+%Y-%m-%d %H:%M:%S')"
        echo "💬 Message de commit par défaut: $COMMIT_MSG"
    else
        COMMIT_MSG="$1"
        echo "💬 Message de commit: $COMMIT_MSG"
    fi
    
    # Commit
    echo "💾 Git commit..."
    git commit -m "$COMMIT_MSG"
    
    if [ $? -ne 0 ]; then
        echo "❌ Erreur lors du commit"
        exit 1
    fi
fi

echo ""
echo "📤 Push vers les repositories..."

# Push vers GitHub
echo "📤 1. Push vers GitHub (origin)..."
git push origin main

if [ $? -eq 0 ]; then
    echo "✅ GitHub push réussi!"
else
    echo "❌ Erreur GitHub push"
    exit 1
fi

# Push vers Gitea (avec force au cas où)
echo "📤 2. Push vers Gitea..."
git push gitea main

if [ $? -ne 0 ]; then
    echo "⚠️  Push normal vers Gitea échoué, tentative avec --force..."
    git push --force gitea main
    
    if [ $? -eq 0 ]; then
        echo "✅ Gitea push FORCE réussi!"
    else
        echo "❌ Erreur Gitea push (même avec force)"
        exit 1
    fi
else
    echo "✅ Gitea push réussi!"
fi

echo ""
echo "🎉 Déploiement terminé avec succès!"
echo "   - GitHub: https://github.com/Wissem95/Flotteq_prod.git"
echo "   - Gitea:  https://gitea.belprelocation.fr/pwepwe973/FLOTTEQ.git"
echo ""
echo "💡 Usage:"
echo "   ./deploy.sh                           # Message auto"
echo "   ./deploy.sh \"Mon message de commit\"  # Message custom" 