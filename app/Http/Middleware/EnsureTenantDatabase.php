<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Laravel\Passport\Token;
use Illuminate\Support\Str;

class EnsureTenantDatabase
{
    public function handle(Request $request, Closure $next)
    {
        // If tenancy already initialized by earlier middleware, continue
        if (tenant()) {
            return $next($request);
        }

        // 1) Try to resolve tenant by request host (domain-based login, e.g., nasir.127.0.0.1.nip.io)
        $host = $request->getHost();
        if ($host) {
            $tenant = Tenant::where('domain', $host)->first();
            if ($tenant) {
                tenancy()->initialize($tenant);
                return $next($request);
            }
        }

        // 2) Fallback: resolve tenant from bearer token name convention set at login: 'tenant:{id};domain:{domain}'
        $bearer = $request->bearerToken();
        // Also support token via query/body param when header is not available
        if (!$bearer) {
            $queryToken = $request->query('token') ?? $request->query('access_token') ?? $request->input('token') ?? $request->input('access_token');
            if ($queryToken) {
                $bearer = $queryToken;
                // Make it available to downstream guards (Passport TokenGuard)
                $request->headers->set('Authorization', 'Bearer ' . $bearer);
            }
        }
        if ($bearer) {
            $jti = $this->getJtiFromJwt($bearer);
            if ($jti) {
                // Passport 12 compatible: use Token model instead of TokenRepository
                $token = Token::find($jti);
                if ($token) {
                    $name = (string) $token->name;
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
                }
            }
        }

        return $next($request);
    }

    private function getJtiFromJwt(string $jwt): ?string
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return null;
        }

        [$headerB64, $payloadB64] = [$parts[0], $parts[1]];
        $payloadJson = $this->base64UrlDecode($payloadB64);
        if (!$payloadJson) {
            return null;
        }

        $payload = json_decode($payloadJson, true);
        return is_array($payload) ? ($payload['jti'] ?? null) : null;
    }

    private function base64UrlDecode(string $data): ?string
    {
        $b64 = strtr($data, '-_', '+/');
        $pad = strlen($b64) % 4;
        if ($pad) {
            $b64 .= str_repeat('=', 4 - $pad);
        }

        $decoded = base64_decode($b64, true);
        return $decoded === false ? null : $decoded;
    }
}
