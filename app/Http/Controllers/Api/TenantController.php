<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Status;
use App\Models\User;
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
        'created_by' => 'nullable|uuid|exists:users,id', // <-- allow optional UUID input
    ]);

    $subdomain = strtolower($validated['subdomain']);

    if (Tenant::where('domain', $subdomain)->exists()) {
        return response()->json(['message' => 'This subdomain is already taken.'], 422);
    }

    $databaseName = 'tenant_' . strtolower(str_replace([' ', '-'], '_', $validated['name'])) . '_' . substr(sha1(uniqid()), 0, 6);

    // Ensure status exists
    $statusId = Status::value('id');
    if (!$statusId) {
        $statusId = Status::create(['status_name' => 'Active'])->id;
    }

    // Use `created_by` from request, fallback to auth, then fallback to first user
    $createdBy = $validated['created_by']
        ?? auth()->id()
        ?? User::value('id');

    if (!$createdBy) {
        return response()->json(['message' => 'No users exist to set created_by. Seed an admin user first.'], 422);
    }

    $tenant = Tenant::create([
        'domain' => $subdomain,
        'db_name' => $databaseName,
        'status' => $statusId,
        'created_by' => $createdBy,
    ]);
    
    // Ensure no data attribute is set
    unset($tenant->data);

    $tenant->setInternal('db_name', $databaseName);
    $tenant->save();

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