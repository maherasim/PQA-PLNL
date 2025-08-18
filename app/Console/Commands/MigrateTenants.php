<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateTenants extends Command
{
    protected $signature = 'tenants:migrate-all-direct';
    protected $description = 'Run migrations for all tenant databases (direct connection switch)';

    public function handle()
    {
        $tenants = Tenant::all();
        
        foreach ($tenants as $tenant) {
            $this->info("Migrating tenant: {$tenant->id} ({$tenant->db_name})");
            
            // Set the database connection to the tenant's database
            config(['database.connections.tenant.database' => $tenant->db_name]);
            DB::purge('tenant');
            
            // Run migrations for this tenant
            $this->call('migrate', [
                '--database' => 'tenant',
                '--force' => true
            ]);
        }
        
        $this->info('All tenant migrations completed!');
    }
}
