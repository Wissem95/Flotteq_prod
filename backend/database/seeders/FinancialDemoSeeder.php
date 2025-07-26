<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;
use App\Models\Transaction;
use App\Models\Maintenance;
use App\Models\Invoice;
use App\Models\Repair;
use App\Models\User;
use App\Models\Tenant;
use Carbon\Carbon;

class FinancialDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer un utilisateur et tenant existants
        $user = User::first();
        $tenant = Tenant::first();
        
        if (!$user || !$tenant) {
            $this->command->info('Aucun utilisateur ou tenant trouvé. Veuillez d\'abord créer des utilisateurs.');
            return;
        }

        // Créer quelques véhicules supplémentaires si nécessaire
        $vehicles = Vehicle::where('user_id', $user->id)->where('tenant_id', $tenant->id)->get();
        
        if ($vehicles->count() < 3) {
            // Créer des véhicules supplémentaires
            $additionalVehicles = [
                [
                    'marque' => 'Mercedes',
                    'modele' => 'Classe A',
                    'immatriculation' => 'AB-123-CD',
                    'annee' => 2020,
                    'purchase_price' => 25000,
                    'status' => 'active',
                ],
                [
                    'marque' => 'BMW',
                    'modele' => 'Serie 3',
                    'immatriculation' => 'EF-456-GH',
                    'annee' => 2019,
                    'purchase_price' => 30000,
                    'status' => 'active',
                ],
                [
                    'marque' => 'Audi',
                    'modele' => 'A4',
                    'immatriculation' => 'IJ-789-KL',
                    'annee' => 2021,
                    'purchase_price' => 35000,
                    'status' => 'en_maintenance',
                ],
            ];

            foreach ($additionalVehicles as $vehicleData) {
                Vehicle::create(array_merge($vehicleData, [
                    'user_id' => $user->id,
                    'tenant_id' => $tenant->id,
                    'carburant' => 'essence',
                    'transmission' => 'manuelle',
                    'puissance' => rand(100, 200),
                    'kilometrage' => rand(10000, 80000),
                    'couleur' => 'Gris',
                    'vin' => 'VIN' . rand(1000000, 9999999),
                ]));
            }
            
            $vehicles = Vehicle::where('user_id', $user->id)->where('tenant_id', $tenant->id)->get();
        }

        // Créer des maintenances avec coûts
        $maintenanceTypes = ['oil_change', 'revision', 'tires', 'brakes', 'belt'];
        
        foreach ($vehicles as $vehicle) {
            for ($i = 0; $i < rand(2, 5); $i++) {
                Maintenance::create([
                    'vehicle_id' => $vehicle->id,
                    'maintenance_type' => $maintenanceTypes[array_rand($maintenanceTypes)],
                    'description' => 'Entretien ' . $maintenanceTypes[array_rand($maintenanceTypes)],
                    'maintenance_date' => Carbon::now()->subDays(rand(1, 365)),
                    'cost' => rand(100, 800),
                    'status' => 'completed',
                    'workshop' => 'Garage Auto Plus',
                    'mileage' => $vehicle->kilometrage - rand(1000, 20000),
                ]);
            }
        }

        // Créer des factures
        $expenseTypes = ['technical_inspection', 'insurance', 'other'];
        
        foreach ($vehicles as $vehicle) {
            for ($i = 0; $i < rand(1, 3); $i++) {
                Invoice::create([
                    'vehicle_id' => $vehicle->id,
                    'invoice_number' => 'FACT-' . rand(1000, 9999),
                    'supplier' => 'Fournisseur Auto',
                    'amount' => rand(50, 400),
                    'invoice_date' => Carbon::now()->subDays(rand(1, 180)),
                    'expense_type' => $expenseTypes[array_rand($expenseTypes)],
                    'description' => 'Facture pour ' . $expenseTypes[array_rand($expenseTypes)],
                    'status' => rand(0, 1) ? 'validated' : 'pending',
                ]);
            }
        }

        // Créer des réparations
        $repairTypes = ['Moteur', 'Transmission', 'Électronique', 'Carrosserie'];
        
        foreach ($vehicles->take(2) as $vehicle) {
            for ($i = 0; $i < rand(1, 2); $i++) {
                Repair::create([
                    'vehicle_id' => $vehicle->id,
                    'description' => 'Réparation ' . $repairTypes[array_rand($repairTypes)],
                    'total_cost' => rand(200, 1500),
                    'repair_date' => Carbon::now()->subDays(rand(1, 200)),
                    'workshop' => 'Garage Réparation Pro',
                    'mileage' => $vehicle->kilometrage - rand(1000, 10000),
                    'status' => 'completed',
                ]);
            }
        }

        // Créer des transactions
        foreach ($vehicles->take(2) as $vehicle) {
            // Transaction d'achat
            Transaction::create([
                'vehicle_id' => $vehicle->id,
                'user_id' => $user->id,
                'tenant_id' => $tenant->id,
                'type' => 'purchase',
                'date' => Carbon::now()->subDays(rand(200, 400)),
                'price' => $vehicle->purchase_price,
                'mileage' => $vehicle->kilometrage - rand(20000, 40000),
                'seller_buyer_name' => 'Concessionnaire Auto',
                'seller_buyer_contact' => '01 23 45 67 89',
                'reason' => 'Achat pour extension de flotte',
                'status' => 'completed',
                'notes' => 'Véhicule en excellent état',
            ]);
        }

        // Créer une transaction de vente fictive
        if ($vehicles->count() > 0) {
            $soldVehicle = $vehicles->first();
            Transaction::create([
                'vehicle_id' => $soldVehicle->id,
                'user_id' => $user->id,
                'tenant_id' => $tenant->id,
                'type' => 'sale',
                'date' => Carbon::now()->subDays(30),
                'price' => $soldVehicle->purchase_price * 0.8, // 20% de dépréciation
                'mileage' => $soldVehicle->kilometrage,
                'seller_buyer_name' => 'Particulier',
                'seller_buyer_contact' => '06 12 34 56 78',
                'reason' => 'Renouvellement de flotte',
                'status' => 'completed',
                'notes' => 'Vente à un particulier',
            ]);
        }

        $this->command->info('Données de démonstration financières créées avec succès !');
        $this->command->info('- Maintenances avec coûts');
        $this->command->info('- Factures diverses');
        $this->command->info('- Réparations');
        $this->command->info('- Transactions d\'achat/vente');
    }
}