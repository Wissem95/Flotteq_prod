<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class InternalAdminSeeder extends Seeder
{
    /**
     * Seed internal admin users for FlotteQ administration
     */
    public function run(): void
    {
        $this->command->info('🔧 Création des utilisateurs administrateurs internes...');

        // Ensure we're not in a tenant context
        DB::purge('tenant');

        $adminUsers = [
            [
                'first_name' => 'Super',
                'last_name' => 'Administrateur',
                'email' => 'admin@flotteq.com',
                'username' => 'superadmin',
                'password' => Hash::make('demo123'),
                'role' => 'admin', // Utiliser les rôles existants
                'role_interne' => 'super_admin',
                'is_internal' => true,
                'is_active' => true,
                'tenant_id' => null, // Pas de tenant pour les internes
                'email_verified_at' => now(),
            ],
            [
                'first_name' => 'Admin',
                'last_name' => 'Technique',
                'email' => 'admin.tech@flotteq.com',
                'username' => 'admin.tech',
                'password' => Hash::make('Admin2024!'),
                'role' => 'admin',
                'role_interne' => 'admin',
                'is_internal' => true,
                'is_active' => true,
                'tenant_id' => null,
                'email_verified_at' => now(),
            ],
            [
                'first_name' => 'Agent',
                'last_name' => 'Support',
                'email' => 'support@flotteq.com',
                'username' => 'support',
                'password' => Hash::make('Support2024!'),
                'role' => 'user',
                'role_interne' => 'support',
                'is_internal' => true,
                'is_active' => true,
                'tenant_id' => null,
                'email_verified_at' => now(),
            ],
            [
                'first_name' => 'Gestionnaire',
                'last_name' => 'Partenaires',
                'email' => 'partners@flotteq.com',
                'username' => 'partners',
                'password' => Hash::make('Partners2024!'),
                'role' => 'manager',
                'role_interne' => 'partner_manager',
                'is_internal' => true,
                'is_active' => true,
                'tenant_id' => null,
                'email_verified_at' => now(),
            ],
            [
                'first_name' => 'Analyste',
                'last_name' => 'Business',
                'email' => 'analyst@flotteq.com',
                'username' => 'analyst',
                'password' => Hash::make('Analyst2024!'),
                'role' => 'user',
                'role_interne' => 'analyst',
                'is_internal' => true,
                'is_active' => true,
                'tenant_id' => null,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($adminUsers as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            if ($user->wasRecentlyCreated) {
                $this->command->info("✅ Utilisateur créé: {$userData['email']}");
            } else {
                $this->command->warn("⚠️  Utilisateur existe déjà: {$userData['email']}");
            }
        }

        $this->command->info('');
        $this->command->info('🎯 IDENTIFIANTS DE CONNEXION INTERFACE INTERNAL:');
        $this->command->info('');
        
        $credentialsTable = [
            ['Email', 'Mot de passe', 'Rôle'],
            ['admin@flotteq.com', 'demo123', 'Super Admin'],
            ['admin.tech@flotteq.com', 'Admin2024!', 'Admin Technique'],
            ['support@flotteq.com', 'Support2024!', 'Support Client'],
            ['partners@flotteq.com', 'Partners2024!', 'Gestionnaire Partenaires'],
            ['analyst@flotteq.com', 'Analyst2024!', 'Analyste Business'],
        ];

        $this->command->table($credentialsTable[0], array_slice($credentialsTable, 1));
        
        $this->command->info('');
        $this->command->info('🌐 Interface Internal: http://localhost:8080/');
        $this->command->info('🔑 Utilisez ces identifiants pour vous connecter à l\'interface d\'administration');
        $this->command->info('');
    }
}