# 🔧 Guide de correction Google OAuth - Erreur "invalid_grant"

## 📋 Problèmes identifiés

1. **Redirection après connexion** : ✅ **CORRIGÉ**
2. **Erreur Google OAuth "invalid_grant"** : ⚠️ **NÉCESSITE CONFIGURATION**

## 🔥 Solutions appliquées

### ✅ 1. Correction de la redirection après connexion

- **Hook `useAuth`** : Utilise maintenant la bonne clé "user" au lieu de "currentUser"
- **Service `authService`** : Nouvelle fonction `handleLoginSuccess()` pour gérer la redirection
- **Composant `GoogleCallback`** : Simplifié et utilise la nouvelle logique
- **Page `Login`** : Utilise la nouvelle fonction `handleLoginSuccess()`

### ⚠️ 2. Configuration Google OAuth (À FAIRE)

L'erreur "invalid_grant" est causée par une **mauvaise configuration**. Voici comment la corriger :

#### 🔧 Étape 1 : Vérifier les variables d'environnement

Dans votre fichier `.env` du backend Laravel :

```env
# Configuration Google OAuth
GOOGLE_CLIENT_ID=votre_client_id_google.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=votre_client_secret_google
GOOGLE_REDIRECT_URI=https://votre-domaine.com/api/auth/google/callback

# URLs autorisées
APP_URL=https://votre-domaine.com
FRONTEND_URL=https://votre-frontend.com
```

#### 🔧 Étape 2 : Configuration Google Cloud Console

1. **Aller dans [Google Cloud Console](https://console.cloud.google.com/)**
2. **Sélectionner votre projet**
3. **Aller dans "APIs & Services" > "Credentials"**
4. **Modifier votre OAuth 2.0 Client ID**

#### 🔧 Étape 3 : URLs autorisées

Ajoutez ces URLs dans votre configuration Google :

**JavaScript origins autorisés :**

```
https://votre-domaine.com
https://votre-frontend.com
http://localhost:3000 (pour dev)
```

**URIs de redirection autorisées :**

```
https://votre-domaine.com/api/auth/google/callback
https://votre-frontend.com/google-callback
http://localhost:3000/google-callback (pour dev)
```

#### 🔧 Étape 4 : Test de la configuration

Testez avec cette commande pour vérifier la connectivité :

```bash
curl -X POST https://votre-domaine.com/api/auth/google/redirect \
  -H "Content-Type: application/json" \
  -d '{"tenant_id": 1}'
```

## 🧪 Comment tester

1. **Connexion normale** :

   - Allez sur `/login`
   - Connectez-vous avec email/mot de passe
   - Vérifiez la redirection vers `/dashboard`

2. **Connexion Google** :
   - Cliquez sur "Connexion via Google"
   - Authentifiez-vous
   - Vérifiez la redirection automatique

## 🐛 Debugging

Si vous avez encore des problèmes :

1. **Vérifiez les logs Laravel** :

```bash
tail -f storage/logs/laravel.log
```

2. **Vérifiez la console JavaScript** (F12)

3. **Vérifiez que les URLs correspondent exactement** entre :
   - Votre configuration Google Cloud
   - Vos variables d'environnement
   - Votre configuration frontend

## 📞 Points de vérification

- [ ] Variables d'environnement correctes
- [ ] URLs de redirection configurées dans Google Cloud
- [ ] Backend Laravel accessible depuis le frontend
- [ ] CORS configuré correctement
- [ ] Certificats SSL valides (pour HTTPS)

## ⚡ Test rapide

Pour tester immédiatement :

```javascript
// Dans la console du navigateur
localStorage.setItem('token', 'test-token');
localStorage.setItem('user', JSON.stringify({ id: 1, email: 'test@test.com' }));
// Puis rechargez la page - vous devriez aller sur le dashboard
```

**Une fois la configuration Google correcte, tout devrait fonctionner parfaitement !** 🎉
