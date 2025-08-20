<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Laravel\Passport\TokenRepository;
use League\OAuth2\Server\ResourceServer;
use Illuminate\Support\Str;

class EnsureTenantDatabase
{
    public function __construct(
        private TokenRepository $tokens,
        private ResourceServer $server
    ) {}

    public function handle(Request $request, Closure $next)
    {
        // If tenancy already initialized by domain, continue
        if (tenant()) {
            return $next($request);
        }

        // Attempt to resolve tenant from bearer token name convention set at login: 'tenant:{id};domain:{domain}'
        $bearer = $request->bearerToken();
        if (!$bearer) {
            return $next($request);
        }

        // Let Passport parse and locate the token record, then read the token name
        $tokenId = substr($bearer, 0, 80);
        $token = $this->tokens->find($tokenId);
        if (!$token) {
            return $next($request);
        }

        $name = (string) $token->name;
        // Expected format: 'tenant:{uuid};domain:{fqdn}'
        if (Str::startsWith($name, 'tenant:')) {
            $parts = collect(explode(';', $name))
                ->map(fn($p) => explode(':', $p, 2))
                ->filter(fn($kv) => count($kv) === 2)
                ->mapWithKeys(fn($kv) => [trim($kv[0]) => trim($kv[1])]);

            $tenantId = $parts->get('tenant');
            if ($tenantId) {
                $tenant = Tenant::find($tenantId);
                if ($tenant) {
                    tenancy()->initialize($tenant);
                }
            }
        }

        return $next($request);
    }
}
