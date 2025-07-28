#!/bin/bash

echo "🚀 Push FORCE vers les deux repositories..."
echo "⚠️  Attention: Cette commande écrase les changements distants!"

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
echo "📤 2. Push FORCE vers Gitea..."
git push --force gitea main

if [ $? -eq 0 ]; then
    echo "✅ Gitea push FORCE réussi!"
else
    echo "❌ Erreur Gitea push"
    exit 1
fi

echo ""
echo "🎉 Push FORCE terminé sur les deux repositories!"
echo "   - GitHub: https://github.com/Wissem95/Flotteq_prod.git"
echo "   - Gitea:  https://gitea.belprelocation.fr/pwepwe973/FLOTTEQ.git" 