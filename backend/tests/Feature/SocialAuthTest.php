<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class SocialAuthTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->tenant = Tenant::create([
            'name' => 'Test Company',
            'domain' => 'test.flotteq.local',
            'database' => 'test_company_db',
        ]);
    }

    public function test_redirect_to_google_returns_auth_url(): void
    {
        $response = $this->postJson('/api/auth/google/redirect', [
            'tenant_domain' => $this->tenant->domain
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'auth_url',
                'state'
            ]);

        $this->assertStringContainsString('https://accounts.google.com', $response->json('auth_url'));
    }

    public function test_redirect_to_google_requires_valid_tenant(): void
    {
        $response = $this->postJson('/api/auth/google/redirect', [
            'tenant_domain' => 'invalid.domain'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tenant_domain']);
    }

    public function test_google_callback_creates_new_user(): void
    {
        // Mock Socialite
        $googleUser = Mockery::mock(SocialiteUser::class);
        $googleUser->shouldReceive('getId')->andReturn('123456789');
        $googleUser->shouldReceive('getEmail')->andReturn('john.doe@example.com');
        $googleUser->shouldReceive('getName')->andReturn('John Doe');
        $googleUser->shouldReceive('getAvatar')->andReturn('https://avatar.url/photo.jpg');

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn(Mockery::self())
            ->shouldReceive('stateless')
            ->andReturn(Mockery::self())
            ->shouldReceive('user')
            ->andReturn($googleUser);

        // Prepare state
        $state = base64_encode(json_encode([
            'tenant_id' => $this->tenant->id,
            'tenant_domain' => $this->tenant->domain,
            'csrf_token' => 'test-token'
        ]));

        $response = $this->getJson("/api/auth/google/callback?state={$state}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'email', 'username', 'first_name', 'last_name', 'avatar'],
                'token',
                'tenant' => ['id', 'name', 'domain']
            ]);

        // Verify user was created
        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@example.com',
            'google_id' => '123456789',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'tenant_id' => $this->tenant->id,
        ]);

        // Verify user has default permissions
        $user = User::where('email', 'john.doe@example.com')->first();
        $this->assertTrue($user->hasPermissionTo('view vehicles'));
        $this->assertTrue($user->hasPermissionTo('create vehicles'));
    }

    public function test_google_callback_updates_existing_user(): void
    {
        // Create existing user
        $user = User::factory()->create([
            'email' => 'john.doe@example.com',
            'tenant_id' => $this->tenant->id,
            'google_id' => null,
        ]);

        // Mock Socialite
        $googleUser = Mockery::mock(SocialiteUser::class);
        $googleUser->shouldReceive('getId')->andReturn('123456789');
        $googleUser->shouldReceive('getEmail')->andReturn('john.doe@example.com');
        $googleUser->shouldReceive('getName')->andReturn('John Doe Updated');
        $googleUser->shouldReceive('getAvatar')->andReturn('https://avatar.url/new-photo.jpg');

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn(Mockery::self())
            ->shouldReceive('stateless')
            ->andReturn(Mockery::self())
            ->shouldReceive('user')
            ->andReturn($googleUser);

        $state = base64_encode(json_encode([
            'tenant_id' => $this->tenant->id,
            'tenant_domain' => $this->tenant->domain,
            'csrf_token' => 'test-token'
        ]));

        $response = $this->getJson("/api/auth/google/callback?state={$state}");

        $response->assertStatus(200);

        // Verify user was updated
        $user->refresh();
        $this->assertEquals('123456789', $user->google_id);
        $this->assertEquals('https://avatar.url/new-photo.jpg', $user->avatar);
    }

    public function test_google_callback_fails_with_invalid_state(): void
    {
        $response = $this->getJson('/api/auth/google/callback?state=invalid');

        $response->assertStatus(400)
            ->assertJson(['error' => 'Invalid state data']);
    }

    public function test_link_google_account_to_authenticated_user(): void
    {
        $user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $this->tenant->makeCurrent();

        // Mock Socialite
        $googleUser = Mockery::mock(SocialiteUser::class);
        $googleUser->shouldReceive('getId')->andReturn('987654321');
        $googleUser->shouldReceive('getAvatar')->andReturn('https://avatar.url/photo.jpg');

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn(Mockery::self())
            ->shouldReceive('stateless')
            ->andReturn(Mockery::self())
            ->shouldReceive('user')
            ->andReturn($googleUser);

        $response = $this->actingAs($user)
            ->postJson('/api/auth/google/link');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Google account linked successfully'
            ]);

        $user->refresh();
        $this->assertEquals('987654321', $user->google_id);
        $this->assertEquals('https://avatar.url/photo.jpg', $user->avatar);
    }

    public function test_cannot_link_google_account_already_used_by_another_user(): void
    {
        $existingUser = User::factory()->create([
            'google_id' => '111111111',
            'tenant_id' => $this->tenant->id,
        ]);

        $currentUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $this->tenant->makeCurrent();

        // Mock Socialite
        $googleUser = Mockery::mock(SocialiteUser::class);
        $googleUser->shouldReceive('getId')->andReturn('111111111');

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn(Mockery::self())
            ->shouldReceive('stateless')
            ->andReturn(Mockery::self())
            ->shouldReceive('user')
            ->andReturn($googleUser);

        $response = $this->actingAs($currentUser)
            ->postJson('/api/auth/google/link');

        $response->assertStatus(409)
            ->assertJson([
                'error' => 'This Google account is already linked to another user'
            ]);
    }

    public function test_unlink_google_account(): void
    {
        $user = User::factory()->create([
            'google_id' => '123456789',
            'tenant_id' => $this->tenant->id,
        ]);

        $this->tenant->makeCurrent();

        $response = $this->actingAs($user)
            ->postJson('/api/auth/google/unlink');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Google account unlinked successfully'
            ]);

        $user->refresh();
        $this->assertNull($user->google_id);
    }

    public function test_username_generation_is_unique(): void
    {
        // Create existing user with username 'johndoe'
        User::factory()->create([
            'username' => 'johndoe',
            'tenant_id' => $this->tenant->id,
        ]);

        // Mock Socialite
        $googleUser = Mockery::mock(SocialiteUser::class);
        $googleUser->shouldReceive('getId')->andReturn('123456789');
        $googleUser->shouldReceive('getEmail')->andReturn('john.doe2@example.com');
        $googleUser->shouldReceive('getName')->andReturn('John Doe');
        $googleUser->shouldReceive('getAvatar')->andReturn('https://avatar.url/photo.jpg');

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn(Mockery::self())
            ->shouldReceive('stateless')
            ->andReturn(Mockery::self())
            ->shouldReceive('user')
            ->andReturn($googleUser);

        $state = base64_encode(json_encode([
            'tenant_id' => $this->tenant->id,
            'tenant_domain' => $this->tenant->domain,
            'csrf_token' => 'test-token'
        ]));

        $response = $this->getJson("/api/auth/google/callback?state={$state}");

        $response->assertStatus(200);

        // Verify new user has unique username
        $newUser = User::where('email', 'john.doe2@example.com')->first();
        $this->assertEquals('johndoe1', $newUser->username);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
