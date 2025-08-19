<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "Starting Passport installation for all tenants...\n";

$allTenants = Tenant::all();

foreach ($allTenants as $tenant) {
    echo "\n--- Processing Tenant: ID {$tenant->id}, Domain: {$tenant->domain} ---\n";

    tenancy()->initialize($tenant);

    try {
        // Check if oauth_clients table exists
        if (!Schema::hasTable('oauth_clients')) {
            echo "⚠️ Skipped: oauth_clients table not found in tenant database. Run migrations first.\n";
            tenancy()->end();
            continue;
        }

        // Personal Access Client
        $personalClient = DB::table('oauth_clients')
            ->where('personal_access_client', true)
            ->first();

        if ($personalClient) {
            echo "✔ Personal access client already exists (ID: {$personalClient->id})\n";
        } else {
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

            DB::table('oauth_personal_access_clients')->insert([
                'client_id' => $clientId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            echo "✅ Created personal access client (ID: {$clientId})\n";
        }

        // Password Client
        $passwordClient = DB::table('oauth_clients')
            ->where('password_client', true)
            ->first();

        if ($passwordClient) {
            echo "✔ Password client already exists (ID: {$passwordClient->id})\n";
        } else {
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

            echo "✅ Created password client (ID: {$clientId})\n";
        }

    } catch (Exception $e) {
        echo "❌ ERROR for tenant {$tenant->id}: {$e->getMessage()}\n";
    } finally {
        tenancy()->end();
    }
}

echo "\n🎉 Passport installation completed for all tenants!\n";
