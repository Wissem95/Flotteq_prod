<?php

declare(strict_types=1);

namespace App\TenantFinder;

use Illuminate\Http\Request;
use App\Models\Tenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder;
use Spatie\Multitenancy\Contracts\IsTenant;

class HeaderTenantFinder extends TenantFinder
{
    public function findForRequest(Request $request): ?IsTenant
    {
        $tenantId = $request->header('X-Tenant-ID');
        if (!$tenantId) {
            return null;
        }
        return Tenant::find($tenantId);
    }
} 