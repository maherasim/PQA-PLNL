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
    // Optional: backend redirect endpoint if you need a web GET to validate and redirect to frontend
    Route::get('/password/reset/{token}', function ($token) {
        // This just validates and redirects to FE if configured
        $hashed = hash('sha256', $token);
        $record = \DB::table('password_reset_tokens')->where('token', $hashed)->first();
        if (!$record || now()->diffInMinutes($record->created_at) > 30) {
            abort(404);
        }
        $frontend = config('app.frontend_password_reset_url');
        if ($frontend) {
            return redirect(rtrim($frontend, '/') . '?token=' . urlencode($token));
        }
        return response()->json(['token' => $token, 'message' => 'Valid token']);
    })->name('tenant.web.reset.redirect');
    Route::middleware(['auth:api', 'ensure.tenant.by.token'])->group(function () {
        Route::get('/api/me', [TenantAuthController::class, 'me'])->name('tenant.api.me');
        Route::post('/api/logout', [TenantAuthController::class, 'logout'])->name('tenant.api.logout');
    });
});

// Tenant web routes (session-based UI)
// Route::middleware(['web', 'Stancl\Tenancy\Middleware\InitializeTenancyByDomain'])->group(function () {
//     // no product routes
// });
