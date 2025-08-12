<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Tenant;
use App\Services\UserPermissionService;

class CleanProductionSeeder extends Seeder
{
    /**
     * Seeder de production FlotteQ - données essentielles uniquement
     * 
     * Ce seeder créé uniquement les données strictement nécessaires
     * au fonctionnement de l'application en production :
     * - Tenant par défaut FlotteQ
     * - Utilisateur administrateur système
     * - Permissions de base
     */
    public function run(): void
    {
        // Créer le tenant FlotteQ (société mère)
        $flotteqTenant = Tenant::firstOrCreate(
            ['domain' => 'flotteq.com'],
            [
                'name' => 'FlotteQ',
                'database' => 'flotteq_main',
                'is_active' => true,
            ]
        );

        // Créer un utilisateur administrateur système pour FlotteQ
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@flotteq.com'],
            [
                'username' => 'admin_flotteq',
                'first_name' => 'Administrateur',
                'last_name' => 'FlotteQ',
                'password' => Hash::make('ChangeThis2024!'),
                'role' => 'admin',
                'is_internal' => true,
                'role_interne' => 'super_admin',
                'tenant_id' => $flotteqTenant->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Assigner les permissions par défaut
        try {
            UserPermissionService::assignDefaultPermissions($adminUser);
            $this->command->info('✅ Permissions assignées à l\'administrateur');
        } catch (\Exception $e) {
            $this->command->warn('⚠️  Erreur lors de l\'assignation des permissions: ' . $e->getMessage());
        }

        $this->command->info('');
        $this->command->info('🚀 FlotteQ - Données de production créées avec succès !');
        $this->command->info('');
        $this->command->info('📋 INFORMATIONS DE CONNEXION :');
        $this->command->info('   👤 Email administrateur : admin@flotteq.com');
        $this->command->info('   🔑 Mot de passe temporaire : ChangeThis2024!');
        $this->command->info('   🏢 Tenant : ' . $flotteqTenant->domain);
        $this->command->info('');
        $this->command->info('⚠️  IMPORTANT : Changez le mot de passe immédiatement !');
        $this->command->info('');
    }
}