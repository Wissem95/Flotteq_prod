<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateTestUser extends Command
{
    protected $signature = 'user:create-test';
    protected $description = 'Create a test user for authentication testing';

    public function handle()
    {
        $user = User::where('email', 'demo@flotteq.com')->first();
        
        if (!$user) {
            $user = User::create([
                'email' => 'demo@flotteq.com',
                'username' => 'demo',
                'first_name' => 'Test',
                'last_name' => 'User',
                'password' => Hash::make('password123'),
                'tenant_id' => 1,
                'role' => 'user',
                'is_active' => true,
            ]);
            $this->info('Utilisateur test créé: ' . $user->email);
        } else {
            // Mettre à jour l'utilisateur existant si nécessaire
            if (!$user->username) {
                $user->update(['username' => 'demo']);
                $this->info('Username ajouté à l\'utilisateur existant');
            }
            $this->info('Utilisateur test existe déjà: ' . $user->email);
        }
        
        $this->info('ID: ' . $user->id . ', Tenant: ' . $user->tenant_id);
        
        // Assigner des permissions
        if (class_exists(\Spatie\Permission\Models\Permission::class)) {
            $permissions = ['view vehicles', 'create vehicles', 'edit vehicles', 'delete vehicles'];
            foreach ($permissions as $permission) {
                if (!\Spatie\Permission\Models\Permission::where('name', $permission)->exists()) {
                    \Spatie\Permission\Models\Permission::create(['name' => $permission]);
                }
            }
            $user->givePermissionTo($permissions);
            $this->info('Permissions assignées à l\'utilisateur');
        }
        
        return 0;
    }
}
