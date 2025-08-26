<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use Laravel\Passport\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        // 1️⃣ Validate input
        $credentials = $request->validate([
            'email'     => 'required|email',
            'password'  => 'required|string',
            'tenant_id' => 'nullable|string',
            'subdomain' => 'nullable|string',
        ]);
    
        // 2️⃣ Find user
        $user = User::where('email', $credentials['email'])->first();
        $storedHash = $user->password_hash ?? $user->password ?? null;
    
        if (!$user || !$storedHash || !Hash::check($credentials['password'], $storedHash)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    
        // 3️⃣ Resolve tenant context
        $tenant = null;
        if (!empty($credentials['tenant_id'])) {
            $tenant = Tenant::find($credentials['tenant_id']);
        } elseif (!empty($credentials['subdomain'])) {
            $domain = rtrim($credentials['subdomain'], '.') . '.' . config('tenancy.base_domain');
            $tenant = Tenant::where('domain', $domain)->first();
        }
    
        if (!$tenant && Schema::hasColumn('users', 'default_tenant_id')) {
            $defaultTenantId = $user->default_tenant_id;
            if ($defaultTenantId) {
                $tenant = Tenant::find($defaultTenantId);
            }
        }
    
        // Tenant info
        $tenantId     = $tenant?->id;
        $tenantDomain = $tenant?->domain;
    
        // 4️⃣ Create token — empty scopes
        $tokenName   = $tenantId ? "tenant:{$tenantId};domain:{$tenantDomain}" : 'admin';
        $tokenResult = $user->createToken($tokenName, []); // ✅ empty scopes
    
        $accessToken = $tokenResult->accessToken;
        $expiresAt   = $tokenResult->token->expires_at;
    
        // 5️⃣ Return response
        return response()->json([
            'token_type'   => 'Bearer',
            'access_token' => $accessToken,
            'expires_at'   => $expiresAt,
            'tenant'       => $tenant ? [
                'id'     => $tenant->id,
                'domain' => $tenant->domain,
            ] : null,
            'user' => [
                'id'    => $user->id,
                'name'  => $user->full_name,
                'email' => $user->email,
            ],
        ]);
    }
    

 
    

    public function me(Request $request)
    {
        $user = $request->user();
        dd( $user);
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Attempt to resolve tenant from the bearer token's name convention
        $resolvedTenant = null;
        $resolvedVia = null;
        $tokenInfo = null;

        $bearer = $request->bearerToken();
        if ($bearer) {
            $jti = $this->getJtiFromJwtLocal($bearer);
            if ($jti) {
                $passportConn = config('passport.connection') ?: config('passport.storage.database.connection') ?: config('database.default');
                $row = \DB::connection($passportConn)->table('oauth_access_tokens')->where('id', $jti)->first();
                if ($row) {
                    $tokenInfo = [
                        'id' => $row->id,
                        'name' => $row->name,
                        'expires_at' => $row->expires_at,
                    ];
                    $name = (string) ($row->name ?? '');
                    if (\Illuminate\Support\Str::startsWith($name, 'tenant:')) {
                        $parts = collect(explode(';', $name))
                            ->map(fn($p) => explode(':', $p, 2))
                            ->filter(fn($kv) => count($kv) === 2)
                            ->mapWithKeys(fn($kv) => [trim($kv[0]) => trim($kv[1])]);
                        $tenantId = $parts->get('tenant');
                        if ($tenantId) {
                            $resolvedTenant = \App\Models\Tenant::find($tenantId);
                            if ($resolvedTenant) {
                                $resolvedVia = 'token_name';
                            }
                        }
                    }
                }
            }
        }

        // Fallback: use user's default tenant if not resolved from token
        if (!$resolvedTenant && !empty($user->default_tenant_id)) {
            $resolvedTenant = \App\Models\Tenant::find($user->default_tenant_id);
            $resolvedVia = $resolvedTenant ? 'user_default' : $resolvedVia;
        }

        // Compose response
        return response()->json([
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
            ],
            'tenant' => $resolvedTenant ? [
                'id' => $resolvedTenant->id,
                'domain' => $resolvedTenant->domain,
                'db_name' => $resolvedTenant->db_name,
            ] : null,
            'token' => $tokenInfo,
            'resolved_via' => $resolvedVia,
        ]);
    }

    // Local minimal JWT decoder for JTI
    private function getJtiFromJwtLocal(string $jwt): ?string
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return null;
        }
        $payloadB64 = $parts[1] ?? '';
        $b64 = strtr($payloadB64, '-_', '+/');
        $pad = strlen($b64) % 4; if ($pad) { $b64 .= str_repeat('=', 4 - $pad); }
        $json = base64_decode($b64, true);
        if ($json === false) { return null; }
        $payload = json_decode($json, true);
        return is_array($payload) ? ($payload['jti'] ?? null) : null;
    }
    
    
    




	public function logout(Request $request)
	{
		$request->user()->token()->revoke();
		return response()->json(['message' => 'Logged out']);
	}

	public function tenantFromToken(Request $request)
	{
		$currentTenant = tenant();
		if (!$currentTenant) {
			return response()->json(['tenant' => null, 'message' => 'No tenant resolved from token or host'], 200);
		}

		return response()->json([
			'tenant' => [
				'id' => $currentTenant->id,
				'domain' => $currentTenant->domain,
				'db_name' => $currentTenant->db_name,
			],
		]);
	}

	private function ensurePersonalAccessClientExists(): void
	{
		$conn = config('passport.connection') ?: config('passport.storage.database.connection');
        $database = $conn ?: config('database.default');

        if (!Schema::connection($database)->hasTable('oauth_clients') || !Schema::connection($database)->hasTable('oauth_personal_access_clients')) {
            // Attempt to run pending migrations so required tables exist in the configured Passport DB
            $params = ['--force' => true];
            if ($conn) {
                $params['--database'] = $conn;
            }
            Artisan::call('migrate', $params);
        }

		if (!Schema::connection($database)->hasTable('oauth_clients')) {
            return;
        }

		$personalClient = DB::connection($database)->table('oauth_clients')
            ->where('personal_access_client', true)
            ->where(function ($q) {
                $q->whereNull('provider')->orWhere('provider', 'users');
            })
            ->first();

		if (!$personalClient) {
            // Detect oauth_clients.id type
            $clientsIdType = null;
            try {
                $row = DB::connection($database)->selectOne("select data_type from information_schema.columns where table_name = 'oauth_clients' and column_name = 'id'");
                $clientsIdType = $row?->data_type ?? null;
            } catch (\Throwable $e) {}

            if ($clientsIdType === 'uuid') {
                $clientId = (string) Str::uuid();
                DB::connection($database)->table('oauth_clients')->insert([
                    'id' => $clientId,
                    'user_id' => null,
                    'name' => 'Laravel Personal Access Client',
                    'provider' => 'users',
                    'secret' => Str::random(40),
                    'redirect' => 'http://localhost',
                    'personal_access_client' => true,
                    'password_client' => false,
                    'revoked' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $clientId = DB::connection($database)->table('oauth_clients')->insertGetId([
                    'user_id' => null,
                    'name' => 'Laravel Personal Access Client',
                    'provider' => 'users',
                    'secret' => Str::random(40),
                    'redirect' => 'http://localhost',
                    'personal_access_client' => true,
                    'password_client' => false,
                    'revoked' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                if (!is_string($clientId)) {
                    $clientId = (string) $clientId;
                }
            }

            if (Schema::connection($database)->hasTable('oauth_personal_access_clients')) {
                DB::connection($database)->table('oauth_personal_access_clients')->insert([
                    'client_id' => $clientId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            return;
        }

		if (Schema::connection($database)->hasTable('oauth_personal_access_clients')) {
            $centralPacRow = DB::connection($database)->table('oauth_personal_access_clients')
                ->where('client_id', $personalClient->id)
                ->first();
            if (!$centralPacRow) {
                DB::connection($database)->table('oauth_personal_access_clients')->insert([
                    'client_id' => $personalClient->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
	}
}