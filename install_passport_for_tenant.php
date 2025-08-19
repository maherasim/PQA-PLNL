<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tenant;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "Starting Passport installation for tenant 'asim'...\n";

try {
    // Find the tenant by domain
    $tenant = Tenant::where('domain', 'asim.127.0.0.1.nip.io')->first();
    
    if (!$tenant) {
        echo "ERROR: Tenant with domain 'asim.127.0.0.1.nip.io' not found!\n";
        echo "Available tenants:\n";
        $allTenants = Tenant::all();
        foreach ($allTenants as $t) {
            echo "- ID: {$t->id}, Domain: {$t->domain}\n";
        }
        exit(1);
    }
    
    echo "Found tenant: ID {$tenant->id}, Domain: {$tenant->domain}\n";
    
    // Initialize tenancy
    tenancy()->initialize($tenant);
    
    try {
        echo "Tenancy initialized. Installing Passport...\n";
        
        // Check if oauth_clients table exists
        if (!Schema::hasTable('oauth_clients')) {
            echo "ERROR: oauth_clients table not found in tenant database!\n";
            echo "Please run tenant migrations first.\n";
            exit(1);
        }
        
        // Check if personal access client already exists
        $personalClient = DB::table('oauth_clients')
            ->where('personal_access_client', true)
            ->first();
            
        if ($personalClient) {
            echo "Personal access client already exists (ID: {$personalClient->id})\n";
        } else {
            echo "Creating personal access client...\n";
            
            // Create personal access client
            $clientId = DB::table('oauth_clients')->insertGetId([
                'user_id' => null,
                'name' => 'Laravel Personal Access Client',
                'secret' => \Illuminate\Support\Str::random(40),
                'provider' => 'users',
                'redirect' => 'http://localhost',
                'personal_access_client' => true,
                'password_client' => false,
                'revoked' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Create personal access client record
            DB::table('oauth_personal_access_clients')->insert([
                'client_id' => $clientId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            echo "Personal access client created with ID: {$clientId}\n";
        }
        
        // Check if password client exists
        $passwordClient = DB::table('oauth_clients')
            ->where('password_client', true)
            ->first();
            
        if ($passwordClient) {
            echo "Password client already exists (ID: {$passwordClient->id})\n";
        } else {
            echo "Creating password client...\n";
            
            // Create password client
            $clientId = DB::table('oauth_clients')->insertGetId([
                'user_id' => null,
                'name' => 'Laravel Password Grant Client',
                'secret' => \Illuminate\Support\Str::random(40),
                'provider' => 'users',
                'redirect' => 'http://localhost',
                'personal_access_client' => false,
                'password_client' => true,
                'revoked' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            echo "Password client created with ID: {$clientId}\n";
        }
        
        echo "Passport installation completed successfully!\n";
        
    } finally {
        // End tenancy
        tenancy()->end();
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "Done!\n";