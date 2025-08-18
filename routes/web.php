<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TenantController;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('welcome');
});

// Tenant management routes (landlord)
Route::resource('tenants', TenantController::class);


// Test route to verify multi-tenancy (central)
Route::get('/test-tenant/{tenantId}', function ($tenantId) {
    $tenant = Tenant::find($tenantId);
    if (!$tenant) {
        return "Tenant not found!";
    }
    
    // Get current tenant info
    $currentTenant = app('currentTenant');
    $products = collect();
    
    // Get current database connection info
    $currentDatabase = config('database.connections.tenant.database');
    
    return [
        'current_tenant' => $currentTenant ? $currentTenant->id : 'No tenant',
        'requested_tenant' => $tenant->id,
        'tenant_database' => $tenant->db_name,
        'current_database' => $currentDatabase,
        'products_count' => 0,
        'products' => [],
        'database_connection' => DB::connection()->getDatabaseName()
    ];
});

// Debug route to test database connection
Route::get('/debug-db', function () {
    $tenantId = request()->query('tenant');
    $tenant = Tenant::find($tenantId);
    
    if ($tenant) {
        // Set the tenant database connection
        config(['database.connections.tenant.database' => $tenant->db_name]);
        DB::purge('tenant');
        
        // Set the default connection to tenant
        config(['database.default' => 'tenant']);
        DB::purge();
        
        $products = collect();
        
        return [
            'tenant' => $tenant->id,
            'database' => $tenant->db_name,
            'current_db' => DB::connection()->getDatabaseName(),
            'products_count' => 0,
            'products' => []
        ];
    }
    
    return ['error' => 'No tenant specified'];
});