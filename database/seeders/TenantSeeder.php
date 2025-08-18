<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // Create sample tenants
        $tenants = [
            [
                'name' => 'Company A',
                'domain' => 'company-a',
                'db_name' => 'tenant_company_a',
            ],
            [
                'name' => 'Company B',
                'domain' => 'company-b',
                'db_name' => 'tenant_company_b',
            ],
        ];

        foreach ($tenants as $tenantData) {
            // Create tenant database
            DB::statement("CREATE DATABASE IF NOT EXISTS {$tenantData['db_name']}");
            
            // Create tenant record
            $tenant = Tenant::create([
                'domain' => $tenantData['domain'],
                'db_name' => $tenantData['db_name'],
                'status' => null,
            ]);
            
            // No products seeding
        }
    }
    
    private function addSampleProducts($tenant) {}
}
