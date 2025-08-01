<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Partner;
use App\Models\Subscription;
use App\Models\UserSubscription;
use App\Models\TenantPartnerRelation;
use App\Models\SupportTicket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class ProductionDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Generating realistic production data for FlotteQ...');

        // Clean existing data first
        $this->command->info('🧹 Cleaning existing data...');
        \DB::statement('DELETE FROM user_subscriptions');
        \DB::statement('DELETE FROM support_tickets');
        \DB::statement('DELETE FROM tenant_partner_relations');
        \DB::statement('DELETE FROM analytics_events');
        \DB::statement('DELETE FROM vehicles');
        \DB::statement('DELETE FROM users WHERE is_internal = 0');
        \DB::statement('DELETE FROM tenants');
        \DB::statement('DELETE FROM partners');
        \DB::statement('DELETE FROM subscriptions');

        // Create subscriptions first
        $this->createSubscriptions();
        
        // Create partners
        $this->createPartners();
        
        // Create tenants with realistic data
        $this->createTenants();
        
        // Create internal users
        $this->createInternalUsers();

        $this->command->info('✅ Production data generation completed!');
    }

    private function createSubscriptions(): void
    {
        $this->command->info('📦 Creating subscription plans...');

        $subscriptions = [
            [
                'name' => 'Starter',
                'description' => 'Parfait pour les petites entreprises jusqu\'à 5 véhicules',
                'price' => 29.99,
                'currency' => 'EUR',
                'billing_cycle' => 'monthly',
                'features' => [
                    'vehicle_management',
                    'basic_maintenance',
                    'basic_reporting',
                    'email_support'
                ],
                'limits' => [
                    'vehicles' => 5,
                    'users' => 3,
                    'support_tickets' => 5
                ],
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Professional',
                'description' => 'Solution complète pour entreprises moyennes jusqu\'à 25 véhicules',
                'price' => 89.99,
                'currency' => 'EUR',
                'billing_cycle' => 'monthly',
                'features' => [
                    'vehicle_management',
                    'advanced_maintenance',
                    'partner_network',
                    'advanced_reporting',
                    'analytics',
                    'priority_support'
                ],
                'limits' => [
                    'vehicles' => 25,
                    'users' => 10,
                    'support_tickets' => 20
                ],
                'is_active' => true,
                'is_popular' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Enterprise',
                'description' => 'Solution sur mesure pour grandes flottes',
                'price' => 199.99,
                'currency' => 'EUR',
                'billing_cycle' => 'monthly',
                'features' => [
                    'vehicle_management',
                    'advanced_maintenance',
                    'partner_network',
                    'custom_reporting',
                    'advanced_analytics',
                    'api_access',
                    'dedicated_support'
                ],
                'limits' => [
                    'vehicles' => null, // Unlimited
                    'users' => null,
                    'support_tickets' => null
                ],
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 3,
            ],
        ];

        foreach ($subscriptions as $sub) {
            Subscription::create($sub);
        }
    }

    private function createPartners(): void
    {
        $this->command->info('🤝 Creating realistic partners...');

        // Garages réels en France
        $garages = [
            [
                'name' => 'Garage Citroën Paris 15',
                'type' => 'garage',
                'description' => 'Concessionnaire et garage agréé Citroën, spécialisé en réparation et entretien',
                'email' => 'contact@citroen-paris15.fr',
                'phone' => '01 45 75 82 94',
                'website' => 'https://www.citroen-paris15.fr',
                'address' => '156 Rue de Vaugirard',
                'city' => 'Paris',
                'postal_code' => '75015',
                'latitude' => 48.8429,
                'longitude' => 2.3108,
                'services' => ['entretien', 'reparation', 'carrosserie', 'pneus'],
                'pricing' => [
                    'vidange' => 45.00,
                    'revision' => 150.00,
                    'pneus' => 80.00
                ],
                'availability' => [
                    'monday' => ['08:00-18:00'],
                    'tuesday' => ['08:00-18:00'],
                    'wednesday' => ['08:00-18:00'],
                    'thursday' => ['08:00-18:00'],
                    'friday' => ['08:00-18:00'],
                    'saturday' => ['08:00-16:00']
                ],
                'rating' => 4.2,
                'rating_count' => 127,
                'is_active' => true,
                'is_verified' => true,
            ],
            [
                'name' => 'Peugeot Lyon Centre',
                'type' => 'garage',
                'description' => 'Garage agréé Peugeot avec service rapide et expertise technique',
                'email' => 'lyon@peugeot-centre.fr',
                'phone' => '04 72 56 89 12',
                'address' => '45 Cours Lafayette',
                'city' => 'Lyon',
                'postal_code' => '69003',
                'latitude' => 45.7640,
                'longitude' => 4.8357,
                'services' => ['entretien', 'diagnostic', 'reparation'],
                'pricing' => [
                    'vidange' => 40.00,
                    'revision' => 140.00,
                    'diagnostic' => 60.00
                ],
                'availability' => [
                    'monday' => ['08:30-18:30'],
                    'tuesday' => ['08:30-18:30'],
                    'wednesday' => ['08:30-18:30'],
                    'thursday' => ['08:30-18:30'],
                    'friday' => ['08:30-18:30']
                ],
                'rating' => 4.5,
                'rating_count' => 93,
                'is_active' => true,
                'is_verified' => true,
            ],
            [
                'name' => 'Auto Service Marseille',
                'type' => 'garage',
                'description' => 'Garage multi-marques spécialisé entretien et réparation toutes marques',
                'email' => 'contact@autoservice-marseille.fr',
                'phone' => '04 91 33 57 42',
                'address' => '89 Avenue du Prado',
                'city' => 'Marseille',
                'postal_code' => '13006',
                'latitude' => 43.2965,
                'longitude' => 5.3698,
                'services' => ['entretien', 'reparation', 'climatisation', 'freins'],
                'pricing' => [
                    'vidange' => 35.00,
                    'freins' => 120.00,
                    'climatisation' => 80.00
                ],
                'availability' => [
                    'monday' => ['08:00-17:30'],
                    'tuesday' => ['08:00-17:30'],
                    'wednesday' => ['08:00-17:30'],
                    'thursday' => ['08:00-17:30'],
                    'friday' => ['08:00-17:30']
                ],
                'rating' => 4.0,
                'rating_count' => 156,
                'is_active' => true,
                'is_verified' => true,
            ],
        ];

        // Centres de contrôle technique
        $centresCT = [
            [
                'name' => 'DEKRA Automotive Paris Nord',
                'type' => 'controle_technique',
                'description' => 'Centre de contrôle technique agréé, contre-visite et diagnostic',
                'email' => 'paris-nord@dekra.fr',
                'phone' => '01 48 25 67 89',
                'website' => 'https://www.dekra-automotive.fr',
                'address' => '25 Boulevard Ney',
                'city' => 'Paris',
                'postal_code' => '75018',
                'latitude' => 48.8975,
                'longitude' => 2.3522,
                'services' => ['controle_technique', 'contre_visite', 'diagnostic_pollution'],
                'pricing' => [
                    'controle_technique' => 78.50,
                    'contre_visite' => 23.50
                ],
                'availability' => [
                    'monday' => ['08:00-18:00'],
                    'tuesday' => ['08:00-18:00'],
                    'wednesday' => ['08:00-18:00'],
                    'thursday' => ['08:00-18:00'],
                    'friday' => ['08:00-18:00'],
                    'saturday' => ['08:00-17:00']
                ],
                'rating' => 4.1,
                'rating_count' => 284,
                'is_active' => true,
                'is_verified' => true,
            ],
            [
                'name' => 'Sécuritest Lyon Villeurbanne',
                'type' => 'controle_technique',
                'description' => 'Centre agréé contrôle technique véhicules légers et utilitaires',
                'email' => 'villeurbanne@securitest.fr',
                'phone' => '04 78 89 45 32',
                'address' => '158 Cours Emile Zola',
                'city' => 'Villeurbanne',
                'postal_code' => '69100',
                'latitude' => 45.7733,
                'longitude' => 4.8794,
                'services' => ['controle_technique', 'contre_visite'],
                'pricing' => [
                    'controle_technique' => 76.90,
                    'contre_visite' => 22.00
                ],
                'availability' => [
                    'monday' => ['08:30-18:30'],
                    'tuesday' => ['08:30-18:30'],
                    'wednesday' => ['08:30-18:30'],
                    'thursday' => ['08:30-18:30'],
                    'friday' => ['08:30-18:30'],
                    'saturday' => ['08:30-16:30']
                ],
                'rating' => 3.9,
                'rating_count' => 198,
                'is_active' => true,
                'is_verified' => true,
            ],
        ];

        // Compagnies d'assurance
        $assurances = [
            [
                'name' => 'AXA Assurance Flotte',
                'type' => 'assurance',
                'description' => 'Solutions d\'assurance dédiées aux flottes d\'entreprise',
                'email' => 'flotte@axa.fr',
                'phone' => '09 78 98 76 54',
                'website' => 'https://www.axa.fr/pro/assurance-flotte',
                'address' => '25 Avenue Matignon',
                'city' => 'Paris',
                'postal_code' => '75008',
                'latitude' => 48.8738,
                'longitude' => 2.3080,
                'services' => ['assurance_flotte', 'assistance_24h', 'gestion_sinistres'],
                'pricing' => [
                    'assurance_base' => 45.00,
                    'assurance_complete' => 89.00
                ],
                'rating' => 4.3,
                'rating_count' => 567,
                'is_active' => true,
                'is_verified' => true,
            ],
            [
                'name' => 'Generali Pro Fleet',
                'type' => 'assurance',
                'description' => 'Assurance professionnelle spécialisée gestion de flottes',
                'email' => 'profleet@generali.fr',
                'phone' => '01 58 38 75 42',
                'website' => 'https://www.generali.fr/professionnel/flotte',
                'address' => '2 Rue Pillet-Will',
                'city' => 'Paris',
                'postal_code' => '75009',
                'latitude' => 48.8719,
                'longitude' => 2.3387,
                'services' => ['assurance_flotte', 'gestion_parc', 'formation_conducteur'],
                'pricing' => [
                    'assurance_essentielle' => 42.00,
                    'assurance_premium' => 78.00
                ],
                'rating' => 4.0,
                'rating_count' => 234,
                'is_active' => true,
                'is_verified' => true,
            ],
        ];

        // Create all partners
        foreach (array_merge($garages, $centresCT, $assurances) as $partner) {
            Partner::create($partner);
        }
    }

    private function createTenants(): void
    {
        $this->command->info('🏢 Creating realistic tenant companies...');

        $companies = [
            [
                'name' => 'FlotteQ Demo',
                'domain' => 'transexpress.local',
                'database' => 'tenant_transexpress',
                'is_active' => true,
                'users_data' => [
                    [
                        'email' => 'wissemkarboubbb@gmail.com',
                        'first_name' => 'Wissem',
                        'last_name' => 'Karboub',
                        'role' => 'admin',
                        'phone' => '+33 6 12 34 56 78',
                        'company' => 'FlotteQ Demo',
                        'fleet_role' => 'Administrateur',
                    ],
                    [
                        'email' => 'flotte@transexpress.fr',
                        'first_name' => 'Sophie',
                        'last_name' => 'Martin',
                        'role' => 'manager',
                        'phone' => '01 42 58 96 33',
                        'company' => 'TransExpress SARL',
                        'fleet_role' => 'Responsable Flotte',
                    ],
                ],
                'subscription_id' => 2, // Professional
                'vehicle_count' => 18,
            ],
            [
                'name' => 'LogiTech Solutions',
                'domain' => 'logitech-solutions.local',
                'database' => 'tenant_logitech',
                'is_active' => true,
                'users_data' => [
                    [
                        'email' => 'admin@logitech-solutions.fr',
                        'first_name' => 'Pierre',
                        'last_name' => 'Bernard',
                        'role' => 'admin',
                        'phone' => '04 72 89 45 67',
                        'company' => 'LogiTech Solutions',
                        'fleet_role' => 'Gérant',
                    ],
                ],
                'subscription_id' => 1, // Starter
                'vehicle_count' => 4,
            ],
            [
                'name' => 'Médical Services Plus',
                'domain' => 'medical-services.local',
                'database' => 'tenant_medical',
                'is_active' => true,
                'users_data' => [
                    [
                        'email' => 'administration@medical-services.fr',
                        'first_name' => 'Dr. Marie',
                        'last_name' => 'Rousseau',
                        'role' => 'admin',
                        'phone' => '03 89 76 54 32',
                        'company' => 'Médical Services Plus',
                        'fleet_role' => 'Administrateur',
                    ],
                    [
                        'email' => 'parc-auto@medical-services.fr',
                        'first_name' => 'Jean',
                        'last_name' => 'Lefevre',
                        'role' => 'user',
                        'phone' => '03 89 76 54 33',
                        'company' => 'Médical Services Plus',
                        'fleet_role' => 'Responsable Parc',
                    ],
                ],
                'subscription_id' => 2, // Professional
                'vehicle_count' => 12,
            ],
            [
                'name' => 'BatiPro Construction',
                'domain' => 'batipro.local',
                'database' => 'tenant_batipro',
                'is_active' => true,
                'users_data' => [
                    [
                        'email' => 'direction@batipro-construction.fr',
                        'first_name' => 'Michel',
                        'last_name' => 'Durand',
                        'role' => 'admin',
                        'phone' => '02 98 45 67 89',
                        'company' => 'BatiPro Construction',
                        'fleet_role' => 'Directeur Général',
                    ],
                ],
                'subscription_id' => 3, // Enterprise
                'vehicle_count' => 35,
            ],
            [
                'name' => 'EcoLivraison',
                'domain' => 'ecolivraison.local',
                'database' => 'tenant_ecolivraison',
                'is_active' => true,
                'users_data' => [
                    [
                        'email' => 'contact@ecolivraison.fr',
                        'first_name' => 'Camille',
                        'last_name' => 'Moreau',
                        'role' => 'admin',
                        'phone' => '05 34 78 92 15',
                        'company' => 'EcoLivraison',
                        'fleet_role' => 'Fondatrice',
                    ],
                ],
                'subscription_id' => 1, // Starter
                'vehicle_count' => 3,
            ],
        ];

        foreach ($companies as $companyData) {
            // Create tenant
            $tenant = Tenant::create([
                'name' => $companyData['name'],
                'domain' => $companyData['domain'],
                'database' => $companyData['database'],
                'is_active' => $companyData['is_active'],
            ]);

            // Create users for this tenant
            foreach ($companyData['users_data'] as $userData) {
                $user = User::create([
                    'tenant_id' => $tenant->id,
                    'email' => $userData['email'],
                    'username' => strtolower($userData['first_name'] . '.' . $userData['last_name']),
                    'first_name' => $userData['first_name'],
                    'last_name' => $userData['last_name'],
                    'password' => Hash::make('demo123'),
                    'role' => $userData['role'],
                    'phone' => $userData['phone'],
                    'company' => $userData['company'],
                    'fleet_role' => $userData['fleet_role'],
                    'is_active' => true,
                    'birthdate' => Carbon::now()->subYears(rand(25, 55)),
                    'gender' => ['M', 'F'][array_rand(['M', 'F'])],
                    'address' => $this->generateRandomAddress(),
                    'city' => ['Paris', 'Lyon', 'Marseille', 'Toulouse', 'Bordeaux'][array_rand(['Paris', 'Lyon', 'Marseille', 'Toulouse', 'Bordeaux'])],
                    'country' => 'France',
                ]);

                // Create subscription - Use first available subscription
                $availableSubscription = Subscription::first();
                if ($availableSubscription) {
                    UserSubscription::create([
                        'tenant_id' => $tenant->id,
                        'subscription_id' => $availableSubscription->id,
                        'start_date' => Carbon::now()->subMonths(rand(1, 12)),
                        'end_date' => Carbon::now()->addYear(),
                        'is_active' => true,
                    ]);
                }

                // Create vehicles for this tenant
                $this->createVehiclesForTenant($tenant, $user, $companyData['vehicle_count']);
            }

            // Create partner relations
            $this->createPartnerRelations($tenant);

            // Create some support tickets
            $this->createSupportTickets($tenant);
        }
    }

    private function createInternalUsers(): void
    {
        $this->command->info('👨‍💼 Creating internal FlotteQ employees...');

        $internalUsers = [
            [
                'email' => 'admin@flotteq.fr',
                'first_name' => 'Alexandre',
                'last_name' => 'Flores',
                'role' => 'admin',
                'role_interne' => 'admin',
                'phone' => '01 75 43 21 89',
            ],
            [
                'email' => 'support@flotteq.fr',
                'first_name' => 'Julie',
                'last_name' => 'Vincent',
                'role' => 'support',
                'role_interne' => 'support',
                'phone' => '01 75 43 21 90',
            ],
            [
                'email' => 'commercial@flotteq.fr',
                'first_name' => 'Thomas',
                'last_name' => 'Garcia',
                'role' => 'commercial',
                'role_interne' => 'commercial',
                'phone' => '01 75 43 21 91',
            ],
        ];

        foreach ($internalUsers as $userData) {
            User::create([
                'email' => $userData['email'],
                'username' => strtolower($userData['first_name'] . '.' . $userData['last_name']),
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'password' => 'admin123', // Will be hashed automatically
                'role' => $userData['role'],
                'is_internal' => true,
                'role_interne' => $userData['role_interne'],
                'phone' => $userData['phone'],
                'is_active' => true,
                'birthdate' => Carbon::now()->subYears(rand(25, 45)),
                'gender' => ['M', 'F'][array_rand(['M', 'F'])],
                'address' => $this->generateRandomAddress(),
                'city' => 'Paris',
                'country' => 'France',
            ]);
        }
    }

    private function createVehiclesForTenant(Tenant $tenant, User $user, int $count): void
    {
        $vehicleTypes = [
            ['marque' => 'Renault', 'modele' => 'Kangoo', 'carburant' => 'diesel'],
            ['marque' => 'Peugeot', 'modele' => 'Partner', 'carburant' => 'diesel'],
            ['marque' => 'Citroën', 'modele' => 'Berlingo', 'carburant' => 'diesel'],
            ['marque' => 'Ford', 'modele' => 'Transit', 'carburant' => 'diesel'],
            ['marque' => 'Mercedes', 'modele' => 'Sprinter', 'carburant' => 'diesel'],
            ['marque' => 'Volkswagen', 'modele' => 'Crafter', 'carburant' => 'essence'],
        ];

        for ($i = 0; $i < $count; $i++) {
            $vehicleType = $vehicleTypes[array_rand($vehicleTypes)];
            $year = rand(2018, 2024);
            
            Vehicle::create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'marque' => $vehicleType['marque'],
                'modele' => $vehicleType['modele'],
                'immatriculation' => $this->generateRegistrationPlate(),
                'vin' => $this->generateVIN(),
                'annee' => $year,
                'couleur' => ['Blanc', 'Gris', 'Noir', 'Bleu', 'Rouge'][array_rand(['Blanc', 'Gris', 'Noir', 'Bleu', 'Rouge'])],
                'kilometrage' => rand(15000, 180000),
                'carburant' => $vehicleType['carburant'],
                'transmission' => 'manuelle',
                'puissance' => rand(90, 160),
                'purchase_date' => Carbon::createFromDate($year, rand(1, 12), rand(1, 28)),
                'purchase_price' => rand(18000, 45000),
                'status' => ['active', 'en_maintenance', 'hors_service'][array_rand(['active', 'en_maintenance', 'hors_service'])],
                'last_ct_date' => Carbon::now()->subMonths(rand(3, 18)),
                'next_ct_date' => Carbon::now()->addMonths(rand(6, 24)),
            ]);
        }
    }

    private function createPartnerRelations(Tenant $tenant): void
    {
        $partners = Partner::active()->take(5)->get();
        
        foreach ($partners as $partner) {
            TenantPartnerRelation::create([
                'tenant_id' => $tenant->id,
                'partner_id' => $partner->id,
                'distance' => rand(5, 50) / 10, // 0.5 to 5.0 km
                'is_preferred' => rand(0, 1) == 1,
                'tenant_rating' => rand(30, 50) / 10, // 3.0 to 5.0
                'booking_count' => rand(0, 15),
                'last_booking_at' => Carbon::now()->subDays(rand(1, 90)),
                'last_interaction_at' => Carbon::now()->subDays(rand(1, 30)),
            ]);
        }
    }

    private function createSupportTickets(Tenant $tenant): void
    {
        $user = $tenant->users()->first();
        $agent = User::where('is_internal', true)->where('role_interne', 'support')->first();
        
        $ticketCount = rand(0, 3);
        
        for ($i = 0; $i < $ticketCount; $i++) {
            $ticket = SupportTicket::create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'agent_id' => $agent ? $agent->id : null,
                'subject' => $this->getRandomTicketSubject(),
                'description' => $this->getRandomTicketDescription(),
                'priority' => ['low', 'medium', 'high'][array_rand(['low', 'medium', 'high'])],
                'status' => ['open', 'in_progress', 'resolved'][array_rand(['open', 'in_progress', 'resolved'])],
                'category' => ['technical', 'billing', 'feature_request'][array_rand(['technical', 'billing', 'feature_request'])],
                'created_at' => Carbon::now()->subDays(rand(1, 30)),
            ]);

            // Add some messages
            $ticket->addMessage([
                'type' => 'user',
                'author_id' => $user->id,
                'author_name' => $user->first_name . ' ' . $user->last_name,
                'content' => $ticket->description,
            ]);

            if ($agent && rand(0, 1)) {
                $ticket->addMessage([
                    'type' => 'agent',
                    'author_id' => $agent->id,
                    'author_name' => $agent->first_name . ' ' . $agent->last_name,
                    'content' => 'Merci pour votre message. Nous examinons votre demande.',
                ]);
            }
        }
    }

    // Helper methods
    private function generateRegistrationPlate(): string
    {
        $letters1 = chr(rand(65, 90)) . chr(rand(65, 90));
        $numbers = str_pad((string)rand(1, 999), 3, '0', STR_PAD_LEFT);
        $letters2 = chr(rand(65, 90)) . chr(rand(65, 90));
        
        return $letters1 . '-' . $numbers . '-' . $letters2;
    }

    private function generateVIN(): string
    {
        return strtoupper(substr(md5(uniqid()), 0, 17));
    }

    private function generateRandomAddress(): string
    {
        $addresses = [
            '15 Rue de la Paix',
            '42 Avenue des Champs-Élysées',
            '7 Boulevard Saint-Germain',
            '23 Rue du Commerce',
            '156 Avenue de la République',
            '89 Rue Victor Hugo',
            '34 Place de la Bastille',
            '67 Rue de Rivoli',
        ];

        return $addresses[array_rand($addresses)];
    }

    private function getRandomTicketSubject(): string
    {
        $subjects = [
            'Problème de synchronisation des données',
            'Question sur la facturation',
            'Demande d\'ajout d\'un véhicule',
            'Erreur lors de la saisie de maintenance',
            'Amélioration de l\'interface',
            'Problème d\'accès utilisateur',
        ];

        return $subjects[array_rand($subjects)];
    }

    private function getRandomTicketDescription(): string
    {
        $descriptions = [
            'Bonjour, je rencontre un problème avec la synchronisation des données de mes véhicules. Pouvez-vous m\'aider ?',
            'J\'aimerais comprendre comment fonctionne la facturation de notre abonnement.',
            'Je souhaiterais ajouter un nouveau véhicule à notre flotte mais je ne trouve pas l\'option.',
            'Une erreur apparaît quand j\'essaie d\'enregistrer une maintenance.',
            'Serait-il possible d\'améliorer l\'ergonomie de l\'interface mobile ?',
            'Un de mes collaborateurs n\'arrive plus à se connecter à son compte.',
        ];

        return $descriptions[array_rand($descriptions)];
    }
}