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
        return $user->hasPermissionTo('view vehicles');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Vehicle $vehicle): bool
    {
        // User must have permission and vehicle must belong to same tenant and same user
        return $user->hasPermissionTo('view vehicles')
            && $vehicle->tenant_id === $user->tenant_id
            && $vehicle->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create vehicles');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Vehicle $vehicle): bool
    {
        // User must have permission and vehicle must belong to same tenant and same user
        return $user->hasPermissionTo('edit vehicles')
            && $vehicle->tenant_id === $user->tenant_id
            && $vehicle->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Vehicle $vehicle): bool
    {
        // User must have permission and vehicle must belong to same tenant and same user
        return $user->hasPermissionTo('delete vehicles')
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
