<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Status;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
// Domain model not used; domain stored on tenants table

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::all()->map(function (Tenant $tenant) {
            $subdomain = $tenant->domain;

            return [
                'id' => $tenant->id,
                'domain' => $tenant->domain,
                'database' => $tenant->db_name,
                'subdomain' => $subdomain,
                'base_url' => $this->makeTenantBaseUrl($subdomain),
            ];
        });

        return response()->json(['data' => $tenants]);
    }

    public function show(Tenant $tenant)
    {
        $subdomain = $tenant->domain;

        return response()->json([
            'data' => [
                'id' => $tenant->id,
                'domain' => $tenant->domain,
                'database' => $tenant->db_name,
                'subdomain' => $subdomain,
                'base_url' => $this->makeTenantBaseUrl($subdomain),
            ],
        ]);
    }

public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'subdomain' => 'required|string|alpha_dash|max:63',
        'email' => 'required|email', // central user email
        'password' => 'required|string|min:8', // central user password
        'full_name' => 'nullable|string|max:100',
        'created_by' => 'nullable|uuid|exists:users,id',
    ]);

    $subdomain = strtolower($validated['subdomain']);

    // Ensure no duplicate domain
    $fullDomain = $subdomain . '.' . config('tenancy.base_domain');

    if (Tenant::where('domain', $fullDomain)->exists()) {
        return response()->json(['message' => 'This subdomain is already taken.'], 422);
    }

    $databaseName = 'tenant_' . strtolower(str_replace([' ', '-'], '_', $validated['name'])) . '_' . substr(sha1(uniqid()), 0, 6);

    // Create tenant
    $tenant = Tenant::create([
        'domain' => $fullDomain, // store full domain
        'db_name' => $databaseName,
    ]);

    unset($tenant->data);

    // Insert into domains table
    DB::table('domains')->insert([
        'id' => \Illuminate\Support\Str::uuid(),
        'domain' => $fullDomain, // store full domain (subdomain + base)
        'tenant_id' => $tenant->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $tenant->setInternal('db_name', $databaseName);
    $tenant->save();

    // Ensure central OAuth clients exist (not in tenant DB)
    $this->ensureCentralOAuthClientsExist();

    // Create or ensure the central user exists using the provided credentials
    $fullName = $validated['full_name'] ?? $validated['name'];

         
         
        
        $centralUser = User::create([
            'name' => $fullName,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']), 
        ]);
    
// dd($centralUser);
    // Build tenant base URL
    $baseUrl = $this->makeTenantBaseUrl($subdomain);

    return response()->json([
        'message' => 'Tenant created successfully',
        'data' => [
            'id' => $tenant->id,
            'domain' => $tenant->domain,
            'subdomain' => $subdomain,
            'base_url' => $baseUrl,
        ],
    ], 201);
}

/**
 * Build tenant base URL
 */
protected function makeTenantBaseUrl(string $subdomain): string
{
    $baseDomain = env('TENANCY_BASE_DOMAIN');
    $appPort = parse_url(config('app.url'), PHP_URL_PORT); // e.g. 8000 in local

    return "http://{$subdomain}.{$baseDomain}" . ($appPort ? ":{$appPort}" : '');
}

    private function ensureCentralOAuthClientsExist(): void
    {
        if (!Schema::hasTable('oauth_clients') || !Schema::hasTable('oauth_personal_access_clients')) {
            return;
        }

        $personalClient = DB::table('oauth_clients')
            ->where('personal_access_client', true)
            ->first();

        if (!$personalClient) {
            $personalClientId = DB::table('oauth_clients')->insertGetId([
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

            DB::table('oauth_personal_access_clients')->insert([
                'client_id' => $personalClientId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $pacRow = DB::table('oauth_personal_access_clients')
                ->where('client_id', $personalClient->id)
                ->first();
            if (!$pacRow) {
                DB::table('oauth_personal_access_clients')->insert([
                    'client_id' => $personalClient->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $passwordClient = DB::table('oauth_clients')
            ->where('password_client', true)
            ->first();
        if (!$passwordClient) {
            DB::table('oauth_clients')->insert([
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
        }
    }


    
    // private function makeTenantBaseUrl(?string $subdomain): ?string
    // {
    //     if (!$subdomain) {
    //         return null;
    //     }

    //     $baseDomain = env('TENANCY_BASE_DOMAIN', '127.0.0.1.nip.io');
    //     $appUrl = config('app.url');
    //     $scheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'http';
    //     $port = parse_url($appUrl, PHP_URL_PORT);

    //     $host = $subdomain . '.' . $baseDomain;

    //     return $scheme . '://' . $host . ($port ? ':' . $port : '');
    // }
}