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
            // PostgreSQL doesn't support "IF NOT EXISTS" in CREATE DATABASE statement
            // We'll need to check if the database exists first
            try {
                DB::statement("CREATE DATABASE {$tenantData['db_name']}");
            } catch (\Exception $e) {
                // Database might already exist, continue anyway
            }
            
            // Create tenant record
            $tenant = Tenant::create([
                'domain' => $tenantData['domain'],
                'db_name' => $tenantData['db_name'],       
                'created_by' => '00000000-0000-0000-0000-000000000000',
                'updated_by' => '00000000-0000-0000-0000-000000000000',
                'deleted_by' => '00000000-0000-0000-0000-000000000000',
            ]);
            
            // No products seeding
        }
    }
    
    private function addSampleProducts($tenant) {}
}
