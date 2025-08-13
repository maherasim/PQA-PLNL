<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TenantController as TenantApiController;
use App\Http\Controllers\Api\AdminAuthController;
// These routes are automatically grouped under the 'api' middleware and prefixed with '/api'
Route::get('/tenants', [TenantApiController::class, 'index'])->name('api.tenants.index');
Route::get('/tenants/{tenant}', [TenantApiController::class, 'show'])->name('api.tenants.show');
Route::post('/tenants', [TenantApiController::class, 'store'])->name('api.tenants.store');

// Admin auth (central)

Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('login');
Route::middleware('auth:api')->group(function () {
    Route::get('/admin/me', [AdminAuthController::class, 'me'])->name('api.admin.me');
    Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('api.admin.logout');
});