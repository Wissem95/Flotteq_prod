<?php

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;

describe('Authentication', function () {
    beforeEach(function () {
        $this->tenant = createTenant([
            'name' => 'Demo Corp',
            'domain' => 'demo.flotteq.local'
        ]);

        actingAsTenant($this->tenant);
    });

    describe('Registration', function () {
        it('can register a new user', function () {
            $userData = [
                'email' => 'john@demo.flotteq.local',
                'username' => 'johndoe',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'company_name' => 'Test Corp',
                'domain' => 'test.flotteq.local'
            ];

            $response = $this->postJson('/api/auth/register', $userData);

            $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'user' => ['id', 'email', 'username', 'first_name', 'last_name', 'role'],
                    'tenant' => ['id', 'name', 'domain'],
                    'token'
                ]);

            $this->assertDatabaseHas('users', [
                'email' => 'john@demo.flotteq.local',
                'username' => 'johndoe',
                'first_name' => 'John',
                'last_name' => 'Doe'
            ]);
        });

        it('requires valid email', function () {
            $userData = [
                'name' => 'John Doe',
                'email' => 'invalid-email',
                'password' => 'password123',
                'password_confirmation' => 'password123'
            ];

            $response = $this->postJson('/api/auth/register', $userData);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });

        it('requires password confirmation', function () {
            $userData = [
                'name' => 'John Doe',
                'email' => 'john@demo.flotteq.local',
                'password' => 'password123',
                'password_confirmation' => 'different-password'
            ];

            $response = $this->postJson('/api/auth/register', $userData);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
        });

        it('prevents duplicate email registration', function () {
            createUser(['email' => 'john@demo.flotteq.local']);

            $userData = [
                'name' => 'John Doe',
                'email' => 'john@demo.flotteq.local',
                'password' => 'password123',
                'password_confirmation' => 'password123'
            ];

            $response = $this->postJson('/api/auth/register', $userData);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });
    });

    describe('Login', function () {
        beforeEach(function () {
            $this->user = createUser([
                'email' => 'admin@demo.flotteq.local',
                'password' => 'password'
            ]);
        });

        it('can login with valid credentials', function () {
            $response = $this->postJson('/api/auth/login', [
                'login' => 'admin@demo.flotteq.local',
                'password' => 'password'
            ]);

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'user' => ['id', 'email', 'username', 'first_name', 'last_name', 'role'],
                    'token'
                ]);
        });

        it('rejects invalid credentials', function () {
            $response = $this->postJson('/api/auth/login', [
                'login' => 'admin@demo.flotteq.local',
                'password' => 'wrong-password'
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['login']);
        });

        it('requires email and password', function () {
            $response = $this->postJson('/api/auth/login', []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['login', 'password']);
        });

        it('rejects non-existent user', function () {
            $response = $this->postJson('/api/auth/login', [
                'login' => 'nonexistent@demo.flotteq.local',
                'password' => 'password'
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['login']);
        });
    });

    describe('Protected Routes', function () {
        beforeEach(function () {
            $this->user = createUser();
        });

        it('can access user profile when authenticated', function () {
            actingAsUser($this->user);

            $response = $this->getJson('/api/auth/me');

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'user' => ['id', 'email', 'username', 'first_name', 'last_name', 'role'],
                    'tenant' => ['id', 'name', 'domain']
                ]);
        });

        it('cannot access protected routes without authentication', function () {
            $response = $this->getJson('/api/auth/me');

            $response->assertStatus(401);
        });

        it('can logout successfully', function () {
            actingAsUser($this->user);

            $response = $this->postJson('/api/auth/logout');

            $response->assertStatus(200)
                ->assertJson(['message' => 'Logged out successfully']);
        });
    });

    describe('Multi-tenancy', function () {
        it('isolates users by tenant', function () {
            // Try to login with user from another tenant
            $response = $this->postJson('/api/auth/login', [
                'login' => 'user2@other.flotteq.local',
                'password' => 'password',
                'domain' => 'demo.flotteq.local' // Wrong domain for this user
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['login']);
        });
    });
});
