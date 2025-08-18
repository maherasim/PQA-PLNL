<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
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
        ]);

        $subdomain = strtolower($validated['subdomain']);

        if (Tenant::where('domain', $subdomain)->exists()) {
            return response()->json(['message' => 'This subdomain is already taken.'], 422);
        }

        $databaseName = 'tenant_' . strtolower(str_replace([' ', '-'], '_', $validated['name'])) . '_' . substr(sha1(uniqid()), 0, 6);

        $tenant = Tenant::create([
            'domain' => $subdomain,
            'db_name' => $databaseName,
            'status' => null,
        ]);

        // Ensure the db_name internal attribute is set for stancl/tenancy database naming
        $tenant->setInternal('db_name', $databaseName);
        $tenant->save();

        // domain persisted on tenants table

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

    private function makeTenantBaseUrl(?string $subdomain): ?string
    {
        if (!$subdomain) {
            return null;
        }

        $baseDomain = env('TENANCY_BASE_DOMAIN', '127.0.0.1.nip.io');
        $appUrl = config('app.url');
        $scheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'http';
        $port = parse_url($appUrl, PHP_URL_PORT);

        $host = $subdomain . '.' . $baseDomain;

        return $scheme . '://' . $host . ($port ? ':' . $port : '');
    }
}