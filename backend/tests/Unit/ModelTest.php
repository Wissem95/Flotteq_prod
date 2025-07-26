<?php

use App\Models\User;
use App\Models\Vehicle;
use App\Models\Tenant;
use App\Models\Maintenance;
use App\Models\Repair;
use App\Models\Piece;
use App\Models\Invoice;
use App\Models\TechnicalInspection;

describe('Model Relationships and Logic', function () {
    beforeEach(function () {
        $this->tenant = createTenant([
            'name' => 'Demo Corp',
            'domain' => 'demo.flotteq.local'
        ]);
        
        actingAsTenant($this->tenant);
    });

    describe('User Model', function () {
        it('can create a user with proper attributes', function () {
            $user = createUser([
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@demo.com',
                'password' => 'password123'
            ]);

            expect($user->first_name)->toBe('John');
            expect($user->last_name)->toBe('Doe');
            expect($user->email)->toBe('john@demo.com');
            expect($user->password)->not->toBe('password123'); // Should be hashed
        });

        it('hashes password when creating user', function () {
            $tenant = createTenant();
            
            $user = User::create([
                'email' => 'test@example.com',
                'username' => 'testuser', 
                'first_name' => 'Test',
                'last_name' => 'User',
                'password' => 'plaintext',
                'tenant_id' => $tenant->id,
                'role' => 'user',
                'is_active' => true
            ]);

            expect($user->password)->not->toBe('plaintext');
            expect(password_verify('plaintext', $user->password))->toBeTrue();
        });

        it('has many vehicles relationship', function () {
            $user = createUser();
            $vehicle1 = createVehicle(['user_id' => $user->id]);
            $vehicle2 = createVehicle(['user_id' => $user->id]);

            expect($user->vehicles)->toHaveCount(2);
            expect($user->vehicles->first()->id)->toBe($vehicle1->id);
        });
    });

    describe('Vehicle Model', function () {
        it('belongs to a user', function () {
            $user = createUser();
            $vehicle = createVehicle(['user_id' => $user->id]);

            expect($vehicle->user->id)->toBe($user->id);
            expect($vehicle->user->first_name)->toBe($user->first_name);
        });

        it('has fillable attributes', function () {
            $user = createUser();
            $data = [
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
                'immatriculation' => 'AB-123-CD',
                'marque' => 'Peugeot',
                'modele' => '208',
                'annee' => 2023,
                'carburant' => 'essence',
                'transmission' => 'manuelle',
                'kilometrage' => 15000
            ];

            $vehicle = Vehicle::create($data);

            expect($vehicle->immatriculation)->toBe('AB-123-CD');
            expect($vehicle->marque)->toBe('Peugeot');
            expect($vehicle->annee)->toBe(2023);
        });

        it('can have multiple invoices', function () {
            $vehicle = createVehicle();
            
            $invoice1 = Invoice::factory()->create(['vehicle_id' => $vehicle->id]);
            $invoice2 = Invoice::factory()->create(['vehicle_id' => $vehicle->id]);

            expect($vehicle->invoices)->toHaveCount(2);
        });

        it('can have multiple maintenances', function () {
            $vehicle = createVehicle();
            
            $maintenance1 = Maintenance::factory()->create(['vehicle_id' => $vehicle->id]);
            $maintenance2 = Maintenance::factory()->create(['vehicle_id' => $vehicle->id]);

            expect($vehicle->maintenances)->toHaveCount(2);
        });

        it('can have multiple repairs', function () {
            $vehicle = createVehicle();
            
            $repair1 = Repair::factory()->create(['vehicle_id' => $vehicle->id]);
            $repair2 = Repair::factory()->create(['vehicle_id' => $vehicle->id]);

            expect($vehicle->repairs)->toHaveCount(2);
        });

        it('can have multiple technical inspections', function () {
            $vehicle = createVehicle();
            
            $ti1 = TechnicalInspection::factory()->create(['vehicle_id' => $vehicle->id]);
            $ti2 = TechnicalInspection::factory()->create(['vehicle_id' => $vehicle->id]);

            expect($vehicle->technicalInspections)->toHaveCount(2);
        });
    });

    describe('Basic Model Creation', function () {
        it('can create basic models with factories', function () {
            $user = User::factory()->create();
            $vehicle = Vehicle::factory()->create();
            $piece = Piece::factory()->create();

            expect($user->id)->toBeGreaterThan(0);
            expect($vehicle->id)->toBeGreaterThan(0);
            expect($piece->id)->toBeGreaterThan(0);
        });
    });
}); 