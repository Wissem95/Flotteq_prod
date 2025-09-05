<?php

// Script de réinitialisation du password admin
// À exécuter sur Railway : railway run php reset-admin-password.php

require_once __DIR__ . '/backend/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/backend/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    echo "=== Recherche des utilisateurs admin existants ===\n";
    
    // Chercher tous les utilisateurs potentiels
    $adminEmails = ['admin@flotteq.fr', 'admin@flotteq.com', 'wissemkarboubbb@gmail.com'];
    $adminFound = false;
    
    foreach ($adminEmails as $email) {
        $user = User::where('email', $email)->first();
        if ($user) {
            echo "✅ Utilisateur trouvé: $email\n";
            
            // Nouveau password
            $newPassword = 'Admin@2024!';
            
            // Réinitialiser le password avec Hash::make
            $user->password = Hash::make($newPassword);
            $user->is_internal = true;
            $user->is_active = true;
            $user->save();
            
            echo "✅ Password réinitialisé avec succès!\n";
            echo "📧 Email: $email\n";
            echo "🔑 Password: $newPassword\n\n";
            $adminFound = true;
        }
    }
    
    // Si aucun admin trouvé, créer un nouvel utilisateur
    if (!$adminFound) {
        echo "⚠️ Aucun utilisateur admin trouvé, création d'un nouvel admin...\n";
        
        $newPassword = 'Admin@2024!';
        
        $user = User::create([
            'email' => 'admin@flotteq.fr',
            'username' => 'admin.flotteq',
            'first_name' => 'Admin',
            'last_name' => 'FlotteQ',
            'password' => Hash::make($newPassword),
            'role' => 'admin',
            'is_internal' => true,
            'role_interne' => 'super_admin',
            'is_active' => true,
            'email_verified_at' => now(),
            'birthdate' => '1990-01-01',
            'gender' => 'other',
            'address' => 'FlotteQ HQ',
            'city' => 'Paris',
            'country' => 'France',
            'phone' => '+33100000000',
        ]);
        
        echo "✅ Nouvel utilisateur admin créé!\n";
        echo "📧 Email: admin@flotteq.fr\n";
        echo "🔑 Password: $newPassword\n\n";
    }
    
    echo "=== Informations de connexion ===\n";
    echo "🔑 Mot de passe pour tous les comptes admin: Admin@2024!\n";
    echo "🌐 URL de connexion: https://internal-rust.vercel.app\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}