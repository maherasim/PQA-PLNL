<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
// Domain model not used; domain stored on tenants table

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

        // Ensure subdomain is unique in tenants table
        if (\App\Models\Tenant::where('domain', $subdomain)->exists()) {
           return back()->withErrors(['subdomain' => 'This subdomain is already taken.'])->withInput();
       }
       
       $databaseName = 'tenant_' . strtolower(str_replace([' ', '-'], '_', $request->name)) . '_' . substr(sha1(uniqid()), 0, 6);

       $tenant = Tenant::create([
           'domain' => $subdomain,
           'db_name' => $databaseName,
           'status' => null,
       ]);

       // Create domain entry for the tenant
       DB::table('domains')->insert([
           'id' => \Illuminate\Support\Str::uuid(),
           'domain' => $subdomain,
           'tenant_id' => $tenant->id,
           'created_at' => now(),
           'updated_at' => now(),
       ]);

       // Set the internal db_name attribute to ensure the correct database is used
       $tenant->setInternal('db_name', $databaseName);
       $tenant->save();

        // Domain saved on tenants table; nothing to create

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
            $tenant->update(['domain' => strtolower($request->domain)]);
        }

        return redirect()->route('tenants.index')->with('success', 'Tenant updated successfully!');
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete();
        return redirect()->route('tenants.index')->with('success', 'Tenant deleted successfully!');
    }
}
