<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\Product;
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
                'domain' => 'company-a.local',
                'database' => 'tenant_company_a',
                'is_active' => true,
            ],
            [
                'name' => 'Company B',
                'domain' => 'company-b.local',
                'database' => 'tenant_company_b',
                'is_active' => true,
            ],
        ];

        foreach ($tenants as $tenantData) {
            // Create tenant database
            DB::statement("CREATE DATABASE IF NOT EXISTS {$tenantData['database']}");
            
            // Create tenant record
            $tenant = Tenant::create($tenantData);
            
            // Add sample products to this tenant's database
            $this->addSampleProducts($tenant);
        }
    }
    
    private function addSampleProducts($tenant)
    {
        // Set the database connection to the tenant's database
        config(['database.connections.tenant.database' => $tenant->database]);
        DB::purge('tenant');
        
        // Create sample products for this tenant
        $products = [
            [
                'name' => $tenant->name . ' Product 1',
                'description' => 'Sample product for ' . $tenant->name,
                'price' => 99.99,
                'stock' => 10,
            ],
            [
                'name' => $tenant->name . ' Product 2',
                'description' => 'Another sample product for ' . $tenant->name,
                'price' => 149.99,
                'stock' => 5,
            ],
        ];
        
        foreach ($products as $productData) {
            Product::create($productData);
        }
    }
}
