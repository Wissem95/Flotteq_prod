<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing subscriptions
        DB::table('subscriptions')->truncate();

        // Starter Plan
        Subscription::create([
            'name' => 'Starter',
            'description' => 'Plan idéal pour les petites flottes et les entrepreneurs individuels',
            'price' => 29.99,
            'currency' => 'EUR',
            'billing_cycle' => 'monthly',
            'features' => [
                'Gestion jusqu\'à 5 véhicules',
                'Jusqu\'à 3 utilisateurs',
                'Tableau de bord analytique',
                'Alertes de maintenance',
                'Support par email',
                'Rapports mensuels',
                'Application mobile'
            ],
            'limits' => [
                'vehicles' => 5,
                'users' => 3,
                'support_tickets' => 5,
                'api_requests' => 1000,
                'storage_gb' => 5
            ],
            'is_active' => true,
            'is_popular' => false,
            'sort_order' => 1,
            'metadata' => [
                'pricing' => [
                    'monthly' => 29.99,
                    'yearly' => 287.90
                ],
                'badge' => 'Essentiel',
                'color' => '#6B7280'
            ]
        ]);

        // Professional Plan
        Subscription::create([
            'name' => 'Professional',
            'description' => 'Solution complète pour les entreprises en croissance',
            'price' => 79.99,
            'currency' => 'EUR',
            'billing_cycle' => 'monthly',
            'features' => [
                'Gestion jusqu\'à 25 véhicules',
                'Jusqu\'à 10 utilisateurs',
                'Tableau de bord analytique avancé',
                'Alertes de maintenance personnalisées',
                'Support prioritaire par chat',
                'Rapports hebdomadaires et mensuels',
                'Application mobile',
                'Intégrations API',
                'Gestion des documents',
                'Planification automatique',
                'Export de données'
            ],
            'limits' => [
                'vehicles' => 25,
                'users' => 10,
                'support_tickets' => 20,
                'api_requests' => 10000,
                'storage_gb' => 25
            ],
            'is_active' => true,
            'is_popular' => true,
            'sort_order' => 2,
            'metadata' => [
                'pricing' => [
                    'monthly' => 79.99,
                    'yearly' => 767.90
                ],
                'badge' => 'Populaire',
                'color' => '#8B5CF6',
                'discount_percentage' => 20
            ]
        ]);

        // Enterprise Plan
        Subscription::create([
            'name' => 'Enterprise',
            'description' => 'Solution sur mesure pour les grandes entreprises avec des besoins spécifiques',
            'price' => 199.99,
            'currency' => 'EUR',
            'billing_cycle' => 'monthly',
            'features' => [
                'Véhicules illimités',
                'Utilisateurs illimités',
                'Tableau de bord personnalisable',
                'Alertes et workflows personnalisés',
                'Support dédié 24/7',
                'Rapports en temps réel',
                'Application mobile avec branding',
                'API complète et webhooks',
                'Gestion documentaire avancée',
                'Intelligence artificielle prédictive',
                'Formation personnalisée',
                'SLA garanti 99.9%',
                'Sauvegarde et récupération',
                'Conformité RGPD avancée',
                'Audit de sécurité trimestriel'
            ],
            'limits' => [
                'vehicles' => -1, // -1 means unlimited
                'users' => -1,
                'support_tickets' => -1,
                'api_requests' => -1,
                'storage_gb' => -1
            ],
            'is_active' => true,
            'is_popular' => false,
            'sort_order' => 3,
            'metadata' => [
                'pricing' => [
                    'monthly' => 199.99,
                    'yearly' => 1919.90
                ],
                'badge' => 'Premium',
                'color' => '#EF4444',
                'custom_pricing' => true,
                'contact_sales' => true
            ]
        ]);

        // Trial Plan (optional - for free trials)
        Subscription::create([
            'name' => 'Trial',
            'description' => 'Essai gratuit de 14 jours avec accès complet aux fonctionnalités Professional',
            'price' => 0,
            'currency' => 'EUR',
            'billing_cycle' => 'monthly',
            'features' => [
                'Toutes les fonctionnalités Professional',
                'Durée limitée à 14 jours',
                'Pas de carte de crédit requise',
                'Support par email',
                'Formation vidéo incluse'
            ],
            'limits' => [
                'vehicles' => 10,
                'users' => 5,
                'support_tickets' => 3,
                'api_requests' => 1000,
                'storage_gb' => 2,
                'trial_days' => 14
            ],
            'is_active' => true,
            'is_popular' => false,
            'sort_order' => 0,
            'metadata' => [
                'is_trial' => true,
                'convert_to' => 'Professional',
                'badge' => 'Essai gratuit',
                'color' => '#10B981'
            ]
        ]);

        $this->command->info('Subscription plans seeded successfully!');
    }
}