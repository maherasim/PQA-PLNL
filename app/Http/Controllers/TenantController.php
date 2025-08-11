<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

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
            'domain' => 'required|string|unique:tenants,domain',
        ]);

        $databaseName = 'tenant_' . strtolower(str_replace([' ', '-'], '_', $request->name));

        $tenant = Tenant::create([
            'name' => $request->name,
            'domain' => $request->domain,
            'database' => $databaseName,
            'is_active' => true,
        ]);

        // Create database & run tenant migrations via stancl/tenancy
        $tenant->database()->manager()->createDatabase($tenant);
        \Stancl\Tenancy\Facades\Tenancy::initialize($tenant);
        Artisan::call('tenants:run', [
            'commandname' => 'migrate',
            '--tenants' => [$tenant->id],
            '--option' => ['force'],
        ]);
        \Stancl\Tenancy\Facades\Tenancy::end();

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
            'domain' => 'required|string|unique:tenants,domain,' . $tenant->id,
        ]);

        $tenant->update($request->only(['name', 'domain']));

        return redirect()->route('tenants.index')->with('success', 'Tenant updated successfully!');
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete();
        return redirect()->route('tenants.index')->with('success', 'Tenant deleted successfully!');
    }
}
