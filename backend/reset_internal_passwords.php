<?php

/**
 * Script pour créer ou réinitialiser les utilisateurs internes
 * Exécuter avec: php reset_internal_passwords.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

echo "=== Réinitialisation des utilisateurs internes ===\n\n";

$newPassword = 'Admin@2024!';
$hashedPassword = Hash::make($newPassword);

try {
    DB::beginTransaction();
    
    // Liste des utilisateurs internes à créer/réinitialiser
    $internalUsers = [
        [
            'email' => 'admin@flotteq.fr',
            'username' => 'admin.flotteq',
            'first_name' => 'Admin',
            'last_name' => 'FlotteQ',
            'role' => 'admin',
            'is_internal' => true,
            'role_interne' => 'super_admin',
            'phone' => '01 00 00 00 00',
        ],
        [
            'email' => 'support@flotteq.fr',
            'username' => 'support.flotteq',
            'first_name' => 'Support',
            'last_name' => 'FlotteQ',
            'role' => 'support',
            'is_internal' => true,
            'role_interne' => 'support',
            'phone' => '01 00 00 00 01',
        ],
        [
            'email' => 'commercial@flotteq.fr',
            'username' => 'commercial.flotteq',
            'first_name' => 'Commercial',
            'last_name' => 'FlotteQ',
            'role' => 'commercial',
            'is_internal' => true,
            'role_interne' => 'commercial',
            'phone' => '01 00 00 00 02',
        ],
    ];
    
    foreach ($internalUsers as $userData) {
        $user = User::where('email', $userData['email'])->first();
        
        if ($user) {
            // Mettre à jour l'utilisateur existant
            $user->update([
                'password' => $hashedPassword,
                'username' => $userData['username'],
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'role' => $userData['role'],
                'is_internal' => $userData['is_internal'],
                'role_interne' => $userData['role_interne'],
                'phone' => $userData['phone'],
                'is_active' => true,
            ]);
            echo "✅ Mot de passe réinitialisé pour: {$userData['email']}\n";
        } else {
            // Créer un nouvel utilisateur
            User::create([
                'email' => $userData['email'],
                'username' => $userData['username'],
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'password' => $hashedPassword,
                'role' => $userData['role'],
                'is_internal' => $userData['is_internal'],
                'role_interne' => $userData['role_interne'],
                'phone' => $userData['phone'],
                'is_active' => true,
                'email_verified_at' => now(),
                'birthdate' => '1990-01-01',
                'gender' => 'other',
                'address' => 'FlotteQ HQ',
                'city' => 'Paris',
                'country' => 'France',
            ]);
            echo "✅ Utilisateur créé: {$userData['email']}\n";
        }
    }
    
    DB::commit();
    
    echo "\n=== Réinitialisation terminée avec succès ===\n";
    echo "\n📧 Comptes internes:\n";
    foreach ($internalUsers as $userData) {
        echo "   - Email: {$userData['email']}\n";
    }
    echo "\n🔑 Nouveau mot de passe pour tous: $newPassword\n";
    echo "\n⚠️  IMPORTANT: Changez ce mot de passe après la première connexion!\n";
    
} catch (\Exception $e) {
    DB::rollback();
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}