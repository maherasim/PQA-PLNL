<?php

namespace App\Jobs;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CreateTenantOAuthClients implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tenant;
    public $tries = 3;
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("Starting OAuth client creation for tenant: {$this->tenant->id}");
            
            // Initialize tenancy
            tenancy()->initialize($this->tenant);
            
            try {
                // Wait for database to be fully ready
                $this->waitForDatabase();
                
                // Check if oauth_clients table exists
                if (!DB::getSchemaBuilder()->hasTable('oauth_clients')) {
                    Log::warning("oauth_clients table not found for tenant {$this->tenant->id}, skipping OAuth client creation");
                    return;
                }
                
                // Create personal access client
                $this->createPersonalAccessClient();
                
                // Create password client
                $this->createPasswordClient();
                
                Log::info("Successfully created OAuth clients for tenant: {$this->tenant->id}");
                
            } finally {
                // End tenancy
                tenancy()->end();
            }
            
        } catch (\Exception $e) {
            Log::error("Failed to create OAuth clients for tenant {$this->tenant->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Wait for database to be ready
     */
    private function waitForDatabase(): void
    {
        $maxAttempts = 10;
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            try {
                DB::connection()->getPdo();
                break;
            } catch (\Exception $e) {
                $attempt++;
                if ($attempt >= $maxAttempts) {
                    throw new \Exception("Database not ready after {$maxAttempts} attempts");
                }
                sleep(1);
            }
        }
    }

    /**
     * Create personal access client
     */
    private function createPersonalAccessClient(): void
    {
        $personalClient = DB::table('oauth_clients')
            ->where('personal_access_client', true)
            ->first();
            
        if (!$personalClient) {
            $clientId = DB::table('oauth_clients')->insertGetId([
                'user_id' => null,
                'name' => 'Laravel Personal Access Client',
                'secret' => Str::random(40),
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
            
            Log::info("Created personal access client with ID: {$clientId} for tenant: {$this->tenant->id}");
        }
    }

    /**
     * Create password client
     */
    private function createPasswordClient(): void
    {
        $passwordClient = DB::table('oauth_clients')
            ->where('password_client', true)
            ->first();
            
        if (!$passwordClient) {
            $clientId = DB::table('oauth_clients')->insertGetId([
                'user_id' => null,
                'name' => 'Laravel Password Grant Client',
                'secret' => Str::random(40),
                'provider' => 'users',
                'redirect' => 'http://localhost',
                'personal_access_client' => false,
                'password_client' => true,
                'revoked' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            Log::info("Created password client with ID: {$clientId} for tenant: {$this->tenant->id}");
        }
    }
}