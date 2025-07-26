<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Your tinker commands here
use Spatie\Permission\Models\Permission;

// Example: Get all permissions
$permissions = Permission::all();
echo "Total permissions: " . $permissions->count() . "\n";

foreach ($permissions as $permission) {
    echo "- " . $permission->name . "\n";
}

