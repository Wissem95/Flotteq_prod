# 🚂 INSTRUCTIONS RAILWAY - Base de Données FlotteQ

## 🎯 OBJECTIF
Exécuter le script SQL pour corriger les utilisateurs internes dans la base de données Railway.

## 📋 ÉTAPES DÉTAILLÉES

### 1. Configuration Railway CLI

```bash
# Dans le dossier backend
cd backend

# Lier le projet Railway (si pas déjà fait)
railway link

# Vérifier la connexion
railway status
```

### 2. Méthodes pour exécuter le SQL

#### 🥇 MÉTHODE RECOMMANDÉE: Railway Shell

```bash
# 1. Lancer le shell Railway
railway shell

# 2. Se connecter à PostgreSQL
psql $DATABASE_URL

# 3. Exécuter le script SQL
\i fix-internal-users.sql

# 4. Vérifier les résultats
SELECT id, email, username, is_internal, role_interne, tenant_id 
FROM users 
WHERE is_internal = true 
ORDER BY created_at;

# 5. Quitter psql
\q

# 6. Quitter Railway shell
exit
```

#### 🥈 MÉTHODE ALTERNATIVE: Railway Run

```bash
# Exécuter directement le script
railway run psql $DATABASE_URL -f fix-internal-users.sql

# Vérifier les résultats
railway run psql $DATABASE_URL -c "SELECT * FROM users WHERE is_internal = true;"
```

#### 🥉 MÉTHODE MANUELLE: Copier/Coller

Si les méthodes précédentes échouent :

```bash
# 1. Ouvrir une connexion psql
railway shell
psql $DATABASE_URL

# 2. Copier/coller manuellement les commandes SQL:
```

```sql
-- Corriger les utilisateurs internes existants
UPDATE users 
SET is_internal = true 
WHERE email LIKE '%@flotteq.%' 
   OR role_interne IS NOT NULL 
   OR role_interne IN ('admin', 'super_admin', 'support', 'analyst');

-- Créer un utilisateur admin interne si aucun n'existe
INSERT INTO users (
    email, 
    username, 
    first_name, 
    last_name, 
    password, 
    role, 
    is_internal, 
    role_interne, 
    is_active, 
    tenant_id,
    created_at,
    updated_at
) 
SELECT 
    'admin@flotteq.com',
    'admin',
    'Admin',
    'FlotteQ',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    true,
    'super_admin',
    true,
    1,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM users 
    WHERE is_internal = true 
    AND role_interne IN ('admin', 'super_admin')
);

-- Vérifier les résultats
SELECT 
    id,
    email,
    username,
    role,
    is_internal,
    role_interne,
    tenant_id,
    is_active
FROM users 
WHERE is_internal = true 
ORDER BY created_at;
```

## ✅ RÉSULTAT ATTENDU

Après exécution, vous devriez voir :

```
 id |      email       | username | role  | is_internal | role_interne | tenant_id | is_active 
----+------------------+----------+-------+-------------+--------------+-----------+-----------
  X | admin@flotteq.com|   admin  | admin |      t      | super_admin  |     1     |     t
```

## 🔐 INFORMATIONS DE CONNEXION

**Utilisateur admin créé:**
- **Email:** `admin@flotteq.com`
- **Mot de passe:** `password`
- **Rôle:** Super Admin Interne

## 🚨 DÉPANNAGE

### Erreur "railway: command not found"
```bash
npm install -g @railway/cli
railway login
```

### Erreur "No linked project"
```bash
cd backend
railway link
# Sélectionner le bon projet FlotteQ
```

### Erreur de connexion PostgreSQL
```bash
# Vérifier les variables
railway variables

# Tester la connexion
railway run echo $DATABASE_URL
```

## 🎉 VALIDATION FINALE

Une fois le SQL exécuté, tester :

1. **Backend API** : `https://flotteq-backend-v2-production.up.railway.app/api/health`
2. **Login Internal** : `https://internal-rust.vercel.app/login`
   - Email: `admin@flotteq.com`  
   - Password: `password`
3. **Login Tenant** : `https://tenant-black.vercel.app/login`
   - Avec un utilisateur tenant existant

---

## 📞 EN CAS DE PROBLÈME

Si toutes les méthodes échouent :

1. Aller sur dashboard.railway.app
2. Ouvrir le projet FlotteQ
3. Onglet "Database" 
4. Cliquer "Connect"
5. Utiliser l'interface web pour exécuter les requêtes SQL