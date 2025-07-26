<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    public function __construct(...$args)
    {
        dd('KERNEL HIT');
        parent::__construct(...$args);
    }

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array<string, class-string|string>
     */
    protected $routeMiddleware = [
        // ... existing middleware ...
        'is_super_admin_interne' => \App\Http\Middleware\IsSuperAdminInterne::class,
        // ... existing middleware ...
    ];
} 