<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class MigrateAllTenants extends Command
{
    protected $signature = 'tenants:migrate-all';
    protected $description = 'Run migrations for all existing tenants';

    public function handle()
    {
        $tenants = Tenant::all();
        
        if ($tenants->isEmpty()) {
            $this->info('No tenants found.');
            return;
        }
        
        $this->info("Found {$tenants->count()} tenants. Starting migration...");
        
        foreach ($tenants as $tenant) {
            $this->info("Migrating tenant: {$tenant->id} ({$tenant->db_name})");
            
            try {
                // Set the database connection to the tenant's database
                config(['database.connections.tenant.database' => $tenant->db_name]);
                DB::purge('tenant');
                
                // Set the default connection to tenant
                config(['database.default' => 'tenant']);
                DB::purge();
                
                // Run migrations for this tenant
                Artisan::call('migrate', [
                    '--database' => 'tenant',
                    '--force' => true
                ]);
                
                $this->info("✓ Successfully migrated tenant {$tenant->id}");
                
            } catch (\Exception $e) {
                $this->error("✗ Failed to migrate tenant {$tenant->id}: " . $e->getMessage());
            }
        }
        
        $this->info('All tenant migrations completed!');
    }
}
