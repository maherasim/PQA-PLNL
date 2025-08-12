<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

// Routes in this file are loaded inside the `tenant` middleware group (see bootstrap/app.php)
// Using subdomain-based tenant identification (store only subdomain in domains table)
Route::middleware(['web', 'Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain'])->group(function () {
    Route::resource('products', ProductController::class);
});
