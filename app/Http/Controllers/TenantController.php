<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Stancl\Tenancy\Database\Models\Domain;

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

        // Attach domain record required by stancl/tenancy
        $tenant->domains()->create([
            'domain' => $request->domain,
        ]);

        // Trigger tenancy events to create DB & run migrations (via TenancyServiceProvider pipelines)
        tenancy()->initialize($tenant);
        tenancy()->end();

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
            'domain' => 'required|string|unique:domains,domain,' . $tenant->id,
        ]);

        $tenant->update($request->only(['name']));

        // Update domain value
        $tenant->domains()->updateOrCreate([], [
            'domain' => $request->domain,
        ]);

        return redirect()->route('tenants.index')->with('success', 'Tenant updated successfully!');
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete();
        return redirect()->route('tenants.index')->with('success', 'Tenant deleted successfully!');
    }
}
