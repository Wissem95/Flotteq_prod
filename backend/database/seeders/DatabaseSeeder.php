<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seeder de production - uniquement les permissions et rôles de base
        $this->call(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->call(\Database\Seeders\CleanProductionSeeder::class);
    }
}
