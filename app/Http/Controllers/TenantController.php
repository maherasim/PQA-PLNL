<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        // Create tenant
        $tenant = Tenant::create([
            'name' => $request->name,
            'domain' => $request->domain,
            'database' => $databaseName,
            'is_active' => true,
        ]);

        // Create tenant database
        DB::statement("CREATE DATABASE IF NOT EXISTS {$databaseName}");

        // Run migrations for the new tenant
        $this->migrateTenant($tenant);

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
        // Drop tenant database
        DB::statement("DROP DATABASE IF EXISTS {$tenant->database}");
        
        $tenant->delete();

        return redirect()->route('tenants.index')->with('success', 'Tenant deleted successfully!');
    }

    private function migrateTenant($tenant)
    {
        try {
            // Set the database connection to the tenant's database
            config(['database.connections.tenant.database' => $tenant->database]);
            DB::purge('tenant');
            
            // Set the default connection to tenant
            config(['database.default' => 'tenant']);
            DB::purge();
            
            // Run migrations for this tenant
            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--force' => true
            ]);
            
            return true;
        } catch (\Exception $e) {
            // Log the error but don't fail the tenant creation
            \Log::error("Failed to migrate tenant {$tenant->name}: " . $e->getMessage());
            return false;
        }
    }
}
