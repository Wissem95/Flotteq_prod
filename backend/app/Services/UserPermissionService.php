<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Log;

class UserPermissionService
{
    /**
     * Assigner les permissions par défaut à un utilisateur.
     */
    public static function assignDefaultPermissions(User $user): void
    {
        // Permissions de base pour tous les utilisateurs
        $defaultPermissions = [
            'view vehicles',
            'create vehicles',
            'edit vehicles',
            'delete vehicles',
        ];

        // Vérifier d'abord si des permissions existent dans la base de données
        $totalPermissions = Permission::where('guard_name', 'sanctum')->count();
        if ($totalPermissions === 0) {
            Log::info("Aucune permission trouvée dans la base de données. Ignoré pour l'utilisateur {$user->id}");
            return;
        }

        // Vérifier et assigner seulement les permissions qui existent et ne sont pas déjà assignées
        foreach ($defaultPermissions as $permissionName) {
            // Utiliser le guard sanctum pour les permissions API
            try {
                $permission = Permission::where('name', $permissionName)->where('guard_name', 'sanctum')->first();
                if ($permission && !$user->hasPermissionTo($permissionName)) {
                    $user->givePermissionTo($permission);
                }
            } catch (\Exception $e) {
                // Log l'erreur mais continue - les permissions peuvent être assignées plus tard
                Log::warning("Impossible d'assigner la permission '{$permissionName}' à l'utilisateur {$user->id}: " . $e->getMessage(), ['tenantId' => $user->tenant_id]);
            }
        }
    }
}
