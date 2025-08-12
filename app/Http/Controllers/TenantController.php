<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Stancl\Tenancy\Database\Models\Domain as TenancyDomain;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::all();
        return view('tenants.index', compact('tenants'));
    }

    public function create()
    {
        return view('tenants.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|unique:domains,domain',
        ]);

        $databaseName = 'tenant_' . strtolower(str_replace([' ', '-'], '_', $request->name));

        $tenant = Tenant::create([
            'name' => $request->name,
            'database' => $databaseName,
            'is_active' => true,
        ]);

        // Attach domain to tenant (stancl/tenancy domains table)
        TenancyDomain::create([
            'domain' => $request->domain,
            'tenant_id' => $tenant->id,
        ]);

        // Database is created & tenant migrations are executed by TenancyServiceProvider (TenantCreated pipeline)

        return redirect()->route('tenants.index')->with('success', 'Tenant created successfully!');
    }

    public function show(Tenant $tenant)
    {
        return view('tenants.show', compact('tenant'));
    }

    public function edit(Tenant $tenant)
    {
        return view('tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|unique:domains,domain,' . $tenant->id . ',tenant_id',
        ]);

        $tenant->update($request->only(['name']));

        if ($request->filled('domain')) {
            // Update or create domain mapping
            $domain = TenancyDomain::firstOrNew(['tenant_id' => $tenant->id]);
            $domain->domain = $request->domain;
            $domain->save();
        }

        return redirect()->route('tenants.index')->with('success', 'Tenant updated successfully!');
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete();
        return redirect()->route('tenants.index')->with('success', 'Tenant deleted successfully!');
    }
}
