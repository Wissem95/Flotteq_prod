<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\Maintenance;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class ProductionDataSeeder extends Seeder
{
    /**
     * Seed realistic production data for FlotteQ testing
     */
    public function run(): void
    {
        // Utiliser le tenant "3WS" existant (ID: 8)
        $tenant = Tenant::find(8);
        
        if (!$tenant) {
            $this->command->error('❌ Tenant 3WS (ID: 8) not found!');
            return;
        }

        $this->command->info("🚀 Création de données métier pour le tenant: {$tenant->name}");

        // 1. CRÉER DES UTILISATEURS MÉTIER RÉALISTES
        $this->createBusinessUsers($tenant->id);
        
        // 2. CRÉER DES VÉHICULES DE FLOTTE PROFESSIONNELLE
        $this->createBusinessVehicles($tenant->id);
        
        // 3. CRÉER DES MAINTENANCES PROGRAMMÉES RÉALISTES
        $this->createRealisticMaintenances($tenant->id);
        
        $this->command->info('✅ Données métier créées avec succès pour une PME de transport!');
        $this->printSummary($tenant->id);
    }

    /**
     * Créer des utilisateurs métier réalistes
     */
    private function createBusinessUsers($tenantId)
    {
        $users = [
            [
                'first_name' => 'Sophie',
                'last_name' => 'Martin',
                'email' => 'sophie.martin@3ws-transport.fr',
                'username' => 'smartin',
                'role' => 'admin',
                'phone' => '06 12 34 56 78',
                'company' => '3WS Transport',
                'fleet_role' => 'Gestionnaire de flotte',
            ],
            [
                'first_name' => 'Thomas',
                'last_name' => 'Bernard',
                'email' => 'thomas.bernard@3ws-transport.fr', 
                'username' => 'tbernard',
                'role' => 'manager',
                'phone' => '06 23 45 67 89',
                'company' => '3WS Transport',
                'fleet_role' => 'Responsable maintenance',
            ],
            [
                'first_name' => 'Marie',
                'last_name' => 'Dubois',
                'email' => 'marie.dubois@3ws-transport.fr',
                'username' => 'mdubois', 
                'role' => 'user',
                'phone' => '06 34 56 78 90',
                'company' => '3WS Transport',
                'fleet_role' => 'Chauffeur senior',
            ],
        ];

        foreach ($users as $userData) {
            $existingUser = User::where('email', $userData['email'])->first();
            
            if (!$existingUser) {
                User::create([
                    'tenant_id' => $tenantId,
                    'first_name' => $userData['first_name'],
                    'last_name' => $userData['last_name'],
                    'email' => $userData['email'],
                    'username' => $userData['username'],
                    'password' => Hash::make('FlotteQ2024!'),
                    'role' => $userData['role'],
                    'phone' => $userData['phone'],
                    'company' => $userData['company'],
                    'fleet_role' => $userData['fleet_role'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);
                
                $this->command->info("👤 Utilisateur créé: {$userData['first_name']} {$userData['last_name']} ({$userData['role']})");
            }
        }
    }

    /**
     * Créer des véhicules de flotte professionnelle
     */
    private function createBusinessVehicles($tenantId)
    {
        // Récupérer un utilisateur admin pour assigner les véhicules
        $adminUser = User::where('tenant_id', $tenantId)->where('role', 'admin')->first();
        
        $vehicles = [
            // Utilitaires de livraison
            [
                'marque' => 'Renault',
                'modele' => 'Master L2H2',
                'immatriculation' => 'AB-123-CD',
                'vin' => 'VF1MA000054321234',
                'annee' => 2022,
                'couleur' => 'Blanc',
                'kilometrage' => 35000,
                'carburant' => 'diesel',
                'transmission' => 'manuelle',
                'puissance' => 165,
                'purchase_date' => '2022-03-15',
                'purchase_price' => 32000.00,
                'status' => 'active',
                'last_ct_date' => '2024-03-15',
                'next_ct_date' => '2026-03-15',
                'insurance_start_date' => '2024-01-01',
                'insurance_expiry_date' => '2024-12-31',
                'insurance_company' => 'Groupama Pro',
                'insurance_policy_number' => 'GP-FLOTTE-001',
                'notes' => 'Véhicule de livraison principal - Équipé GPS et camera de recul',
            ],
            [
                'marque' => 'Peugeot',
                'modele' => 'Boxer L2H2',
                'immatriculation' => 'CD-456-EF',
                'vin' => 'VF3YBBHMZ12345678',
                'annee' => 2021,
                'couleur' => 'Blanc',
                'kilometrage' => 67000,
                'carburant' => 'diesel',
                'transmission' => 'manuelle',
                'puissance' => 140,
                'purchase_date' => '2021-06-20',
                'purchase_price' => 28500.00,
                'status' => 'active',
                'last_ct_date' => '2023-06-20',
                'next_ct_date' => '2025-06-20',
                'insurance_start_date' => '2024-01-01',
                'insurance_expiry_date' => '2024-12-31',
                'insurance_company' => 'Groupama Pro',
                'insurance_policy_number' => 'GP-FLOTTE-002',
                'notes' => 'Véhicule de livraison secondaire - Révisé récemment',
            ],
            // Véhicule en maintenance
            [
                'marque' => 'Citroën',
                'modele' => 'Jumper L1H1',
                'immatriculation' => 'EF-789-GH',
                'vin' => 'VF7YDHMZ012345678',
                'annee' => 2020,
                'couleur' => 'Gris métallisé',
                'kilometrage' => 95000,
                'carburant' => 'diesel',
                'transmission' => 'manuelle',
                'puissance' => 130,
                'purchase_date' => '2020-09-10',
                'purchase_price' => 26000.00,
                'status' => 'en_maintenance',
                'last_ct_date' => '2022-09-10',
                'next_ct_date' => '2024-09-10',
                'insurance_start_date' => '2024-01-01',
                'insurance_expiry_date' => '2024-12-31',
                'insurance_company' => 'Groupama Pro',
                'insurance_policy_number' => 'GP-FLOTTE-003',
                'notes' => 'Actuellement en garage pour révision générale et remplacement embrayage',
            ],
            // Véhicules légers pour déplacements commerciaux
            [
                'marque' => 'Volkswagen',
                'modele' => 'Caddy Maxi',
                'immatriculation' => 'GH-012-IJ',
                'vin' => 'WVWZZZ2KZ1E123456',
                'annee' => 2023,
                'couleur' => 'Bleu foncé',
                'kilometrage' => 18000,
                'carburant' => 'diesel',
                'transmission' => 'automatique',
                'puissance' => 122,
                'purchase_date' => '2023-01-15',
                'purchase_price' => 29500.00,
                'status' => 'active',
                'last_ct_date' => null, // Véhicule récent
                'next_ct_date' => '2027-01-15',
                'insurance_start_date' => '2024-01-01',
                'insurance_expiry_date' => '2024-12-31',
                'insurance_company' => 'Groupama Pro',
                'insurance_policy_number' => 'GP-FLOTTE-004',
                'notes' => 'Véhicule commercial neuf - Garantie constructeur jusqu\'en 2026',
            ],
            [
                'marque' => 'Ford',
                'modele' => 'Transit Connect',
                'immatriculation' => 'IJ-345-KL',
                'vin' => 'WF0XXXGCDXDA123456',
                'annee' => 2021,
                'couleur' => 'Rouge',
                'kilometrage' => 52000,
                'carburant' => 'diesel',
                'transmission' => 'manuelle',
                'puissance' => 100,
                'purchase_date' => '2021-11-01',
                'purchase_price' => 22500.00,
                'status' => 'active',
                'last_ct_date' => '2023-11-01',
                'next_ct_date' => '2025-11-01',
                'insurance_start_date' => '2024-01-01',
                'insurance_expiry_date' => '2024-12-31',
                'insurance_company' => 'Groupama Pro',
                'insurance_policy_number' => 'GP-FLOTTE-005',
                'notes' => 'Véhicule de livraison urbaine - Ideal pour centre-ville',
            ],
        ];

        foreach ($vehicles as $vehicleData) {
            $existingVehicle = Vehicle::where('immatriculation', $vehicleData['immatriculation'])->first();
            
            if (!$existingVehicle) {
                Vehicle::create([
                    'tenant_id' => $tenantId,
                    'user_id' => $adminUser->id,
                    ...$vehicleData
                ]);
                
                $this->command->info("🚗 Véhicule créé: {$vehicleData['marque']} {$vehicleData['modele']} ({$vehicleData['immatriculation']})");
            }
        }
    }

    /**
     * Créer des maintenances programmées réalistes
     */
    private function createRealisticMaintenances($tenantId)
    {
        $vehicles = Vehicle::where('tenant_id', $tenantId)->get();
        $adminUser = User::where('tenant_id', $tenantId)->where('role', 'admin')->first();

        $maintenancesData = [
            // Maintenance en retard (critique)
            [
                'maintenance_type' => 'revision',
                'description' => 'Révision 60000 km - URGENT en retard',
                'maintenance_date' => null,
                'scheduled_date' => Carbon::now()->subDays(8), // 8 jours de retard
                'status' => 'scheduled',
                'priority' => 'urgent',
                'reason' => 'Révision périodique obligatoire selon carnet d\'entretien',
                'mileage' => 67000,
                'cost' => 0,
                'workshop' => 'Garage Peugeot Bourg-en-Bresse',
                'notes' => '⚠️ URGENT: Révision en retard de 8 jours! Prise RDV nécessaire immédiatement.',
            ],
            // Maintenance prochainement due
            [
                'maintenance_type' => 'oil_change',
                'description' => 'Vidange + filtre à huile et contrôles',
                'maintenance_date' => null,
                'scheduled_date' => Carbon::now()->addDays(5), // Dans 5 jours
                'status' => 'scheduled',
                'priority' => 'high',
                'reason' => 'Entretien périodique 35000 km',
                'mileage' => 35000,
                'cost' => 0,
                'workshop' => 'Garage Renault Pro Villefranche',
                'notes' => 'RDV pris le ' . Carbon::now()->addDays(5)->format('d/m/Y') . ' à 9h00. Prévoir 2h d\'immobilisation.',
            ],
            // Maintenance en cours
            [
                'maintenance_type' => 'other',
                'description' => 'Remplacement embrayage + révision générale',
                'maintenance_date' => Carbon::now(),
                'scheduled_date' => Carbon::now(),
                'status' => 'in_progress',
                'priority' => 'high',
                'reason' => 'Embrayage défaillant détecté lors contrôle routine',
                'mileage' => 95000,
                'cost' => 1200.00,
                'workshop' => 'Garage Central Citroën',
                'notes' => 'Véhicule déposé ce matin. Fin des travaux prévue demain 17h. Véhicule de remplacement attribué.',
                'completed_at' => null,
            ],
            // Maintenance programmée future
            [
                'maintenance_type' => 'tires',
                'description' => 'Changement des 4 pneus + géométrie',
                'maintenance_date' => null,
                'scheduled_date' => Carbon::now()->addDays(25), // Dans 25 jours
                'status' => 'scheduled',
                'priority' => 'medium',
                'reason' => 'Usure des pneus à 3mm - Changement préventif avant hiver',
                'mileage' => 18000,
                'cost' => 0,
                'workshop' => 'Euromaster Mâcon',
                'notes' => 'Devis accepté: 4 pneus Michelin Agilis + main d\'œuvre = 680€ TTC',
            ],
            // Maintenance complétée récemment
            [
                'maintenance_type' => 'revision',
                'description' => 'Révision 50000 km complète effectuée',
                'maintenance_date' => Carbon::now()->subDays(15),
                'scheduled_date' => Carbon::now()->subDays(15),
                'status' => 'completed',
                'priority' => 'medium',
                'reason' => 'Entretien périodique selon planning maintenance',
                'mileage' => 50000,
                'cost' => 420.00,
                'workshop' => 'Ford Service Bourg-en-Bresse',
                'notes' => 'Révision effectuée avec succès. Prochaine révision à 70000 km ou dans 12 mois.',
                'completed_at' => Carbon::now()->subDays(15),
                'next_maintenance' => Carbon::now()->addMonths(10), // Dans 10 mois
                'next_maintenance_km' => 70000,
            ],
        ];

        // Assigner les maintenances aux véhicules de façon réaliste
        foreach ($vehicles->take(5) as $index => $vehicle) {
            if (isset($maintenancesData[$index])) {
                $maintenanceData = $maintenancesData[$index];
                
                Maintenance::create([
                    'vehicle_id' => $vehicle->id,
                    'user_id' => $adminUser->id,
                    ...$maintenanceData
                ]);
                
                $statusEmoji = match($maintenanceData['status']) {
                    'scheduled' => '📅',
                    'in_progress' => '🔧',
                    'completed' => '✅',
                    default => '📝'
                };
                
                $this->command->info("{$statusEmoji} Maintenance créée: {$maintenanceData['maintenance_type']} pour {$vehicle->marque} {$vehicle->modele}");
            }
        }
    }

    /**
     * Afficher un résumé des données créées
     */
    private function printSummary($tenantId)
    {
        $vehicleCount = Vehicle::where('tenant_id', $tenantId)->count();
        $userCount = User::where('tenant_id', $tenantId)->count();
        $maintenanceCount = Maintenance::whereIn('vehicle_id', 
            Vehicle::where('tenant_id', $tenantId)->pluck('id')
        )->count();

        $this->command->info('');
        $this->command->info('📊 RÉSUMÉ DES DONNÉES CRÉÉES:');
        $this->command->info("👥 Utilisateurs: {$userCount}");
        $this->command->info("🚗 Véhicules: {$vehicleCount}");
        $this->command->info("🔧 Maintenances: {$maintenanceCount}");
        $this->command->info('');
        $this->command->info('🔐 COMPTES DE TEST:');
        $this->command->info('   Admin: sophie.martin@3ws-transport.fr / FlotteQ2024!');
        $this->command->info('   Manager: thomas.bernard@3ws-transport.fr / FlotteQ2024!');
        $this->command->info('   User: marie.dubois@3ws-transport.fr / FlotteQ2024!');
        $this->command->info('');
        $this->command->info('✨ Tenant "3WS" maintenant prêt pour les tests de production!');
    }
}