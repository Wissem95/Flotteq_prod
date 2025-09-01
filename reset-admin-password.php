<?php

// Script de réinitialisation du password admin
// À exécuter sur Railway : railway run php reset-admin-password.php

require_once __DIR__ . '/backend/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/backend/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Trouver l'utilisateur admin
    $user = \App\Models\User::where('email', 'admin@flotteq.com')->first();
    
    if (!$user) {
        echo "❌ Utilisateur admin@flotteq.com non trouvé!\n";
        exit(1);
    }
    
    // Nouveau password
    $newPassword = 'FlotteQ2024!Admin';
    
    // Réinitialiser le password
    $user->password = $newPassword; // Le mutator se charge du hashing
    $user->save();
    
    echo "✅ Password admin réinitialisé avec succès!\n";
    echo "📧 Email: admin@flotteq.com\n";
    echo "🔑 Password: $newPassword\n";
    echo "\n";
    echo "🧪 Test du login:\n";
    echo "curl -X POST https://flotteq-backend-v2-production.up.railway.app/api/internal/auth/login \\\n";
    echo "  -H 'Content-Type: application/json' \\\n";
    echo "  -d '{\"email\":\"admin@flotteq.com\",\"password\":\"$newPassword\"}'\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}