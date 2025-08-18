<?php

<?php return; // Deprecated middleware

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class EnsureTenantDatabase
{
    public function handle(Request $request, Closure $next)
    {
        // No product routes anymore; pass-through
        if (false) {
            $tenantId = $request->query('tenant');
            $tenant = Tenant::find($tenantId);
            
            if ($tenant) {
                // Set the tenant database connection
                config(['database.connections.tenant.database' => $tenant->db_name]);
                DB::purge('tenant');
                
                // Set the current tenant in the container
                app()->instance('currentTenant', $tenant);
                
                // Set the default connection to tenant for this request
                config(['database.default' => 'tenant']);
                DB::purge();
            }
        }
        
        return $next($request);
    }
}
