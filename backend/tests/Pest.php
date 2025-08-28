<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->in('Feature');

pest()->extend(Tests\TestCase::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeValidated', function () {
    return $this->toBeInstanceOf(Illuminate\Http\JsonResponse::class)
        ->and($this->getStatusCode())->toBe(422);
});

expect()->extend('toBeSuccessful', function () {
    return $this->toBeInstanceOf(Illuminate\Http\JsonResponse::class)
        ->and($this->getStatusCode())->toBeGreaterThanOrEqual(200)
        ->and($this->getStatusCode())->toBeLessThan(300);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function createTenant(array $attributes = []): \App\Models\Tenant
{
    return \App\Models\Tenant::factory()->create($attributes);
}

function createUser(array $attributes = []): \App\Models\User
{
    $currentTenant = app('currentTenant'); // Using app container instead of Spatie current()
    
    $defaultAttributes = [];
    
    if ($currentTenant) {
        $defaultAttributes['tenant_id'] = $currentTenant->id;
    }
    
    $user = \App\Models\User::factory()->create(array_merge($defaultAttributes, $attributes));
    
    // Give default permissions for tests
    $user->givePermissionTo([
        'view vehicles',
        'create vehicles', 
        'edit vehicles',
        'delete vehicles',
        'export vehicles',
    ]);
    
    return $user;
}

function createVehicle(array $attributes = []): \App\Models\Vehicle
{
    $currentTenant = app('currentTenant'); // Using app container instead of Spatie current()
    $currentUser = auth('sanctum')->user();
    
    $defaultAttributes = [];
    
    if ($currentTenant) {
        $defaultAttributes['tenant_id'] = $currentTenant->id;
    }
    
    if ($currentUser) {
        $defaultAttributes['user_id'] = $currentUser->id;
    }
    
    return \App\Models\Vehicle::factory()->create(array_merge($defaultAttributes, $attributes));
}

function actingAsTenant(\App\Models\Tenant $tenant): void
{
    // Set tenant in context for middleware compatibility (makeCurrent removed)
    app()->instance('currentTenant', $tenant);
    
    // Set tenant in session for middleware compatibility
    session()->put('tenant_id', $tenant->id);
    session()->put('tenant', $tenant);
}

function actingAsUser(\App\Models\User $user): void
{
    test()->actingAs($user, 'sanctum');
}

/**
 * Setup a complete test environment with tenant and user
 */
function setupTenantTest(array $tenantAttributes = [], array $userAttributes = []): array
{
    $tenant = createTenant($tenantAttributes);
    actingAsTenant($tenant);
    
    $user = createUser($userAttributes);
    actingAsUser($user);
    
    return compact('tenant', 'user');
}
