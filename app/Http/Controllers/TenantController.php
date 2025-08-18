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
            'subdomain' => 'required|string|alpha_dash|max:63',
        ]);

        $subdomain = strtolower($request->subdomain);

        // Ensure subdomain is unique in domains table (we now store only subdomain)
        if (\Stancl\Tenancy\Database\Models\Domain::where('domain', $subdomain)->exists()) {
            return back()->withErrors(['subdomain' => 'This subdomain is already taken.'])->withInput();
        }

        $databaseName = 'tenant_' . strtolower(str_replace([' ', '-'], '_', $request->name)) . '_' . substr(sha1(uniqid()), 0, 6);

        $tenant = Tenant::create([
            'domain' => $request->name,
            'db_name' => $databaseName,
            'status' => null,
        ]);

        // Set the internal db_name attribute to ensure the correct database is used
        $tenant->setInternal('db_name', $databaseName);
        $tenant->save();

        // Store only the subdomain in tenancy domains table
        TenancyDomain::create([
            'domain' => $subdomain,
            'tenant_id' => $tenant->id,
        ]);

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

        $tenant->update(['domain' => $request->name]);

        if ($request->filled('domain')) {
            // Update or create domain mapping (still storing subdomain only)
            $domain = TenancyDomain::firstOrNew(['tenant_id' => $tenant->id]);
            $domain->domain = strtolower($request->domain);
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
