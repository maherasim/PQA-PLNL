<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\AuthController as TenantAuthController;

// Routes in this file are loaded inside the `tenant` middleware group (see bootstrap/app.php)
// Using subdomain-based tenant identification (store only subdomain in domains table)

// Tenant API routes (stateless)
Route::middleware(['api', 'Stancl\Tenancy\Middleware\InitializeTenancyByDomain'])->group(function () {
    Route::post('/api/register', [TenantAuthController::class, 'register'])->name('tenant.api.register');
    Route::post('/api/login', [TenantAuthController::class, 'login'])->name('tenant.api.login');
    Route::post('/api/forgot-password', [TenantAuthController::class, 'forgotPassword'])->name('tenant.api.forgot');
    Route::post('/api/token-verify', [TenantAuthController::class, 'verifyResetToken'])->name('tenant.api.token.verify');
    Route::post('/api/reset-password', [TenantAuthController::class, 'resetPassword'])->name('tenant.api.reset');
    Route::middleware('auth:api')->group(function () {
        Route::get('/api/me', [TenantAuthController::class, 'me'])->name('tenant.api.me');
        Route::post('/api/logout', [TenantAuthController::class, 'logout'])->name('tenant.api.logout');
    });
});

// Tenant web routes (session-based UI)
// Route::middleware(['web', 'Stancl\Tenancy\Middleware\InitializeTenancyByDomain'])->group(function () {
//     // no product routes
// });
