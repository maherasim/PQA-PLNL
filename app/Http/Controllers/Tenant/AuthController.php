<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Status;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use App\Notifications\TenantPasswordResetToken;
use Stancl\Tenancy\Tenancy;

class AuthController extends Controller
{
public function register(Request $request)
{
    return response()->json(['message' => 'Registration is managed centrally.'], 405);
}


    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Capture current tenant from subdomain, then switch to central DB for auth & token issuance
        $currentTenant = tenant();
        if (!$currentTenant) {
            return response()->json(['message' => 'Tenant context missing. Use tenant subdomain for login.'], 400);
        }

        tenancy()->end();

        try {
            $user = User::where('email', $credentials['email'])->first();
            if (!$user || !Hash::check($credentials['password'], $user->password_hash)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            // Encode tenant id (and domain) in token name so middleware can resolve tenancy from the token later
            $tokenName = 'tenant:' . $currentTenant->id . ';domain:' . $currentTenant->domain;
            $personalToken = $user->createToken($tokenName);
            $accessToken = $personalToken->accessToken;

            return response()->json([
                'token_type' => 'Bearer',
                'access_token' => $accessToken,
                'user' => [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'cvb_id' => $user->cvb_id,
                ],
            ]);
        } finally {
            // Restore tenant context for the remainder of the request lifecycle if needed
            if ($currentTenant) {
                tenancy()->initialize($currentTenant);
            }
        }
    }

    public function me(Request $request)
    {
        return response()->json(['user' => $request->user()]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json(['message' => 'Logged out']);
    }

    public function forgotPassword(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $data['email'])->first();
      //  dd(  $user);
        if (!$user) {
            return response()->json(['message' => 'Email not found'], 404);
        }

        // Create or update token in tenant's password_reset_tokens table
        $token = Str::random(64);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => hash('sha256', $token), 'created_at' => now()]
        );

        // Send email notification with the raw token
        Notification::route('mail', $user->email)->notify(new TenantPasswordResetToken($token));

        return response()->json(['message' => 'A reset token has been sent.']);
    }

    public function verifyResetToken(Request $request)
    {
        $data = $request->validate([
            'token' => 'required|string',
        ]);

        $hashed = hash('sha256', $data['token']);
        $record = DB::table('password_reset_tokens')->where('token', $hashed)->first();

        if (!$record) {
            return response()->json(['valid' => false, 'reason' => 'invalid'], 200);
        }

        // 30 minutes validity
        $expired = now()->diffInMinutes($record->created_at) > 30;
        if ($expired) {
            return response()->json(['valid' => false, 'reason' => 'expired'], 200);
        }

        return response()->json(['valid' => true]);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $hashed = hash('sha256', $data['token']);
        $record = DB::table('password_reset_tokens')->where('token', $hashed)->first();

        if (!$record) {
            return response()->json(['message' => 'Invalid or expired token'], 422);
        }

        // 30 minutes validity
        if (now()->diffInMinutes($record->created_at) > 30) {
            return response()->json(['message' => 'Invalid or expired token'], 422);
        }

        $user = User::where('email', $record->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Invalid token'], 422);
        }

        $user->password_hash = Hash::make($data['password']);
        $user->password_last_changed = now();
        $user->password_change_required = false;
        $user->save();

        // Delete token after successful reset
        DB::table('password_reset_tokens')->where('email', $record->email)->delete();

        return response()->json(['message' => 'Password has been reset successfully']);
    }
}