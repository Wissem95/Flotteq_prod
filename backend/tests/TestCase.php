<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Multitenancy\Concerns\UsesMultitenancyConfig;

abstract class TestCase extends BaseTestCase
{
    use UsesMultitenancyConfig;
    use WithFaker;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Initialize session for multitenancy
        $this->withSession([]);
        
        // Disable problematic middlewares for testing
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Spatie\Multitenancy\Http\Middleware\EnsureValidTenantSession::class,
        ]);
        
        // Seed permissions for tests
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }
}
