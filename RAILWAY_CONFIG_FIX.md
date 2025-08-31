# 🔧 RAILWAY CONFIG FIX - FlotteQ Backend

**Problème identifié**: Routes POST et endpoints avec middleware d'authentification redirigent vers la racine

---

## 🎯 DIAGNOSTIC PRÉCIS

### Symptoms Observés:
```bash
✅ GET /api/health → 200 OK (fonctionne)
❌ POST /api/auth/login → Redirect 302 vers racine  
❌ GET /api/internal/tenants → 500/timeout
❌ GET /api/internal/tenant-users → Parfois 0.02ms, parfois 500ms
```

### Cause Probable:
Railway utilise probablement une configuration Apache/Nginx qui:
1. Redirige certaines routes POST vers la racine
2. Cause des timeouts sur les endpoints avec middlewares d'auth
3. N'applique pas correctement la configuration Laravel

---

## ⚙️ SOLUTIONS RECOMMANDÉES

### 1. Vérifier le fichier `.htaccess`

Créer ou mettre à jour `/public/.htaccess`:
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### 2. Configurer Railway Variables d'Environnement

Dans Railway Dashboard → Variables:
```env
APP_URL=https://flotteq-backend-v2-production.up.railway.app
APP_ENV=production
APP_DEBUG=false

# S'assurer que ces variables sont définies
DB_CONNECTION=pgsql
DB_HOST=aws-0-eu-west-3.pooler.supabase.com
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=postgres.itaujufkikojwqyrjipx
DB_PASSWORD=Wissem2002.
```

### 3. Vérifier Dockerfile/Railway Config

Si un `Dockerfile` existe, s'assurer qu'il expose correctement le port:
```dockerfile
EXPOSE 8080
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
```

Ou vérifier `railway.toml`:
```toml
[build]
builder = "nixpacks"

[deploy]
startCommand = "php artisan serve --host=0.0.0.0 --port=$PORT"

[[services]]
name = "backend"
```

### 4. Configurer CORS pour les requêtes POST

Dans `config/cors.php`:
```php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'], // Ou spécifier les domaines frontend
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

### 5. Middleware Route Configuration

Vérifier `app/Http/Kernel.php` pour les groupes de middleware API:
```php
protected $middlewareGroups = [
    'api' => [
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        'throttle:api',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],
];
```

---

## 🔄 COMMANDES À EXÉCUTER

### Sur Railway (via CLI):
```bash
# 1. Reconstruire l'application
railway up --force

# 2. Vérifier les logs après redéploiement  
railway logs

# 3. Tester les routes après fix
curl -X POST https://flotteq-backend-v2-production.up.railway.app/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@flotteq.com","password":"password"}'
```

### Cache clearing après fix:
```bash
railway run php artisan config:clear
railway run php artisan route:clear
railway run php artisan cache:clear
railway run php artisan config:cache
railway run php artisan route:cache
```

---

## 🧪 TESTS DE VALIDATION POST-FIX

### 1. Test Routes POST:
```bash
# Auth login (doit retourner JSON, pas redirect)
curl -X POST /api/auth/login -H "Content-Type: application/json" -d '{"email":"test","password":"test"}'

# Internal auth login  
curl -X POST /api/internal/auth/login -H "Content-Type: application/json" -d '{"email":"admin@flotteq.com","password":"password"}'
```

### 2. Test Endpoints Protégés:
```bash  
# Avec token d'authentification (après login réussi)
curl -H "Authorization: Bearer TOKEN" /api/internal/tenant-users
curl -H "Authorization: Bearer TOKEN" /api/internal/tenants
```

### 3. Test Multi-Tenancy:
```bash
# Avec X-Tenant-ID header
curl -H "X-Tenant-ID: 1" -H "Authorization: Bearer TOKEN" /api/users
curl -H "X-Tenant-ID: 1" -H "Authorization: Bearer TOKEN" /api/vehicles
```

---

## 🎯 RÉSULTATS ATTENDUS APRÈS FIX

### Comportements Corrigés:
```diff
- POST /api/auth/login → Redirect 302
+ POST /api/auth/login → JSON response (401 ou 200)

- GET /api/internal/tenants → 500 timeout  
+ GET /api/internal/tenants → 401 sans token, 200 avec token

- GET /api/internal/tenant-users → Inconsistant
+ GET /api/internal/tenant-users → Réponse stable en <50ms
```

### Validation TenantUsersOverview:
Une fois les routes fixées, notre nouveau contrôleur devrait répondre:
```json
{
  "stats": {
    "total_users": 3,
    "active_users": 3,
    "inactive_users": 0,
    "tenants_with_users": 1
  },
  "data": [
    // Liste des utilisateurs des tenants
  ]
}
```

---

## 🚨 ACTIONS URGENTES

1. **Immédiat**: Vérifier `.htaccess` et variables d'environnement Railway
2. **Priorité 1**: Tester POST routes après fix
3. **Priorité 2**: Valider authentification avec tokens  
4. **Final**: Tests complets TenantUsersOverview

Une fois ces fixes appliqués, le système FlotteQ sera 100% opérationnel en production avec toutes les nouvelles fonctionnalités accessibles.

---

*Guide technique Railway Config Fix - 31/08/2025*