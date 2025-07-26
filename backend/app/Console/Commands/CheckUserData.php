<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CheckUserData extends Command
{
    protected $signature = 'check:user-data {user_id=2}';
    protected $description = 'Vérifier les données utilisateur pour diagnostiquer le problème Google OAuth';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("Utilisateur {$userId} non trouvé.");
            return;
        }

        $this->info("=== DONNÉES UTILISATEUR ID {$userId} ===");
        $this->info("Email: {$user->email}");
        $this->info("Username: {$user->username}");
        $this->info("Prénom: " . ($user->first_name ?? 'VIDE'));
        $this->info("Nom: " . ($user->last_name ?? 'VIDE'));
        $this->info("Google ID: " . ($user->google_id ?? 'VIDE'));
        $this->info("Avatar: " . ($user->avatar ?? 'VIDE'));
        $this->info("Téléphone: " . ($user->phone ?? 'VIDE'));
        $this->info("Date de naissance: " . ($user->birthdate ?? 'VIDE'));
        $this->info("Genre: " . ($user->gender ?? 'VIDE'));
        $this->info("Adresse: " . ($user->address ?? 'VIDE'));
        $this->info("Code postal: " . ($user->postalCode ?? 'VIDE'));
        $this->info("Ville: " . ($user->city ?? 'VIDE'));
        $this->info("Pays: " . ($user->country ?? 'VIDE'));

        $this->info("=== STATUT PROFIL ===");
        $this->info("Profil incomplet: " . ($user->hasIncompleteProfile() ? 'OUI' : 'NON'));
        $missingFields = $user->getMissingProfileFields();
        if (count($missingFields) > 0) {
            $this->info("Champs manquants: " . implode(', ', array_values($missingFields)));
        } else {
            $this->info("Aucun champ manquant");
        }

        $this->info("=== DATES ===");
        $this->info("Créé le: {$user->created_at}");
        $this->info("Modifié le: {$user->updated_at}");
    }
}
