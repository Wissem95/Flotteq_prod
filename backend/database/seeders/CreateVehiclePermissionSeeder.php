<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class CreateVehiclePermissionSeeder extends Seeder
{
    public function run()
    {
        $permission = Permission::findOrCreate('create vehicles', 'web');
        $user = User::find(4); // ID changé à 4
        if ($user) {
            $user->givePermissionTo($permission);
        }
    }
}