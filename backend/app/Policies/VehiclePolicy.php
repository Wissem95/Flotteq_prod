<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Auth\Access\Response;

class VehiclePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Pour les utilisateurs internes, utiliser les rôles traditionnels
        if ($user->isInternal()) {
            return in_array($user->role, ['admin', 'manager', 'support']) || $user->isSuperAdmin();
        }
        
        // Pour les utilisateurs tenants, utiliser Spatie Permission avec fallback sur rôle
        return $user->hasPermissionTo('view vehicles') || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Vehicle $vehicle): bool
    {
        // Pour les utilisateurs internes, pas de restriction tenant
        if ($user->isInternal()) {
            return in_array($user->role, ['admin', 'manager', 'support']) || $user->isSuperAdmin();
        }
        
        // Pour les utilisateurs tenants, vérifier permission + tenant + propriété
        return ($user->hasPermissionTo('view vehicles') || $user->hasRole('admin'))
            && $vehicle->tenant_id === $user->tenant_id
            && $vehicle->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Pour les utilisateurs internes
        if ($user->isInternal()) {
            return in_array($user->role, ['admin', 'manager']) || $user->isSuperAdmin();
        }
        
        // Pour les utilisateurs tenants
        return $user->hasPermissionTo('create vehicles') || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Vehicle $vehicle): bool
    {
        // Pour les utilisateurs internes, pas de restriction tenant
        if ($user->isInternal()) {
            return in_array($user->role, ['admin', 'manager']) || $user->isSuperAdmin();
        }
        
        // Pour les utilisateurs tenants, vérifier permission + tenant + propriété
        return ($user->hasPermissionTo('edit vehicles') || $user->hasRole('admin'))
            && $vehicle->tenant_id === $user->tenant_id
            && $vehicle->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Vehicle $vehicle): bool
    {
        // Pour les utilisateurs internes, pas de restriction tenant
        if ($user->isInternal()) {
            return in_array($user->role, ['admin']) || $user->isSuperAdmin();
        }
        
        // Pour les utilisateurs tenants, vérifier permission + tenant + propriété
        return ($user->hasPermissionTo('delete vehicles') || $user->hasRole('admin'))
            && $vehicle->tenant_id === $user->tenant_id
            && $vehicle->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Vehicle $vehicle): bool
    {
        return $user->hasPermissionTo('delete vehicles')
            && $vehicle->tenant_id === $user->tenant_id
            && $vehicle->user_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Vehicle $vehicle): bool
    {
        return $user->hasPermissionTo('delete vehicles')
            && $vehicle->tenant_id === $user->tenant_id
            && $vehicle->user_id === $user->id;
    }

    /**
     * Determine whether the user can export vehicles.
     */
    public function export(User $user): bool
    {
        return $user->hasPermissionTo('export vehicles');
    }
}
