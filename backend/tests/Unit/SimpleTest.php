<?php

test('basic test works', function () {
    expect(true)->toBeTrue();
});

test('database connection works', function () {
    expect(\DB::connection()->getPdo())->not->toBeNull();
});

test('tenants table exists on default connection', function () {
    // Check if tenants table exists on default connection
    expect(\Illuminate\Support\Facades\Schema::hasTable('tenants'))->toBeTrue();
}); 