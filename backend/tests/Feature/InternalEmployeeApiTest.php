<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class InternalEmployeeApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $clientUser;

    protected function setUp(): void
    {
        parent::setUp();
        // Crée un super-admin interne
        $this->superAdmin = User::factory()->create([
            'is_internal' => true,
            'role_interne' => 'admin',
            'role' => 'admin',
            'tenant_id' => null,
            'is_active' => true,
        ]);
        // Crée un user client classique
        $this->clientUser = User::factory()->create([
            'is_internal' => false,
            'role' => 'user',
        ]);
    }

    public function test_super_admin_can_crud_internal_employees(): void
    {
        $token = $this->superAdmin->createToken('test')->plainTextToken;
        $headers = ['Authorization' => 'Bearer ' . $token];

        // CREATE
        $payload = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@flotteq.com',
            'username' => 'johndoe',
            'password' => 'password123',
            'role_interne' => 'support',
        ];
        $response = $this->postJson('/api/admin/employes', $payload, $headers);
        $response->assertStatus(201);
        $id = $response->json('id');
        $this->assertDatabaseHas('users', [
            'id' => $id,
            'is_internal' => true,
            'role_interne' => 'support',
        ]);

        // READ
        $response = $this->getJson('/api/admin/employes', $headers);
        $response->assertStatus(200)
            ->assertJsonFragment(['email' => 'john.doe@flotteq.com']);

        // UPDATE
        $response = $this->putJson(
            "/api/admin/employes/{$id}",
            ['first_name' => 'Jane', 'role_interne' => 'commercial'],
            $headers
        );
        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $id,
            'first_name' => 'Jane',
            'role_interne' => 'commercial',
        ]);

        // DELETE
        $response = $this->deleteJson("/api/admin/employes/{$id}", [], $headers);
        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', [
            'id' => $id,
        ]);
    }

    public function test_client_user_cannot_access_internal_employee_api(): void
    {
        $token = $this->clientUser->createToken('test')->plainTextToken;
        $headers = ['Authorization' => 'Bearer ' . $token];

        $response = $this->getJson('/api/admin/employes', $headers);
        $response->assertStatus(403);

        $response = $this->postJson('/api/admin/employes', [
            'first_name' => 'X',
            'last_name' => 'Y',
            'email' => 'x@y.com',
            'username' => 'xy',
            'password' => 'password123',
            'role_interne' => 'support',
        ], $headers);
        $response->assertStatus(403);
    }

    public function test_validation_errors_on_create(): void
    {
        $token = $this->superAdmin->createToken('test')->plainTextToken;
        $headers = ['Authorization' => 'Bearer ' . $token];

        $response = $this->postJson('/api/admin/employes', [
            'first_name' => '',
            'email' => 'not-an-email',
            'username' => '',
            'password' => 'short',
            'role_interne' => '',
        ], $headers);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['first_name', 'last_name', 'email', 'username', 'password', 'role_interne']);
    }
}
