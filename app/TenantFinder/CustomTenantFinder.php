<?php

namespace App\TenantFinder;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Spatie\Multitenancy\TenantFinder\TenantFinder;
use Illuminate\Support\Facades\DB;

class CustomTenantFinder extends TenantFinder
{
    public function findForRequest(Request $request): ?Tenant
    {
        // For testing, we'll use a query parameter to switch tenants
        $tenantId = $request->query('tenant');
        
        if ($tenantId) {
            $tenant = Tenant::find($tenantId);
            if ($tenant) {
                // Set the tenant database connection
                config(['database.connections.tenant.database' => $tenant->database]);
                DB::purge('tenant');
                return $tenant;
            }
        }
        
        // For domain-based detection (in production)
        $host = $request->getHost();
        $tenant = Tenant::where('domain', $host)->first();
        
        if ($tenant) {
            // Set the tenant database connection
            config(['database.connections.tenant.database' => $tenant->database]);
            DB::purge('tenant');
        }
        
        return $tenant;
    }
}
