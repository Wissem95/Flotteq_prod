<?php

use App\Models\User;
use App\Models\Tenant;
use App\Models\Vehicle;

describe('Vehicle Management', function () {
    beforeEach(function () {
        $this->tenant = createTenant([
            'name' => 'Demo Corp',
            'domain' => 'demo.flotteq.local'
        ]);
        
        actingAsTenant($this->tenant);
        
        $this->user = createUser([
            'email' => 'admin@demo.flotteq.local',
            'password' => bcrypt('password')
        ]);
    });

    describe('Vehicle CRUD Operations', function () {
        it('can list all vehicles for authenticated user', function () {
            actingAsUser($this->user);
            
            createVehicle(['immatriculation' => 'AB-123-CD']);
            createVehicle(['immatriculation' => 'EF-456-GH']);

            $response = $this->getJson('/api/vehicles');

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'immatriculation',
                            'marque',
                            'modele',
                            'created_at'
                        ]
                    ]
                ]);

            expect($response->json('data'))->toHaveCount(2);
        });

        it('can create a new vehicle', function () {
            actingAsUser($this->user);

            $vehicleData = [
                'immatriculation' => 'AB-123-CD',
                'marque' => 'Peugeot',
                'modele' => '208',
                'annee' => 2023,
                'carburant' => 'essence',
                'transmission' => 'manuelle',
                'kilometrage' => 15000,
                'vin' => 'VF3XXXXXXXXX12345'
            ];

            $response = $this->postJson('/api/vehicles', $vehicleData);

            $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'vehicle' => [
                        'id',
                        'immatriculation',
                        'marque',
                        'modele',
                        'annee'
                    ]
                ]);

            $this->assertDatabaseHas('vehicles', [
                'immatriculation' => 'AB-123-CD',
                'marque' => 'Peugeot',
                'modele' => '208'
            ]);
        });

        it('validates required fields when creating vehicle', function () {
            actingAsUser($this->user);

            $response = $this->postJson('/api/vehicles', []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'immatriculation',
                    'marque',
                    'modele',
                    'annee',
                    'carburant',
                    'transmission'
                ]);
        });

        it('validates unique immatriculation', function () {
            actingAsUser($this->user);

            createVehicle(['immatriculation' => 'XY-999-ZZ']);

            $vehicleData = [
                'immatriculation' => 'XY-999-ZZ',
                'marque' => 'Renault',
                'modele' => 'Clio',
                'annee' => 2022,
                'carburant' => 'diesel',
                'transmission' => 'manuelle',
                'kilometrage' => 10000
            ];

            $response = $this->postJson('/api/vehicles', $vehicleData);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['immatriculation']);
        });

        it('can show a specific vehicle', function () {
            actingAsUser($this->user);
            
            $vehicle = createVehicle([
                'immatriculation' => 'AB-123-CD',
                'marque' => 'Peugeot'
            ]);

            $response = $this->getJson("/api/vehicles/{$vehicle->id}");

            $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $vehicle->id,
                        'immatriculation' => 'AB-123-CD',
                        'marque' => 'Peugeot'
                    ]
                ]);
        });

        it('returns 404 for non-existent vehicle', function () {
            actingAsUser($this->user);

            $response = $this->getJson('/api/vehicles/999');

            $response->assertStatus(404);
        });

        it('can update a vehicle', function () {
            actingAsUser($this->user);
            
            $vehicle = createVehicle([
                'immatriculation' => 'AB-123-CD',
                'kilometrage' => 10000
            ]);

            $updateData = [
                'kilometrage' => 15000,
                'marque' => 'Peugeot Updated'
            ];

            $response = $this->putJson("/api/vehicles/{$vehicle->id}", $updateData);

            $response->assertStatus(200)
                ->assertJsonFragment([
                    'kilometrage' => 15000,
                    'marque' => 'Peugeot Updated'
                ]);

            $this->assertDatabaseHas('vehicles', [
                'id' => $vehicle->id,
                'kilometrage' => 15000,
                'marque' => 'Peugeot Updated'
            ]);
        });

        it('can delete a vehicle', function () {
            actingAsUser($this->user);
            
            $vehicle = createVehicle();

            $response = $this->deleteJson("/api/vehicles/{$vehicle->id}");

            $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Vehicle deleted successfully'
                ]);

            $this->assertDatabaseMissing('vehicles', [
                'id' => $vehicle->id
            ]);
        });
    });

    describe('Vehicle Validation', function () {
        beforeEach(function () {
            actingAsUser($this->user);
        });

        it('validates immatriculation format', function () {
            $vehicleData = [
                'immatriculation' => 'INVALID',
                'marque' => 'Peugeot',
                'modele' => '208',
                'annee' => 2023,
                'carburant' => 'essence',
                'transmission' => 'manuelle',
                'kilometrage' => 15000
            ];

            $response = $this->postJson('/api/vehicles', $vehicleData);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['immatriculation']);
        });

        it('validates year range', function () {
            $vehicleData = [
                'immatriculation' => 'AB-123-CD',
                'marque' => 'Peugeot',
                'modele' => '208',
                'annee' => 1800, // Invalid year
                'carburant' => 'essence',
                'transmission' => 'manuelle',
                'kilometrage' => 15000
            ];

            $response = $this->postJson('/api/vehicles', $vehicleData);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['annee']);
        });

        it('validates carburant enum', function () {
            $vehicleData = [
                'immatriculation' => 'AB-123-CD',
                'marque' => 'Peugeot',
                'modele' => '208',
                'annee' => 2023,
                'carburant' => 'unknown_fuel',
                'transmission' => 'manuelle',
                'kilometrage' => 15000
            ];

            $response = $this->postJson('/api/vehicles', $vehicleData);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['carburant']);
        });
    });

    describe('Multi-tenancy Isolation', function () {
        it('isolates vehicles by tenant', function () {
            // Create vehicle in current tenant
            actingAsUser($this->user);
            $vehicleInCurrentTenant = createVehicle(['immatriculation' => 'AB-123-CD']);

            // Create another tenant
            $otherTenant = createTenant([
                'name' => 'Other Corp',
                'domain' => 'other.flotteq.local'
            ]);
            
            actingAsTenant($otherTenant);
            $userInOtherTenant = createUser(['email' => 'user@other.flotteq.local']);
            $vehicleInOtherTenant = createVehicle(['immatriculation' => 'EF-456-GH']);

            // Switch back to original tenant
            actingAsTenant($this->tenant);
            actingAsUser($this->user);

            // Should only see vehicles from current tenant
            $response = $this->getJson('/api/vehicles');

            $response->assertStatus(200);
            
            $vehicles = $response->json('data');
            expect($vehicles)->toHaveCount(1);
            expect($vehicles[0]['immatriculation'])->toBe('AB-123-CD');
        });

        it('cannot access vehicle from other tenant', function () {
            // Create vehicle in other tenant
            $otherTenant = createTenant([
                'name' => 'Other Corp',
                'domain' => 'other.flotteq.local'
            ]);
            
            actingAsTenant($otherTenant);
            $userInOtherTenant = createUser(['email' => 'user@other.flotteq.local']);
            $vehicleInOtherTenant = createVehicle(['immatriculation' => 'EF-456-GH']);

            // Switch to original tenant and try to access other tenant's vehicle
            actingAsTenant($this->tenant);
            actingAsUser($this->user);

            $response = $this->getJson("/api/vehicles/{$vehicleInOtherTenant->id}");

            $response->assertStatus(403);
        });
    });

    describe('Authentication Requirements', function () {
        it('requires authentication for all vehicle endpoints', function () {
            $endpoints = [
                ['GET', '/api/vehicles'],
                ['POST', '/api/vehicles'],
                ['GET', '/api/vehicles/1'],
                ['PUT', '/api/vehicles/1'],
                ['DELETE', '/api/vehicles/1']
            ];

            foreach ($endpoints as [$method, $url]) {
                $response = $this->json($method, $url);
                $response->assertStatus(401);
            }
        });
    });
}); 