<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\ProductController;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('welcome');
});

// Tenant management routes (landlord)
Route::resource('tenants', TenantController::class);


// Test route to verify multi-tenancy
Route::get('/test-tenant/{tenantId}', function ($tenantId) {
    $tenant = Tenant::find($tenantId);
    if (!$tenant) {
        return "Tenant not found!";
    }
    
    // Get current tenant info
    $currentTenant = app('currentTenant');
    $products = Product::all();
    
    // Get current database connection info
    $currentDatabase = config('database.connections.tenant.database');
    
    return [
        'current_tenant' => $currentTenant ? $currentTenant->name : 'No tenant',
        'requested_tenant' => $tenant->name,
        'tenant_database' => $tenant->database,
        'current_database' => $currentDatabase,
        'products_count' => $products->count(),
        'products' => $products->toArray(),
        'database_connection' => DB::connection()->getDatabaseName()
    ];
});

// Debug route to test database connection
Route::get('/debug-db', function () {
    $tenantId = request()->query('tenant');
    $tenant = Tenant::find($tenantId);
    
    if ($tenant) {
        // Set the tenant database connection
        config(['database.connections.tenant.database' => $tenant->database]);
        DB::purge('tenant');
        
        // Set the default connection to tenant
        config(['database.default' => 'tenant']);
        DB::purge();
        
        $products = Product::all();
        
        return [
            'tenant' => $tenant->name,
            'database' => $tenant->database,
            'current_db' => DB::connection()->getDatabaseName(),
            'products_count' => $products->count(),
            'products' => $products->toArray()
        ];
    }
    
    return ['error' => 'No tenant specified'];
});
