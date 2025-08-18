<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantPassportInstallController extends Controller
{
    public function install(Request $request)
    {
        $tenantId = $request->input('tenant_id');
        $tenant = Tenant::findOrFail($tenantId);

        // Run passport:install inside tenant context
        tenancy()->initialize($tenant);
        try {
            \Artisan::call('passport:install', ['--force' => true]);
        } finally {
            tenancy()->end();
        }

        return redirect()->back()->with('success', 'Passport installed for tenant '.$tenant->id);
    }
}