<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

// Routes in this file are loaded inside the `tenant` middleware group (see bootstrap/app.php)
// Using domain-based tenant identification
Route::middleware(['web', 'Stancl\Tenancy\Middleware\InitializeTenancyByDomain'])->group(function () {
    Route::resource('products', ProductController::class);
});
