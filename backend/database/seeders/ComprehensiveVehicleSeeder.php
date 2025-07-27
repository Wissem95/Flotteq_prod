<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Vehicle;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Maintenance;
use App\Models\EtatDesLieux;
use Carbon\Carbon;

class ComprehensiveVehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer le premier tenant et utilisateur disponibles
        $tenant = Tenant::first();
        $user = User::where('tenant_id', $tenant->id)->first();

        if (!$tenant || !$user) {
            $this->command->warn('Aucun tenant ou utilisateur trouvé. Veuillez d\'abord créer des données de base.');
            return;
        }

        $vehicles = [
            [
                'marque' => 'Toyota',
                'modele' => 'Corolla',
                'immatriculation' => 'AA-123-BB',
                'vin' => 'JTDKB20U203456789',
                'annee' => 2020,
                'couleur' => 'Blanc',
                'kilometrage' => 45000,
                'carburant' => 'essence',
                'transmission' => 'manuelle',
                'puissance' => 122,
                'purchase_date' => '2020-03-15',
                'purchase_price' => 22500.00,
                'status' => 'active',
                'last_ct_date' => '2023-08-15',
                'next_ct_date' => '2025-08-15',
                'notes' => 'Véhicule de société en parfait état, entretien régulier',
            ],
            [
                'marque' => 'Renault',
                'modele' => 'Megane',
                'immatriculation' => 'BB-456-CC',
                'vin' => 'VF1KB0E0H54123456',
                'annee' => 2019,
                'couleur' => 'Gris',
                'kilometrage' => 62000,
                'carburant' => 'diesel',
                'transmission' => 'automatique',
                'puissance' => 115,
                'purchase_date' => '2019-07-20',
                'purchase_price' => 19800.00,
                'status' => 'active',
                'last_ct_date' => '2023-05-10',
                'next_ct_date' => '2025-05-10',
                'notes' => 'Véhicule commercial, excellente consommation',
            ],
            [
                'marque' => 'Volkswagen',
                'modele' => 'Golf',
                'immatriculation' => 'CC-789-DD',
                'vin' => 'WVWZZZ1KZ9W123456',
                'annee' => 2021,
                'couleur' => 'Noir',
                'kilometrage' => 28000,
                'carburant' => 'essence',
                'transmission' => 'manuelle',
                'puissance' => 130,
                'purchase_date' => '2021-01-10',
                'purchase_price' => 26300.00,
                'status' => 'active',
                'last_ct_date' => '2024-01-15',
                'next_ct_date' => '2026-01-15',
                'notes' => 'Véhicule récent, garantie constructeur',
            ],
            [
                'marque' => 'Ford',
                'modele' => 'Transit',
                'immatriculation' => 'DD-012-EE',
                'vin' => 'WF0XXTTGMXKX12345',
                'annee' => 2018,
                'couleur' => 'Blanc',
                'kilometrage' => 98000,
                'carburant' => 'diesel',
                'transmission' => 'manuelle',
                'puissance' => 140,
                'purchase_date' => '2018-05-25',
                'purchase_price' => 28900.00,
                'status' => 'en_maintenance',
                'last_ct_date' => '2023-03-12',
                'next_ct_date' => '2025-03-12',
                'notes' => 'Utilitaire de livraison, réparation freins en cours',
            ],
            [
                'marque' => 'BMW',
                'modele' => 'Serie 3',
                'immatriculation' => 'EE-345-FF',
                'vin' => 'WBAFC51010A123456',
                'annee' => 2022,
                'couleur' => 'Bleu',
                'kilometrage' => 15000,
                'carburant' => 'hybride',
                'transmission' => 'automatique',
                'puissance' => 184,
                'purchase_date' => '2022-09-08',
                'purchase_price' => 45200.00,
                'status' => 'active',
                'last_ct_date' => null,
                'next_ct_date' => '2026-09-08',
                'notes' => 'Véhicule de direction, hybride rechargeable',
            ],
            [
                'marque' => 'Citroen',
                'modele' => 'Berlingo',
                'immatriculation' => 'FF-678-GG',
                'vin' => 'VF7MBHFUB8J123456',
                'annee' => 2017,
                'couleur' => 'Rouge',
                'kilometrage' => 110000,
                'carburant' => 'diesel',
                'transmission' => 'manuelle',
                'puissance' => 100,
                'purchase_date' => '2017-11-30',
                'purchase_price' => 16500.00,
                'status' => 'vendu',
                'last_ct_date' => '2022-11-15',
                'next_ct_date' => '2024-11-15',
                'notes' => 'Vendu en octobre 2024, remplacé par Transit',
            ],
            [
                'marque' => 'Nissan',
                'modele' => 'Qashqai',
                'immatriculation' => 'GG-901-HH',
                'vin' => 'SJNFAAJ10U2123456',
                'annee' => 2021,
                'couleur' => 'Argent',
                'kilometrage' => 32000,
                'carburant' => 'essence',
                'transmission' => 'automatique',
                'puissance' => 140,
                'purchase_date' => '2021-06-15',
                'purchase_price' => 29800.00,
                'status' => 'active',
                'last_ct_date' => '2024-06-20',
                'next_ct_date' => '2026-06-20',
                'notes' => 'SUV familial, climatisation automatique',
            ],
            [
                'marque' => 'Mercedes',
                'modele' => 'Sprinter',
                'immatriculation' => 'HH-234-II',
                'vin' => 'WDB9066161B123456',
                'annee' => 2020,
                'couleur' => 'Blanc',
                'kilometrage' => 75000,
                'carburant' => 'diesel',
                'transmission' => 'automatique',
                'puissance' => 163,
                'purchase_date' => '2020-08-12',
                'purchase_price' => 42000.00,
                'status' => 'active',
                'last_ct_date' => '2023-09-05',
                'next_ct_date' => '2025-09-05',
                'notes' => 'Fourgon aménagé pour transport de marchandises',
            ]
        ];

        foreach ($vehicles as $vehicleData) {
            $vehicle = Vehicle::create([
                'user_id' => $user->id,
                'tenant_id' => $tenant->id,
                ...$vehicleData
            ]);

            // Ajouter quelques maintenances pour chaque véhicule
            $this->addMaintenances($vehicle);

            // Ajouter quelques états des lieux
            $this->addEtatsDesLieux($vehicle, $user);

            $this->command->info("Véhicule créé: {$vehicle->marque} {$vehicle->modele} ({$vehicle->immatriculation})");
        }

        $this->command->info('Tous les véhicules d\'exemple ont été créés avec succès !');
    }

    private function addMaintenances(Vehicle $vehicle): void
    {
        $maintenanceTypes = ['oil_change', 'revision', 'tires', 'brakes', 'belt', 'filters'];
        $workshops = ['Garage Dupont', 'Auto Service Plus', 'Mécanik Pro', 'Station Total', 'Garage Central'];

        $numberOfMaintenances = rand(2, 5);
        
        for ($i = 0; $i < $numberOfMaintenances; $i++) {
            $daysAgo = rand(30, 700); // Entre 1 mois et 2 ans
            $date = Carbon::now()->subDays($daysAgo);
            
            // Calculer un kilométrage cohérent basé sur la date
            $kmPerDay = rand(20, 100); // 20-100 km par jour
            $maintenanceKm = max(5000, $vehicle->kilometrage - ($daysAgo * $kmPerDay / 7)); // Approximation

            Maintenance::create([
                'vehicle_id' => $vehicle->id,
                'maintenance_type' => $maintenanceTypes[array_rand($maintenanceTypes)],
                'description' => $this->getMaintenanceDescription($maintenanceTypes[array_rand($maintenanceTypes)]),
                'maintenance_date' => $date,
                'mileage' => $maintenanceKm,
                'cost' => rand(80, 800),
                'workshop' => $workshops[array_rand($workshops)],
                'next_maintenance' => $date->copy()->addMonths(rand(6, 12)),
                'status' => 'completed',
                'notes' => 'Maintenance effectuée selon planning',
            ]);
        }
    }

    private function addEtatsDesLieux(Vehicle $vehicle, User $user): void
    {
        $numberOfEtats = rand(1, 3);
        
        for ($i = 0; $i < $numberOfEtats; $i++) {
            $daysAgo = rand(7, 365);
            $date = Carbon::now()->subDays($daysAgo);
            
            // Calculer un kilométrage cohérent
            $kmPerDay = rand(20, 100);
            $etatKm = max(1000, $vehicle->kilometrage - ($daysAgo * $kmPerDay / 7));

            EtatDesLieux::create([
                'vehicle_id' => $vehicle->id,
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
                'type' => rand(0, 1) ? 'depart' : 'retour',
                'conducteur' => $this->getRandomDriver(),
                'kilometrage' => $etatKm,
                'notes' => $this->getRandomNotes(),
                'photos' => null, // Pas de photos pour les données de test
                'is_validated' => rand(0, 1),
                'validated_at' => rand(0, 1) ? $date->copy()->addHours(rand(1, 24)) : null,
                'validated_by' => rand(0, 1) ? $user->id : null,
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
    }

    private function getMaintenanceDescription(string $type): string
    {
        $descriptions = [
            'oil_change' => 'Vidange moteur + filtre à huile + vérification niveaux',
            'revision' => 'Révision complète 15 000 km - contrôle général',
            'tires' => 'Changement pneumatiques avant + équilibrage',
            'brakes' => 'Remplacement plaquettes de frein + disques',
            'belt' => 'Changement courroie de distribution + pompe à eau',
            'filters' => 'Remplacement filtres air/habitacle/carburant',
        ];

        return $descriptions[$type] ?? 'Maintenance générale';
    }

    private function getRandomDriver(): string
    {
        $drivers = [
            'Jean Martin',
            'Marie Dubois',
            'Pierre Moreau',
            'Sophie Bernard',
            'Antoine Leroy',
            'Claire Petit',
            'Michel Garcia',
            'Nathalie Roux',
        ];

        return $drivers[array_rand($drivers)];
    }

    private function getRandomNotes(): string
    {
        $notes = [
            'Véhicule en bon état général',
            'Légère rayure côté conducteur à noter',
            'Pneus à surveiller pour prochaine fois',
            'Nettoyage intérieur effectué',
            'RAS - état conforme',
            'Petite éraflure pare-choc arrière',
            'Véhicule propre et fonctionnel',
            'Niveau carburant : 3/4 plein',
        ];

        return rand(0, 1) ? $notes[array_rand($notes)] : '';
    }
}