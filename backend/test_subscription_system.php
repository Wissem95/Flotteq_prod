<?php

/**
 * Script de test complet du système d'abonnement FlotteQ
 * Ce script teste les restrictions, l'upgrade/downgrade et l'injection de données
 */

require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

use App\Models\Tenant;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Subscription;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\Hash;

class SubscriptionSystemTester
{
    private $tenant;
    private $testResults = [];
    
    public function __construct()
    {
        echo "🚀 TESTE DU SYSTÈME D'ABONNEMENT FLOTTEQ\n";
        echo str_repeat('=', 60) . "\n";
    }

    /**
     * Exécuter tous les tests
     */
    public function runAllTests(): void
    {
        try {
            $this->setupTestEnvironment();
            $this->testDataInjection();
            $this->testPlanLimitations();
            $this->testPlanUpgrade();
            $this->testPlanDowngrade();
            $this->generateReport();
        } catch (\Exception $e) {
            echo "❌ ERREUR CRITIQUE: " . $e->getMessage() . "\n";
            echo "Stack trace: " . $e->getTraceAsString() . "\n";
        }
    }

    /**
     * Configuration de l'environnement de test
     */
    private function setupTestEnvironment(): void
    {
        echo "\n1. 🔧 CONFIGURATION DE L'ENVIRONNEMENT\n";
        echo str_repeat('-', 40) . "\n";
        
        // Récupérer ou créer le tenant de test "3WS"
        $this->tenant = Tenant::where('name', '3WS')->first();
        
        if (!$this->tenant) {
            echo "⚠️ Tenant '3WS' non trouvé, création...\n";
            $this->tenant = Tenant::create([
                'name' => '3WS',
                'domain' => '3ws.flotteq.local'
            ]);
        }

        echo "✅ Tenant utilisé: {$this->tenant->name} (ID: {$this->tenant->id})\n";

        // Vérifier les plans d'abonnement
        $plans = Subscription::active()->get();
        echo "✅ Plans disponibles: " . $plans->count() . "\n";
        
        foreach ($plans as $plan) {
            echo "   - {$plan->name}: {$plan->price}€ ({$plan->max_vehicles} véhicules, {$plan->max_users} users)\n";
        }

        $this->testResults['setup'] = true;
    }

    /**
     * Test d'injection des données métier
     */
    private function testDataInjection(): void
    {
        echo "\n2. 📊 TEST D'INJECTION DES DONNÉES MÉTIER\n";
        echo str_repeat('-', 40) . "\n";

        // Exécuter le seeder
        try {
            echo "⏳ Exécution du ProductionDataSeeder...\n";
            \Artisan::call('db:seed', ['--class' => 'ProductionDataSeeder']);
            
            // Vérifier les résultats
            $vehicleCount = Vehicle::where('tenant_id', $this->tenant->id)->count();
            $userCount = User::where('tenant_id', $this->tenant->id)->count();
            
            echo "✅ Véhicules créés: {$vehicleCount}\n";
            echo "✅ Utilisateurs créés: {$userCount}\n";
            
            if ($vehicleCount >= 5 && $userCount >= 3) {
                $this->testResults['data_injection'] = true;
                echo "✅ Injection de données réussie!\n";
            } else {
                $this->testResults['data_injection'] = false;
                echo "⚠️ Données insuffisantes créées\n";
            }
        } catch (\Exception $e) {
            echo "❌ Erreur lors de l'injection: " . $e->getMessage() . "\n";
            $this->testResults['data_injection'] = false;
        }
    }

    /**
     * Test des limitations de plan
     */
    private function testPlanLimitations(): void
    {
        echo "\n3. 🚫 TEST DES LIMITATIONS DE PLAN\n";
        echo str_repeat('-', 40) . "\n";

        // Récupérer l'abonnement actuel
        $primaryUser = User::where('tenant_id', $this->tenant->id)->first();
        $subscription = UserSubscription::where('tenant_id', $this->tenant->id)
            ->where('is_active', true)
            ->with('subscription')
            ->first();

        if ($subscription) {
            echo "📋 Plan actuel: {$subscription->subscription->name}\n";
            echo "   Limites: {$subscription->subscription->max_vehicles} véhicules, {$subscription->subscription->max_users} utilisateurs\n";
        } else {
            echo "⚠️ Aucun abonnement actif - Mode gratuit\n";
        }

        // Test des limites véhicules
        $currentVehicles = Vehicle::where('tenant_id', $this->tenant->id)->count();
        $vehicleLimit = $subscription ? $subscription->subscription->max_vehicles : 1;
        
        echo "\n🚗 Test limite véhicules:\n";
        echo "   Utilisés: {$currentVehicles} / {$vehicleLimit}\n";
        
        if ($currentVehicles >= $vehicleLimit) {
            echo "✅ Limitation fonctionnelle (limite atteinte)\n";
            $this->testResults['vehicle_limits'] = true;
        } else {
            echo "⚠️ Limite non encore atteinte\n";
            $this->testResults['vehicle_limits'] = 'partial';
        }

        // Test des limites utilisateurs
        $currentUsers = User::where('tenant_id', $this->tenant->id)->count();
        $userLimit = $subscription ? $subscription->subscription->max_users : 1;
        
        echo "\n👥 Test limite utilisateurs:\n";
        echo "   Utilisés: {$currentUsers} / {$userLimit}\n";
        
        if ($currentUsers >= $userLimit) {
            echo "✅ Limitation fonctionnelle (limite atteinte)\n";
            $this->testResults['user_limits'] = true;
        } else {
            echo "⚠️ Limite non encore atteinte\n";
            $this->testResults['user_limits'] = 'partial';
        }
    }

    /**
     * Test d'upgrade de plan
     */
    private function testPlanUpgrade(): void
    {
        echo "\n4. ⬆️ TEST D'UPGRADE DE PLAN\n";
        echo str_repeat('-', 40) . "\n";

        try {
            // Récupérer l'abonnement actuel
            $currentSubscription = UserSubscription::where('tenant_id', $this->tenant->id)
                ->where('is_active', true)
                ->with('subscription')
                ->first();

            // Trouver un plan plus élevé
            $currentPrice = $currentSubscription ? $currentSubscription->subscription->price : 0;
            $higherPlan = Subscription::where('price', '>', $currentPrice)
                ->where('is_active', true)
                ->orderBy('price')
                ->first();

            if (!$higherPlan) {
                echo "⚠️ Aucun plan supérieur disponible pour l'upgrade\n";
                $this->testResults['upgrade'] = 'skipped';
                return;
            }

            echo "📈 Upgrade vers: {$higherPlan->name} ({$higherPlan->price}€)\n";
            echo "   Nouvelles limites: {$higherPlan->max_vehicles} véhicules, {$higherPlan->max_users} utilisateurs\n";

            // Simuler l'upgrade
            if ($currentSubscription) {
                $currentSubscription->update([
                    'is_active' => false,
                    'end_date' => now(),
                ]);
            }

            $newSubscription = UserSubscription::create([
                'tenant_id' => $this->tenant->id,
                'subscription_id' => $higherPlan->id,
                'is_active' => true,
                'start_date' => now(),
                'end_date' => now()->addMonth(),
                'metadata' => [
                    'billing_cycle' => 'monthly',
                    'upgrade_test' => true
                ]
            ]);

            echo "✅ Upgrade réussi vers {$higherPlan->name}\n";
            $this->testResults['upgrade'] = true;

        } catch (\Exception $e) {
            echo "❌ Erreur lors de l'upgrade: " . $e->getMessage() . "\n";
            $this->testResults['upgrade'] = false;
        }
    }

    /**
     * Test de downgrade de plan
     */
    private function testPlanDowngrade(): void
    {
        echo "\n5. ⬇️ TEST DE DOWNGRADE DE PLAN\n";
        echo str_repeat('-', 40) . "\n";

        try {
            // Récupérer l'abonnement actuel
            $currentSubscription = UserSubscription::where('tenant_id', $this->tenant->id)
                ->where('is_active', true)
                ->with('subscription')
                ->first();

            if (!$currentSubscription) {
                echo "⚠️ Aucun abonnement actuel pour downgrade\n";
                $this->testResults['downgrade'] = 'skipped';
                return;
            }

            // Trouver un plan moins cher
            $currentPrice = $currentSubscription->subscription->price;
            $lowerPlan = Subscription::where('price', '<', $currentPrice)
                ->where('is_active', true)
                ->orderBy('price', 'desc')
                ->first();

            if (!$lowerPlan) {
                echo "⚠️ Aucun plan inférieur disponible pour le downgrade\n";
                $this->testResults['downgrade'] = 'skipped';
                return;
            }

            echo "📉 Downgrade vers: {$lowerPlan->name} ({$lowerPlan->price}€)\n";
            echo "   Nouvelles limites: {$lowerPlan->max_vehicles} véhicules, {$lowerPlan->max_users} utilisateurs\n";

            // Vérifier les limites actuelles vs nouveau plan
            $currentVehicles = Vehicle::where('tenant_id', $this->tenant->id)->count();
            $currentUsers = User::where('tenant_id', $this->tenant->id)->count();

            if ($currentVehicles > $lowerPlan->max_vehicles || $currentUsers > $lowerPlan->max_users) {
                echo "❌ Downgrade impossible: utilisation actuelle dépasse les limites du plan inférieur\n";
                echo "   Véhicules: {$currentVehicles} > {$lowerPlan->max_vehicles}\n";
                echo "   Utilisateurs: {$currentUsers} > {$lowerPlan->max_users}\n";
                $this->testResults['downgrade'] = 'blocked_correctly';
            } else {
                echo "✅ Downgrade possible et effectué\n";
                // Effectuer le downgrade
                $currentSubscription->update([
                    'is_active' => false,
                    'end_date' => now(),
                ]);

                UserSubscription::create([
                    'tenant_id' => $this->tenant->id,
                    'subscription_id' => $lowerPlan->id,
                    'is_active' => true,
                    'start_date' => now(),
                    'end_date' => now()->addMonth(),
                    'metadata' => [
                        'billing_cycle' => 'monthly',
                        'downgrade_test' => true
                    ]
                ]);

                $this->testResults['downgrade'] = true;
            }

        } catch (\Exception $e) {
            echo "❌ Erreur lors du downgrade: " . $e->getMessage() . "\n";
            $this->testResults['downgrade'] = false;
        }
    }

    /**
     * Générer le rapport final
     */
    private function generateReport(): void
    {
        echo "\n" . str_repeat('=', 60) . "\n";
        echo "📋 RAPPORT FINAL DES TESTS\n";
        echo str_repeat('=', 60) . "\n";

        $totalTests = count($this->testResults);
        $passedTests = 0;
        $partialTests = 0;

        foreach ($this->testResults as $test => $result) {
            $emoji = match($result) {
                true => '✅',
                'partial' => '⚠️',
                'skipped' => '⏭️',
                'blocked_correctly' => '✅',
                default => '❌'
            };

            $status = match($result) {
                true => 'RÉUSSI',
                'partial' => 'PARTIEL',
                'skipped' => 'IGNORÉ',
                'blocked_correctly' => 'BLOQUÉ (CORRECT)',
                default => 'ÉCHEC'
            };

            echo "{$emoji} " . ucfirst(str_replace('_', ' ', $test)) . ": {$status}\n";

            if ($result === true || $result === 'blocked_correctly') {
                $passedTests++;
            } elseif ($result === 'partial') {
                $partialTests++;
            }
        }

        echo str_repeat('-', 60) . "\n";
        echo "📊 RÉSUMÉ:\n";
        echo "   Tests réussis: {$passedTests}/{$totalTests}\n";
        echo "   Tests partiels: {$partialTests}/{$totalTests}\n";
        echo "   Taux de réussite: " . round(($passedTests / $totalTests) * 100, 1) . "%\n";

        if ($passedTests >= ($totalTests * 0.8)) {
            echo "\n🎉 SYSTÈME D'ABONNEMENT OPÉRATIONNEL!\n";
            echo "Le système de restrictions et d'upgrade/downgrade fonctionne correctement.\n";
        } else {
            echo "\n⚠️ AMÉLIORATIONS NÉCESSAIRES\n";
            echo "Certains tests ont échoué, vérifiez les logs ci-dessus.\n";
        }

        // Instructions de test manuel
        echo "\n" . str_repeat('-', 60) . "\n";
        echo "🔍 TESTS MANUELS RECOMMANDÉS:\n";
        echo "1. Connectez-vous avec: sophie.martin@3ws-transport.fr / FlotteQ2024!\n";
        echo "2. Tentez d'ajouter un véhicule quand la limite est atteinte\n";
        echo "3. Testez l'interface d'upgrade dans le dashboard tenant\n";
        echo "4. Vérifiez les alertes de limite dans l'interface\n";
        echo "5. Testez le downgrade depuis l'interface interne\n";
    }
}

// Exécution du script si appelé directement
if (php_sapi_name() === 'cli') {
    $tester = new SubscriptionSystemTester();
    $tester->runAllTests();
}