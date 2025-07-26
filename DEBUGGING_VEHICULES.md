# 🚗 Documentation de Debug - Système de Véhicules Flotteq

## 📋 Vue d'ensemble

Ce document retrace les problèmes rencontrés lors du développement du système de gestion des véhicules de Flotteq et leurs solutions. L'application utilise une architecture Laravel backend + React frontend avec système multitenancy.

---

## 🔴 Problème 1 : Erreurs 500 (Internal Server Error)

### Symptômes

- Toutes les requêtes API retournaient des erreurs 500
- Impossible de récupérer ou créer des véhicules
- Messages d'erreur peu informatifs côté frontend

### Diagnostic

```bash
# Test API direct
curl -X GET http://localhost:8000/api/vehicles \
  -H "Authorization: Bearer TOKEN"
# Retour: 500 Internal Server Error
```

**Cause racine** : Le système multitenancy était activé mais l'en-tête `X-Tenant-ID` n'était pas envoyé par le frontend.

### Architecture multitenancy

```php
// FLOTTEQ/backend/app/TenantFinder/HeaderTenantFinder.php
class HeaderTenantFinder extends TenantFinder
{
    public function findForRequest(Request $request): ?Tenant
    {
        $tenantId = $request->header('X-Tenant-ID'); // ❌ Header manquant
        return Tenant::find($tenantId);
    }
}
```

### Solution appliquée

**Fichier modifié** : `FLOTTEQ/frontend/tenant/src/lib/api.ts`

```typescript
// Ajout de l'intercepteur de requêtes
axios.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  // ✅ SOLUTION: Ajout systématique du tenant ID
  config.headers['X-Tenant-ID'] = '1';

  return config;
});
```

**Résultat** : Les erreurs 500 ont été résolues, les API répondent correctement.

---

## 🔴 Problème 2 : Erreurs 403 (Forbidden)

### Symptômes

- API accessible mais retours 403 "Forbidden"
- Utilisateurs ne pouvaient pas accéder aux véhicules
- Message : "This action is unauthorized"

### Diagnostic

```php
// Test des permissions utilisateur
php artisan tinker
$user = App\Models\User::first();
$user->permissions; // Collection vide ❌
```

**Cause racine** : Le système Spatie Laravel Permission était configuré mais les utilisateurs n'avaient pas les permissions nécessaires.

### Architecture des permissions

```php
// FLOTTEQ/backend/app/Policies/VehiclePolicy.php
public function viewAny(User $user): bool
{
    return $user->can('view vehicles'); // ❌ Permission manquante
}

public function create(User $user): bool
{
    return $user->can('create vehicles');
}

public function update(User $user, Vehicle $vehicle): bool
{
    return $user->can('edit vehicles');
}
```

### Solution temporaire

**Fichier créé** : `FLOTTEQ/backend/app/Console/Commands/AssignUserPermissions.php`

```php
<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Permission;

class AssignUserPermissions extends Command
{
    protected $signature = 'users:assign-permissions';

    public function handle()
    {
        $permissions = [
            'view vehicles',
            'create vehicles',
            'edit vehicles',
            'delete vehicles'
        ];

        // Créer les permissions si elles n'existent pas
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assigner aux 5 premiers utilisateurs
        User::take(5)->get()->each(function ($user) use ($permissions) {
            $user->givePermissionTo($permissions);
            $this->info("Permissions assignées à {$user->email}");
        });
    }
}
```

### Solution permanente

**Fichier créé** : `FLOTTEQ/backend/app/Services/UserPermissionService.php`

```php
<?php
namespace App\Services;

use App\Models\User;
use Spatie\Permission\Models\Permission;

class UserPermissionService
{
    public static function assignDefaultPermissions(User $user): void
    {
        $defaultPermissions = [
            'view vehicles',
            'create vehicles',
            'edit vehicles',
            'delete vehicles',
        ];

        foreach ($defaultPermissions as $permissionName) {
            if (!Permission::where('name', $permissionName)->exists()) {
                Permission::create(['name' => $permissionName]);
            }
        }

        $user->givePermissionTo($defaultPermissions);
    }
}
```

**Intégration dans les contrôleurs** :

```php
// FLOTTEQ/backend/app/Http/Controllers/API/AuthController.php

// Dans register()
$user = User::create($validated);
UserPermissionService::assignDefaultPermissions($user); // ✅

// Dans login()
if (Auth::attempt($credentials)) {
    $user = Auth::user();
    UserPermissionService::assignDefaultPermissions($user); // ✅
    // ...
}
```

**Résultat** : Les permissions sont automatiquement assignées lors de l'inscription/connexion.

---

## 🔴 Problème 3 : Erreurs 422 (Validation Failed)

### Symptômes

- Formulaire de véhicule soumis mais rejeté par le backend
- Messages de validation peu clairs
- Champs requis manquants ou format incorrect

### Diagnostic des incohérences

| Champ        | Frontend       | Backend attendu   | Status |
| ------------ | -------------- | ----------------- | ------ |
| Plaque       | `plaque`       | `immatriculation` | ❌     |
| Numéro série | `numero_serie` | `vin`             | ❌     |
| Carburant    | `"Essence"`    | `"essence"`       | ❌     |
| Transmission | manquant       | requis            | ❌     |

### Solutions appliquées

**1. Fichier** : `FLOTTEQ/frontend/tenant/src/components/vehicles/VehicleForm.tsx`

```tsx
// ❌ AVANT
<input name="plaque" />
<input name="numero_serie" />
<option value="Essence">Essence</option>

// ✅ APRÈS
<input name="immatriculation" />
<input name="vin" />
<option value="essence">Essence</option>

// ✅ AJOUT du champ transmission
<select name="transmission" required>
  <option value="">Sélectionner...</option>
  <option value="manuelle">Manuelle</option>
  <option value="automatique">Automatique</option>
</select>
```

**2. Validation backend harmonisée** :

```php
// FLOTTEQ/backend/app/Http/Controllers/API/VehicleController.php
$validated = $request->validate([
    'marque' => ['required', 'string', 'max:100'],
    'modele' => ['required', 'string', 'max:100'],
    'immatriculation' => ['required', 'string', 'regex:/^[A-Z]{2}-[0-9]{3}-[A-Z]{2}$/'],
    'vin' => ['nullable', 'string', 'max:17'],
    'carburant' => ['required', 'in:essence,diesel,electrique,hybride,gpl'], // ✅ minuscules
    'transmission' => ['required', 'in:manuelle,automatique'], // ✅ ajouté
    // ...
]);
```

**Résultat** : Formulaires acceptés, véhicules créés avec succès.

---

## 🔴 Problème 4 : Véhicules ne s'affichent pas

### Symptômes

- Véhicules créés avec succès mais liste vide
- API retourne des données mais frontend ne les affiche pas
- Console sans erreurs

### Diagnostic

```javascript
// Test API response structure
{
  "current_page": 1,
  "data": [
    { "id": 1, "marque": "Peugeot", ... }  // ✅ Véhicules ici
  ],
  "total": 1
}

// Mais le service extractait mal les données
const { data } = await axios.get('/vehicles');
return data; // ❌ Retournait l'objet paginé, pas les véhicules
```

### Solution

**Fichier** : `FLOTTEQ/frontend/tenant/src/services/vehicleService.ts`

```typescript
// ❌ AVANT
export async function fetchVehicles(): Promise<Vehicle[]> {
  const { data } = await axios.get<Vehicle[]>('/vehicles');
  return data; // Structure incorrecte
}

// ✅ APRÈS
export async function fetchVehicles(): Promise<Vehicle[]> {
  const { data } = await axios.get<{ data: Vehicle[] }>(
    `/vehicles?t=${Date.now()}`,
  );
  return data.data; // ✅ Extraction correcte + cache busting
}

export async function fetchVehicleById(id: string): Promise<Vehicle> {
  const { data } = await axios.get<{ data: Vehicle }>(`/vehicles/${id}`);
  return data.data; // ✅ Cohérence avec l'API
}
```

**Interface unifiée** :

```typescript
// FLOTTEQ/frontend/tenant/src/services/vehicleService.ts
export interface Vehicle {
  id: number;
  immatriculation: string; // ✅ harmonisé
  marque: string;
  modele: string;
  vin?: string | null;
  // ... tous les champs cohérents avec le backend
}
```

**Callbacks de rechargement** :

```tsx
// FLOTTEQ/frontend/tenant/src/components/vehicles/AddVehicleModal.tsx
<AddVehicleModal
  onCreated={() => {
    loadVehicles(); // ✅ Recharge automatique
    setShowAddModal(false);
  }}
/>
```

**Résultat** : Liste des véhicules s'affiche et se met à jour automatiquement.

---

## 🔴 Problème 5 : Modifications ne s'enregistrent pas

### Symptômes

- Formulaire de modification soumis avec succès
- API retourne "Vehicle updated successfully"
- Mais données inchangées en base (`updated_at` identique)

### Diagnostic détaillé

```bash
# Test avec FormData (frontend)
curl -X PUT /vehicles/1 \
  -F "annee=2010" \
  -F "kilometrage=230000"
# Response: 200 OK "Vehicle updated successfully"

# Mais en base:
SELECT annee, kilometrage, updated_at FROM vehicles WHERE id = 1;
# Résultat: 2009 | 12839 | 2025-06-30 23:44:11 (inchangé)

# Test avec JSON
curl -X PUT /vehicles/1 \
  -H "Content-Type: application/json" \
  -d '{"annee": 2010, "kilometrage": 230000}'
# Résultat: Données mises à jour ✅
```

**Cause racine** : Laravel reçoit FormData avec tous les champs en string, mais les validations passent sans conversion.

```php
// FormData reçu:
[
  "annee" => "2009",        // string
  "kilometrage" => "12839"  // string
]

// Laravel validation:
'annee' => ['sometimes', 'integer', 'min:1900']
// ✅ "2009" passe la validation integer (coercion)
// ❌ Mais Eloquent n'update pas si la valeur est identique
```

### Solution

**Fichier** : `FLOTTEQ/frontend/tenant/src/services/vehicleService.ts`

```typescript
export async function updateVehicle(
  id: number,
  payload: FormData,
): Promise<Vehicle> {
  // ✅ CONVERSION FormData → JSON avec types corrects
  const jsonData: any = {};
  for (let [key, value] of payload.entries()) {
    if (key === 'photos' || key === 'documents') continue;

    // ✅ Conversion des champs numériques
    if (['annee', 'kilometrage', 'puissance', 'purchase_price'].includes(key)) {
      jsonData[key] = value ? parseInt(value as string, 10) : null;
    } else if (key === 'purchase_date') {
      jsonData[key] = value || null;
    } else {
      jsonData[key] = value;
    }
  }

  console.log('🔧 Données JSON converties pour update :', jsonData);

  const { data } = await axios.put<{ message: string; vehicle: Vehicle }>(
    `/vehicles/${id}`,
    jsonData, // ✅ JSON au lieu de FormData
    { headers: { 'Content-Type': 'application/json' } },
  );

  return data.vehicle;
}
```

**Anti-cache ajouté** :

```typescript
export async function fetchVehicles(): Promise<Vehicle[]> {
  const { data } = await axios.get<{ data: Vehicle[] }>(
    `/vehicles?t=${Date.now()}`,
  );
  return data.data; // ✅ Timestamp empêche le cache
}
```

**Résultat** : Modifications s'enregistrent correctement avec types appropriés.

---

## 🔴 Problème 6 : Changement de statut non fonctionnel

### Symptômes

- Dropdown de statut ne répond pas
- Erreurs lors du changement de statut
- Valeurs de statut incohérentes

### Diagnostic des incohérences

| Couche         | Valeurs de statut                                         |
| -------------- | --------------------------------------------------------- |
| Migration DB   | `active, sold, under_repair, out_of_service`              |
| Contrôleur API | `active,vendu,en_reparation,hors_service`                 |
| Frontend       | `active, maintenance, en_reparation, hors_service, vendu` |

**Test d'erreur** :

```bash
php artisan tinker
$vehicle = App\Models\Vehicle::find(1);
$vehicle->update(['status' => 'maintenance']);
# SQLSTATE[23000]: CHECK constraint failed: status
```

### Solution

**1. Migration harmonisée** :

```php
// FLOTTEQ/backend/database/migrations/2025_06_12_112453_create_vehicles_table.php
$table->enum('status', [
    'active',
    'vendu',
    'en_reparation',
    'en_maintenance',  // ✅ ajouté
    'hors_service'
])->default('active');

// ✅ Ajout des colonnes CT
$table->date('last_ct_date')->nullable();
$table->date('next_ct_date')->nullable();
```

**2. Contrôleur mis à jour** :

```php
// FLOTTEQ/backend/app/Http/Controllers/API/VehicleController.php
'status' => ['sometimes', 'in:active,vendu,en_reparation,en_maintenance,hors_service'],
'last_ct_date' => ['nullable', 'date'],
'next_ct_date' => ['nullable', 'date'],
```

**3. Frontend StatusDropdown** :

```tsx
// FLOTTEQ/frontend/tenant/src/components/vehicles/StatusDropdown.tsx
const statusOptions = [
  {
    value: 'active',
    label: 'En service',
    className: 'bg-green-100 text-green-700',
  },
  {
    value: 'en_maintenance',
    label: 'En maintenance',
    className: 'bg-amber-100 text-amber-700',
  },
  {
    value: 'en_reparation',
    label: 'En réparation',
    className: 'bg-orange-100 text-orange-700',
  },
  {
    value: 'hors_service',
    label: 'Hors service',
    className: 'bg-red-100 text-red-700',
  },
  { value: 'vendu', label: 'Vendu', className: 'bg-gray-100 text-gray-700' },
];
```

**4. Service API ajouté** :

```typescript
export async function updateVehicleStatus(
  id: number,
  status: string,
): Promise<Vehicle> {
  const { data } = await axios.put<{ message: string; vehicle: Vehicle }>(
    `/vehicles/${id}`,
    { status },
    { headers: { 'Content-Type': 'application/json' } },
  );
  return data.vehicle;
}
```

**Résultat** : Changement de statut fonctionnel avec mise à jour en temps réel.

---

## ⚠️ Problème 7 : Perte de données (migrate:fresh)

### Symptômes

- Utilisateur signale "plus de véhicules"
- Base de données vide après modifications

### Cause

Utilisation de `php artisan migrate:fresh --seed` au lieu de migrations incrémentales.

```bash
# ❌ COMMANDE DESTRUCTIVE
php artisan migrate:fresh --seed
# = DROP ALL TABLES + Recreate + Seed

# ✅ ALTERNATIVE SÛRE
php artisan make:migration add_ct_dates_to_vehicles_table
php artisan migrate
```

### Prévention future

**Bonnes pratiques** :

- ✅ Toujours sauvegarder avant `migrate:fresh`
- ✅ Utiliser des migrations incrémentales en production
- ✅ Tester d'abord sur une copie de la base

**Migration de récupération** :

```php
// Si données perdues, créer script de récupération
Schema::table('vehicles', function (Blueprint $table) {
    $table->date('last_ct_date')->nullable();
    $table->date('next_ct_date')->nullable();
    $table->dropColumn('some_old_column');
});
```

---

## 📊 Résumé des améliorations

### Avant les corrections

- ❌ Erreurs 500 sur toutes les API
- ❌ Permissions non assignées
- ❌ Formulaires rejetés (422)
- ❌ Véhicules invisibles
- ❌ Modifications non persistées
- ❌ Statuts incohérents

### Après les corrections

- ✅ API fonctionnelles avec multitenancy
- ✅ Permissions automatiques à l'inscription
- ✅ Formulaires validés et harmonisés
- ✅ Liste de véhicules dynamique
- ✅ Modifications persisted en JSON
- ✅ Changement de statut en temps réel
- ✅ Interface enrichie (VIN, couleur, prix, dates CT)

### Architecture finale

```
Frontend (React + TypeScript)
├── Services API avec axios interceptors
├── Composants modulaires (StatusDropdown, VehicleForm)
├── Gestion d'état avec callbacks
└── Validation côté client

Backend (Laravel + Spatie)
├── Multitenancy avec HeaderTenantFinder
├── Permissions automatiques via UserPermissionService
├── Validation harmonisée
├── Policies pour autorisation
└── API Resources structurées

Base de données
├── Schema cohérent (statuts, colonnes CT)
├── Relations utilisateur/tenant/véhicule
├── Index de performance
└── Contraintes de validation
```

---

## 🔧 Commandes utiles

### Debugging

```bash
# Vérifier les véhicules en base
php artisan tinker --execute="echo App\Models\Vehicle::count() . ' véhicules'"

# Tester les permissions
php artisan tinker --execute="App\Models\User::first()->permissions->pluck('name')"

# Test API direct
curl -X GET "http://localhost:8000/api/vehicles" \
  -H "X-Tenant-ID: 1" \
  -H "Authorization: Bearer TOKEN"
```

### Récupération de données

```bash
# Si besoin de recréer des véhicules de test
php artisan db:seed --class=VehicleSeeder

# Assigner permissions manuellement
php artisan users:assign-permissions
```

### Développement

```bash
# Migration sûre
php artisan make:migration add_field_to_table --table=vehicles
php artisan migrate

# Rollback si problème
php artisan migrate:rollback --step=1
```

---

**Date de création** : 2025-06-30  
**Version** : Laravel 11 + React 18  
**Status** : ✅ Résolu et documenté
