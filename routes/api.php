<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TenantController as TenantApiController;
use App\Http\Controllers\Api\AdminAuthController;
// These routes are automatically grouped under the 'api' middleware and prefixed with '/api'
Route::middleware(['ensure.tenant.by.token'])->group(function () {
    Route::get('/tenants', [TenantApiController::class, 'index'])->name('api.tenants.index');
    Route::get('/tenants/{tenant}', [TenantApiController::class, 'show'])->name('api.tenants.show');
    Route::post('/tenants', [TenantApiController::class, 'store'])->name('api.tenants.store');

    // Admin auth (central)

    Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('login');
    // Central admin password reset flow (no auth)
    Route::post('/admin/forgot-password', [AdminAuthController::class, 'forgotPassword'])->name('api.admin.forgot');
    Route::get('/admin/password/reset/verify', [AdminAuthController::class, 'verifyResetToken'])->name('api.admin.verify');
    Route::post('/admin/reset-password', [AdminAuthController::class, 'resetPassword'])->name('api.admin.reset');

    Route::middleware('auth:api')->group(function () {
        Route::get('/admin/me', [AdminAuthController::class, 'me'])->name('api.admin.me');
        Route::get('/admin/tenant', [AdminAuthController::class, 'tenantFromToken'])->name('api.admin.tenant');
        Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('api.admin.logout');
    });
});