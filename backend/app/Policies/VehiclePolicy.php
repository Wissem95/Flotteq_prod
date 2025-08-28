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
        
        // Pour les utilisateurs normaux (tenants), ils peuvent voir leurs propres véhicules
        return true;
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
        
        // Pour les utilisateurs normaux, ils peuvent voir leurs propres véhicules
        return $vehicle->tenant_id === $user->tenant_id
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
        
        // Pour les utilisateurs normaux, ils peuvent créer des véhicules
        return true;
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
        
        // Pour les utilisateurs normaux, ils peuvent modifier leurs propres véhicules
        return $vehicle->tenant_id === $user->tenant_id
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
        
        // TEMPORAIRE: Fallback sur rôles natifs + vérification tenant/propriété
        return in_array($user->role, ['admin'])
            && $vehicle->tenant_id === $user->tenant_id
            && $vehicle->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Vehicle $vehicle): bool
    {
        // TEMPORAIRE: Fallback sur rôles natifs
        return in_array($user->role, ['admin'])
            && $vehicle->tenant_id === $user->tenant_id
            && $vehicle->user_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Vehicle $vehicle): bool
    {
        // TEMPORAIRE: Fallback sur rôles natifs
        return in_array($user->role, ['admin'])
            && $vehicle->tenant_id === $user->tenant_id
            && $vehicle->user_id === $user->id;
    }

    /**
     * Determine whether the user can export vehicles.
     */
    public function export(User $user): bool
    {
        // TEMPORAIRE: Fallback sur rôles natifs
        return in_array($user->role, ['admin', 'manager']);
    }
}
