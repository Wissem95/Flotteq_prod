<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for vehicles
        $vehiclePermissions = [
            'view vehicles',
            'create vehicles',
            'edit vehicles',
            'delete vehicles',
            'export vehicles',
        ];

        // Create permissions for users
        $userPermissions = [
            'view users',
            'create users',
            'edit users',
            'delete users',
            'manage user roles',
        ];

        // Create permissions for statistics
        $statisticsPermissions = [
            'view statistics',
            'export statistics',
        ];

        // Create permissions for invoices/documents
        $documentPermissions = [
            'view documents',
            'create documents',
            'edit documents',
            'delete documents',
            'generate pdf reports',
        ];

        // Create permissions for subscriptions
        $subscriptionPermissions = [
            'view subscriptions',
            'manage subscriptions',
            'view billing',
        ];

        // Create permissions for company management
        $companyPermissions = [
            'view companies',
            'create companies',
            'edit companies',
            'delete companies',
            'switch companies',
        ];

        // Create all permissions
        $allPermissions = array_merge(
            $vehiclePermissions,
            $userPermissions,
            $statisticsPermissions,
            $documentPermissions,
            $subscriptionPermissions,
            $companyPermissions
        );

        foreach ($allPermissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'sanctum']);
        }

        // Update cache to know about the newly created permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles and assign permissions

        // Super Admin - all permissions
        $superAdmin = Role::create(['name' => 'super-admin', 'guard_name' => 'sanctum']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin - company management permissions
        $admin = Role::create(['name' => 'admin', 'guard_name' => 'sanctum']);
        $admin->givePermissionTo([
            ...$vehiclePermissions,
            ...$userPermissions,
            ...$statisticsPermissions,
            ...$documentPermissions,
            'view subscriptions',
            'view billing',
            'view companies',
            'edit companies',
        ]);

        // Manager - vehicle and user management
        $manager = Role::create(['name' => 'manager', 'guard_name' => 'sanctum']);
        $manager->givePermissionTo([
            ...$vehiclePermissions,
            'view users',
            'create users',
            'edit users',
            ...$statisticsPermissions,
            ...$documentPermissions,
        ]);

        // Employee - basic vehicle operations
        $employee = Role::create(['name' => 'employee', 'guard_name' => 'sanctum']);
        $employee->givePermissionTo([
            'view vehicles',
            'create vehicles',
            'edit vehicles',
            'view documents',
            'create documents',
            'generate pdf reports',
        ]);

        // Viewer - read-only access
        $viewer = Role::create(['name' => 'viewer', 'guard_name' => 'sanctum']);
        $viewer->givePermissionTo([
            'view vehicles',
            'view users',
            'view statistics',
            'view documents',
        ]);
    }
}
