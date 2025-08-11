<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Get tenant from query parameter (primary method for now)
        if ($request->has('tenant')) {
            $tenantId = $request->query('tenant');
            $tenant = Tenant::find($tenantId);
            
            if ($tenant) {
                // Set the tenant database connection
                config(['database.connections.tenant.database' => $tenant->database]);
                DB::purge('tenant');
                
                // Set the current tenant in the container
                app()->instance('currentTenant', $tenant);
                
                // Set the default connection to tenant for this request
                config(['database.default' => 'tenant']);
                DB::purge();
                
                return $next($request);
            }
        }
        
        // If no tenant found, abort
        abort(404, 'Tenant not found or not specified');
    }
}
