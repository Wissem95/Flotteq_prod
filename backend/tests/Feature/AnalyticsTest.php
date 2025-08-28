<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->tenant = Tenant::create([
            'name' => 'Test Company',
            'domain' => 'test.flotteq.local',
            'database' => 'test_company_db',
        ]);
        
        // Set tenant context (makeCurrent removed)
        app()->instance('currentTenant', $this->tenant);
        
        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'admin',
        ]);
        
        $this->user->givePermissionTo([
            'view statistics',
            'view vehicles',
            'view users',
        ]);
    }

    public function test_get_dashboard_stats_successfully(): void
    {
        // Create some test data
        $users = User::factory()->count(5)->create(['tenant_id' => $this->tenant->id]);
        $vehicles = Vehicle::factory()->count(10)->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $users->random()->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/analytics/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'overview' => [
                    'total_vehicles',
                    'total_users',
                    'active_vehicles',
                    'inactive_vehicles',
                    'new_vehicles_this_month',
                    'new_users_this_month',
                    'average_vehicle_age',
                ],
                'distributions' => [
                    'vehicles_by_status',
                    'vehicles_by_fuel',
                ],
                'generated_at'
            ]);

        $this->assertEquals(10, $response->json('overview.total_vehicles')); // 10 created
        $this->assertEquals(6, $response->json('overview.total_users')); // 5 + 1 from setup
    }

    public function test_get_vehicle_stats_with_filtering(): void
    {
        // Create vehicles with different attributes
        Vehicle::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'marque' => 'Toyota',
            'annee' => 2020,
            'carburant' => 'essence',
        ]);
        
        Vehicle::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'marque' => 'Toyota',
            'annee' => 2018,
            'carburant' => 'diesel',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/analytics/vehicles?period=30&group_by=day');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'trends' => [
                    'vehicle_creation',
                ],
                'rankings' => [
                    'top_brands',
                ],
                'distributions' => [
                    'age_ranges',
                ],
                'mileage_stats' => [
                    'average',
                    'minimum',
                    'maximum',
                    'total_vehicles_with_mileage',
                ],
                'period_days',
                'group_by',
                'generated_at'
            ]);

        $this->assertEquals(30, $response->json('period_days'));
        $this->assertEquals('day', $response->json('group_by'));
    }

    public function test_get_user_stats_successfully(): void
    {
        // Create users with different attributes
        User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'google_id' => '123456789',
            'role' => 'admin',
        ]);
        
        User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'google_id' => null,
            'role' => 'user',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/analytics/users?period=30');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'overview' => [
                    'total_users',
                    'active_users',
                    'users_with_google',
                    'google_adoption_rate',
                ],
                'trends' => [
                    'user_registrations',
                ],
                'distributions' => [
                    'users_by_role',
                    'vehicle_ownership',
                ],
                'period_days',
                'generated_at'
            ]);

        $this->assertEquals(30, $response->json('period_days'));
        $this->assertGreaterThan(0, $response->json('overview.total_users'));
    }

    public function test_get_system_health_successfully(): void
    {
        // Create some test data
        $vehicle = Vehicle::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
        ]);

        // Add media to vehicle
        $file = UploadedFile::fake()->image('test.jpg');
        $vehicle->addMedia($file)->toMediaCollection('images');

        $response = $this->actingAs($this->user)
            ->getJson('/api/analytics/system');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'database' => [
                    'total_vehicles',
                    'total_users',
                    'total_media_files',
                ],
                'storage' => [
                    'total_size_bytes',
                    'total_size_mb',
                    'files_count',
                ],
                'activity' => [
                    'vehicles_created_today',
                    'users_created_today',
                ],
                'data_quality' => [
                    'vehicles_with_images',
                    'vehicles_with_complete_info',
                    'users_with_complete_profiles',
                ],
                'generated_at'
            ]);
    }

    public function test_export_analytics_json_format(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/analytics/export', [
                'type' => 'dashboard',
                'format' => 'json'
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'export_data',
                'export_type',
                'export_format',
                'exported_at'
            ]);

        $this->assertEquals('dashboard', $response->json('export_type'));
        $this->assertEquals('json', $response->json('export_format'));
    }

    public function test_export_analytics_csv_format(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/analytics/export', [
                'type' => 'vehicles',
                'format' => 'csv'
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'export_data',
                'export_type',
                'export_format',
                'exported_at'
            ]);

        $this->assertEquals('vehicles', $response->json('export_type'));
        $this->assertEquals('csv', $response->json('export_format'));
        $this->assertIsArray($response->json('export_data'));
    }

    public function test_export_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/analytics/export', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    public function test_export_validates_type_enum(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/analytics/export', [
                'type' => 'invalid_type'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    public function test_vehicle_stats_validates_period(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/analytics/vehicles?period=invalid');

        $response->assertStatus(422);
    }

    public function test_vehicle_stats_validates_group_by(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/analytics/vehicles?group_by=invalid');

        $response->assertStatus(422);
    }

    public function test_analytics_requires_authentication(): void
    {
        $response = $this->getJson('/api/analytics/dashboard');
        $response->assertStatus(401);

        $response = $this->getJson('/api/analytics/vehicles');
        $response->assertStatus(401);

        $response = $this->getJson('/api/analytics/users');
        $response->assertStatus(401);

        $response = $this->getJson('/api/analytics/system');
        $response->assertStatus(401);

        $response = $this->postJson('/api/analytics/export');
        $response->assertStatus(401);
    }

    public function test_analytics_isolates_by_tenant(): void
    {
        // Create another tenant with data
        $otherTenant = Tenant::create([
            'name' => 'Other Company',
            'domain' => 'other.flotteq.local',
            'database' => 'other_company_db',
        ]);

        $otherUser = User::factory()->create(['tenant_id' => $otherTenant->id]);
        Vehicle::factory()->count(5)->create([
            'tenant_id' => $otherTenant->id,
            'user_id' => $otherUser->id,
        ]);

        // Get stats for current tenant
        $response = $this->actingAs($this->user)
            ->getJson('/api/analytics/dashboard');

        $response->assertStatus(200);
        
        // Should not include other tenant's data
        $totalVehicles = $response->json('overview.total_vehicles');
        $this->assertLessThan(5, $totalVehicles); // Should not include the 5 from other tenant
    }

    public function test_dashboard_stats_with_empty_data(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/analytics/dashboard');

        $response->assertStatus(200);
        
        // Should handle empty data gracefully
        $this->assertIsNumeric($response->json('overview.total_vehicles'));
        $this->assertIsNumeric($response->json('overview.total_users'));
        $this->assertIsNumeric($response->json('overview.average_vehicle_age'));
    }

    public function test_vehicle_stats_calculates_mileage_correctly(): void
    {
        Vehicle::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'kilometrage' => 50000,
        ]);
        
        Vehicle::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'kilometrage' => 100000,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/analytics/vehicles');

        $response->assertStatus(200);
        
        $mileageStats = $response->json('mileage_stats');
        $this->assertEquals(75000, $mileageStats['average']);
        $this->assertEquals(50000, $mileageStats['minimum']);
        $this->assertEquals(100000, $mileageStats['maximum']);
        $this->assertEquals(2, $mileageStats['total_vehicles_with_mileage']);
    }
}
