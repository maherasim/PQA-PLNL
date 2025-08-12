<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Stancl\Tenancy\Database\Models\Domain as TenancyDomain;
use Illuminate\Support\Str;

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

        // Create a stable, readable tenant ID from the name
        $slug = Str::of($request->name)->slug('_');

        // Match what stancl/tenancy will create: prefix + tenant_id + suffix
        $databaseName = config('tenancy.database.prefix') . $slug . config('tenancy.database.suffix');

        $tenant = Tenant::create([
            'id' => (string) $slug,
            'name' => $request->name,
            'database' => $databaseName,
            'is_active' => true,
        ]);

        // Set the internal db_name attribute to ensure the correct database is used
        $tenant->setInternal('db_name', $databaseName);
        $tenant->save();

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
