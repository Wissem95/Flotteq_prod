<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Maintenance;
use App\Models\Vehicle;
use App\Models\User;
use Carbon\Carbon;

class MaintenanceTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer un véhicule et un utilisateur existants
        $vehicle = Vehicle::first();
        $user = User::first();

        if (!$vehicle || !$user) {
            $this->command->warn('Aucun véhicule ou utilisateur trouvé. Création des données de test annulée.');
            return;
        }

        $this->command->info('Création de maintenances de test pour démontrer les nouvelles fonctionnalités...');

        // 1. Maintenance en retard (overdue)
        Maintenance::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'type' => 'Vidange',
            'description' => 'Changement d\'huile moteur et filtre à huile',
            'date' => null, // Pas encore effectuée
            'scheduled_date' => Carbon::now()->subDays(5), // Était prévue il y a 5 jours
            'mileage' => $vehicle->mileage ?? 50000,
            'cost' => 0, // Pas encore de coût car pas effectuée
            'garage' => 'Garage FlotteQ Service',
            'status' => 'pending',
            'priority' => 'high',
            'reason' => 'Maintenance périodique obligatoire',
            'notes' => 'URGENT: Cette maintenance aurait dû être effectuée il y a 5 jours',
            'next_maintenance_km' => ($vehicle->mileage ?? 50000) + 15000,
        ]);

        // 2. Maintenance programmée (upcoming - dans 3 jours)
        Maintenance::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'type' => 'Contrôle technique',
            'description' => 'Contrôle technique obligatoire',
            'date' => null,
            'scheduled_date' => Carbon::now()->addDays(3),
            'mileage' => $vehicle->mileage ?? 50000,
            'cost' => 0,
            'garage' => 'Centre de Contrôle Technique Agréé',
            'status' => 'scheduled',
            'priority' => 'urgent',
            'reason' => 'Obligation légale - Expiration du contrôle technique actuel',
            'notes' => 'Prendre RDV rapidement. Documents nécessaires: carte grise, attestation d\'assurance',
        ]);

        // 3. Maintenance en cours
        Maintenance::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'type' => 'Réparation freins',
            'description' => 'Remplacement des plaquettes de frein avant et disques',
            'date' => Carbon::now(), // En cours aujourd'hui
            'scheduled_date' => Carbon::now(),
            'mileage' => $vehicle->mileage ?? 50000,
            'cost' => 450.00,
            'garage' => 'Garage Spécialiste Freinage',
            'status' => 'in_progress',
            'priority' => 'high',
            'reason' => 'Usure détectée lors du dernier contrôle',
            'notes' => 'Véhicule déposé ce matin à 8h. Récupération prévue à 17h.',
        ]);

        // 4. Maintenance complétée récemment
        Maintenance::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'type' => 'Révision complète',
            'description' => 'Révision 60000 km - Changement filtres, bougies, liquides',
            'date' => Carbon::now()->subDays(15),
            'scheduled_date' => Carbon::now()->subDays(15),
            'mileage' => 60000,
            'cost' => 650.00,
            'garage' => 'Concessionnaire Officiel',
            'status' => 'completed',
            'priority' => 'medium',
            'reason' => 'Révision périodique selon carnet d\'entretien',
            'notes' => 'Tout est OK. Prochain contrôle recommandé à 75000 km ou dans 1 an.',
            'next_maintenance_km' => 75000,
            'completed_at' => Carbon::now()->subDays(15),
        ]);

        // 5. Maintenance programmée (dans 2 semaines)
        Maintenance::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'type' => 'Changement pneus',
            'description' => 'Remplacement des 4 pneus - usure normale',
            'date' => null,
            'scheduled_date' => Carbon::now()->addDays(14),
            'mileage' => $vehicle->mileage ?? 50000,
            'cost' => 0,
            'garage' => 'Centre Pneumatique Pro',
            'status' => 'scheduled',
            'priority' => 'medium',
            'reason' => 'Usure des pneus à 3mm - Remplacement préventif',
            'notes' => 'Devis accepté: 4x Michelin Primacy 4 - 600€ TTC',
        ]);

        // 6. Maintenance annulée
        Maintenance::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'type' => 'Diagnostic électronique',
            'description' => 'Vérification suite à voyant moteur',
            'date' => null,
            'scheduled_date' => Carbon::now()->subDays(2),
            'mileage' => $vehicle->mileage ?? 50000,
            'cost' => 0,
            'garage' => 'Garage Électronique Auto',
            'status' => 'cancelled',
            'priority' => 'low',
            'reason' => 'Voyant moteur allumé',
            'notes' => 'ANNULÉ: Le voyant s\'est éteint après redémarrage. Fausse alerte.',
        ]);

        $this->command->info('✅ 6 maintenances de test créées avec différents statuts:');
        $this->command->info('  - 1 en retard (overdue)');
        $this->command->info('  - 2 programmées (scheduled)');
        $this->command->info('  - 1 en cours (in_progress)');
        $this->command->info('  - 1 complétée (completed)');
        $this->command->info('  - 1 annulée (cancelled)');
    }
}