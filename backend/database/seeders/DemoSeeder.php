<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo tenant
        $tenant = Tenant::create([
            'name' => 'Demo Company',
            'domain' => 'demo.flotteq.local',
            'database' => 'flotteq_demo',
            'is_active' => true,
        ]);

        // Create admin user
        $admin = User::create([
            'email' => 'admin@demo.com',
            'username' => 'admin',
            'first_name' => 'Admin',
            'last_name' => 'Demo',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'tenant_id' => $tenant->id,
            'is_active' => true,
        ]);

        // Create regular user
        $user = User::create([
            'email' => 'user@demo.com',
            'username' => 'user',
            'first_name' => 'User',
            'last_name' => 'Demo',
            'password' => Hash::make('password'),
            'role' => 'user',
            'tenant_id' => $tenant->id,
            'is_active' => true,
        ]);

        // Create demo vehicles
        Vehicle::create([
            'user_id' => $admin->id,
            'tenant_id' => $tenant->id,
            'marque' => 'Peugeot',
            'modele' => '308',
            'immatriculation' => 'AB-123-CD',
            'vin' => 'VF3LCBHZXGS000001',
            'annee' => 2020,
            'couleur' => 'Bleu',
            'kilometrage' => 45000,
            'carburant' => 'diesel',
            'transmission' => 'manuelle',
            'puissance' => 130,
            'purchase_date' => '2020-03-15',
            'purchase_price' => 18500.00,
            'status' => 'active',
            'notes' => 'Véhicule de direction',
        ]);

        Vehicle::create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'marque' => 'Renault',
            'modele' => 'Clio',
            'immatriculation' => 'EF-456-GH',
            'vin' => 'VF1RBHZXGS000002',
            'annee' => 2019,
            'couleur' => 'Rouge',
            'kilometrage' => 62000,
            'carburant' => 'essence',
            'transmission' => 'manuelle',
            'puissance' => 90,
            'purchase_date' => '2019-06-20',
            'purchase_price' => 14000.00,
            'status' => 'active',
            'notes' => 'Véhicule commercial',
        ]);

        $this->command->info('Demo data created successfully!');
        $this->command->info('Tenant: ' . $tenant->domain);
        $this->command->info('Admin: admin@demo.com / password');
        $this->command->info('User: user@demo.com / password');
    }
}
