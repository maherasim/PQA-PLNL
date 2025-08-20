<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Status;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
        'email' => 'required|email',
        'password' => 'required|string|min:8',
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

    // Initialize tenant DB before Passport setup and user creation
    tenancy()->initialize($tenant);

    try {
        // Ensure OAuth clients exist in tenant DB
        if (\Illuminate\Support\Facades\Schema::hasTable('oauth_clients')) {
            // Create Personal Access Client
            $personalClient = DB::table('oauth_clients')
                ->where('personal_access_client', true)
                ->first();

            if (!$personalClient) {
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
            }

            // Create Password Grant Client
            $passwordClient = DB::table('oauth_clients')
                ->where('password_client', true)
                ->first();

            if (!$passwordClient) {
                DB::table('oauth_clients')->insert([
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
            }
        }

        // Create initial user in tenant DB
        $fullName = $validated['full_name'] ?? $validated['name'];

        // Ensure users table exists before attempting to create
        if (\Illuminate\Support\Facades\Schema::hasTable('users')) {
            // Optional: prevent duplicate email within tenant
            $existing = User::where('email', $validated['email'])->first();
            if ($existing) {
                return response()->json(['message' => 'Email already exists for this tenant.'], 422);
            }

            User::create([
                'full_name' => $fullName,
                'email' => $validated['email'],
                'password_hash' => Hash::make($validated['password']),
                'password_created_at' => now(),
                'password_last_changed' => now(),
            ]);
        }
    } finally {
        tenancy()->end();
    }

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