<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TenantController;

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
    // Run migrations for all tenants
    \Artisan::call('tenants:migrate-all');
    return redirect()->back()->with('success', 'Migrations completed for all tenants');
})->name('landlord.tenants.bulk-migrate');

Route::post('/tenants/bulk-seed', function () {
    // Run seeders for all tenants
    \Artisan::call('tenant:artisan', ['command' => 'db:seed', '--all' => true]);
    return redirect()->back()->with('success', 'Seeders completed for all tenants');
})->name('landlord.tenants.bulk-seed');
