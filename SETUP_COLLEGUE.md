# 🚀 Setup rapide après pull - Résoudre erreurs 500 & 422

## 🔴 Problèmes courants après pull

- **Erreur 500** : Permissions manquantes, migrations non exécutées
- **Erreur 422** : Validation échouée, champs mal formatés
- **Frontend qui ne se connecte pas** : Configuration manquante

---

## ⚡ Solution complète (dans l'ordre strict)

### 1. Backend - Installation dépendances

```bash
cd FLOTTEQ/backend
composer install --no-dev --optimize-autoloader
```

### 2. Vérifier la configuration de base

```bash
# Vérifier que le fichier .env existe
ls -la .env

# Si .env manque, copier depuis .env.example
cp .env.example .env

# Générer la clé d'application
php artisan key:generate
```

### 3. Migrations et base de données

```bash
# Exécuter toutes les migrations
php artisan migrate --force

# Si erreur "table already exists"
php artisan migrate:status
```

### 4. 🚨 ÉTAPE CRITIQUE : Créer les permissions

```bash
php artisan tinker --execute="
// Créer les permissions véhicules
\$permissions = [
    'view vehicles',
    'create vehicles',
    'edit vehicles',
    'delete vehicles',
    'export vehicles'
];

foreach (\$permissions as \$permission) {
    if (!Spatie\\Permission\\Models\\Permission::where('name', \$permission)->exists()) {
        Spatie\\Permission\\Models\\Permission::create(['name' => \$permission]);
        echo 'Permission créée: ' . \$permission . \"\\n\";
    } else {
        echo 'Permission existe déjà: ' . \$permission . \"\\n\";
    }
}
echo \"\\n✅ Permissions vérifiées/créées\\n\";
"
```

### 5. 🚨 ÉTAPE CRITIQUE : Assigner permissions aux utilisateurs

```bash
php artisan tinker --execute="
// Assigner permissions à TOUS les utilisateurs existants
\$permissions = ['view vehicles', 'create vehicles', 'edit vehicles', 'delete vehicles'];

App\\Models\\User::all()->each(function (\$user) use (\$permissions) {
    try {
        \$user->givePermissionTo(\$permissions);
        echo '✅ Permissions assignées à: ' . \$user->email . \"\\n\";
    } catch (\\Exception \$e) {
        echo '❌ Erreur pour ' . \$user->email . ': ' . \$e->getMessage() . \"\\n\";
    }
});

echo \"\\n🎯 Fini! Tous les utilisateurs ont maintenant les permissions véhicules\\n\";
"
```

### 6. Frontend - Vérifier la configuration

```bash
cd ../frontend/tenant

# Installer les dépendances
npm install

# Vérifier que le fichier .env.local existe
ls -la .env.local

# Si .env.local manque
echo "VITE_API_URL=http://localhost:8000/api" > .env.local
```

### 7. Démarrer les serveurs

```bash
# Terminal 1 : Backend Laravel
cd FLOTTEQ/backend
php artisan serve --port=8000

# Terminal 2 : Frontend React
cd FLOTTEQ/frontend/tenant
npm run dev
```

---

## 🔍 Tests de validation

### Test 1 : Vérifier les permissions

```bash
cd FLOTTEQ/backend
php artisan tinker --execute="
\$user = App\\Models\\User::first();
if (\$user) {
    echo 'Utilisateur: ' . \$user->email . \"\\n\";
    echo 'Permissions: ' . \$user->permissions->pluck('name')->join(', ') . \"\\n\";

    if (\$user->hasPermissionTo('view vehicles')) {
        echo '✅ Permission view vehicles OK' . \"\\n\";
    } else {
        echo '❌ Permission view vehicles MANQUANTE' . \"\\n\";
    }
} else {
    echo '❌ Aucun utilisateur trouvé' . \"\\n\";
}
"
```

### Test 2 : Vérifier l'API véhicules

```bash
# Tester directement l'endpoint avec curl
curl -X GET "http://localhost:8000/api/vehicles" \
  -H "Accept: application/json" \
  -H "X-Tenant-ID: 1" \
  -H "Authorization: Bearer TON_TOKEN_ICI"
```

### Test 3 : Frontend - Console de développement

Ouvrir **F12 > Console** et vérifier :

- ✅ Aucune erreur CORS
- ✅ Header `X-Tenant-ID: 1` présent
- ✅ Réponses 200 pour les requêtes

---

## 🆘 Si ça ne marche toujours pas

### Erreur 500 persistante

```bash
# Vérifier les logs Laravel
cd FLOTTEQ/backend
tail -f storage/logs/laravel.log
```

### Erreur 422 persistante

```bash
# Vérifier exactement les champs envoyés vs attendus
php artisan tinker --execute="
// Voir exactement quels champs sont requis
\$rules = (new App\\Http\\Requests\\VehicleRequest())->rules();
foreach (\$rules as \$field => \$rule) {
    echo \$field . ': ' . (is_array(\$rule) ? implode('|', \$rule) : \$rule) . \"\\n\";
}
"
```

### Base de données corrompue

```bash
# Reset complet (⚠️ PERTE DE DONNÉES)
php artisan migrate:fresh --seed
```

---

## ✅ Une fois que tout marche

1. Se connecter avec un utilisateur existant
2. Aller dans **Véhicules**
3. Tester **Ajouter véhicule**
4. Les champs corrects sont maintenant :
   - **Immatriculation** (pas "plaque")
   - **VIN** (pas "numero_serie")
   - **Carburant** en minuscules : "essence", "diesel", "electrique"
   - **Transmission** requis : "manuelle" ou "automatique"

**Le système devrait maintenant fonctionner sans erreur 500 ni 422 !** 🎉
