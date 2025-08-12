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
        // SEEDER DÉSACTIVÉ EN PRODUCTION
        // Ce seeder contenait des données de démonstration qui ne doivent pas
        // être utilisées en environnement de production.
        
        $this->command->warn('DemoSeeder désactivé en production. Utilisez ProductionDataSeeder à la place.');
        return;
        
        // Ancien code commenté pour référence historique
        /*
        $tenant = Tenant::create([
            'name' => 'Demo Company',
            'domain' => 'demo.flotteq.local',
            'database' => 'flotteq_demo',
            'is_active' => true,
        ]);
        
        // ... reste du code démo commenté
        */
    }
}
