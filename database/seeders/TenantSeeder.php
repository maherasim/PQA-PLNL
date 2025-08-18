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
            
            // Add sample products to this tenant's database
            $this->addSampleProducts($tenant);
        }
    }
    
    private function addSampleProducts($tenant)
    {
        // Set the database connection to the tenant's database
        config(['database.connections.tenant.database' => $tenant->db_name]);
        DB::purge('tenant');
        
        // Create sample products for this tenant
        $products = [
            [
                'name' => 'Product 1',
                'description' => 'Sample product',
                'price' => 99.99,
                'stock' => 10,
            ],
            [
                'name' => 'Product 2',
                'description' => 'Another sample product',
                'price' => 149.99,
                'stock' => 5,
            ],
        ];
        
        foreach ($products as $productData) {
            Product::create($productData);
        }
    }
}
