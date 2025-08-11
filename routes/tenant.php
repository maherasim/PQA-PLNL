<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

// Routes in this file are loaded inside the `tenant` middleware group (see bootstrap/app.php)
// Add the `web` middleware here to ensure sessions, CSRF, etc., are applied as well.
Route::middleware(['web', \Stancl\Tenancy\Middleware\InitializeTenancyByRequestData::class])->group(function () {
    Route::resource('products', ProductController::class);
});
