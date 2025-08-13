<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TenantController as TenantApiController;

// These routes are automatically grouped under the 'api' middleware and prefixed with '/api'
Route::get('/tenants', [TenantApiController::class, 'index'])->name('api.tenants.index');
Route::get('/tenants/{tenant}', [TenantApiController::class, 'show'])->name('api.tenants.show');
Route::post('/tenants', [TenantApiController::class, 'store'])->name('api.tenants.store');