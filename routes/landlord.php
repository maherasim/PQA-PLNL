<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\Landlord\TenantPassportInstallController;

/*
|--------------------------------------------------------------------------
| Landlord Routes
|--------------------------------------------------------------------------
|
| These routes are for the landlord/admin to manage tenants and
| system-wide functionality. They are not tenant-specific.
|
*/

// Tenant management routes (landlord/admin only)
Route::resource('tenants', TenantController::class);

// Bulk operations for tenants
Route::post('/tenants/bulk-migrate', function () {
    // Prefer stancl/tenancy command if available; otherwise use direct
    try {
        \Artisan::call('tenants:migrate');
    } catch (\Throwable $e) {
        \Artisan::call('tenants:migrate-all-direct');
    }
    return redirect()->back()->with('success', 'Migrations completed for all tenants');
})->name('landlord.tenants.bulk-migrate');

Route::post('/tenants/bulk-seed', function () {
    // Run seeders for all tenants via tenancy helper
    \Artisan::call('tenant:artisan', ['command' => 'db:seed', '--all' => true]);
    return redirect()->back()->with('success', 'Seeders completed for all tenants');
})->name('landlord.tenants.bulk-seed');

// Install passport clients for a given tenant
Route::post('/tenants/{tenant}/passport-install', [TenantPassportInstallController::class, 'install'])->name('landlord.tenants.passport-install');
