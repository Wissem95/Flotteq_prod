#!/bin/bash

echo "🚀 Push vers les deux repositories..."

echo ""
echo "📤 1. Push vers GitHub (origin)..."
git push origin main

if [ $? -eq 0 ]; then
    echo "✅ GitHub push réussi!"
else
    echo "❌ Erreur GitHub push"
    exit 1
fi

echo ""
echo "📤 2. Push vers Gitea..."
git push gitea main

if [ $? -eq 0 ]; then
    echo "✅ Gitea push réussi!"
else
    echo "❌ Erreur Gitea push"
    exit 1
fi

echo ""
echo "🎉 Push terminé sur les deux repositories!"
echo "   - GitHub: https://github.com/Wissem95/Flotteq_prod.git"
echo "   - Gitea:  https://gitea.belprelocation.fr/pwepwe973/FLOTTEQ.git" 