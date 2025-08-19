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

class AuthController extends Controller
{
public function register(Request $request)
{
    $data = $request->validate([
        'full_name' => 'required|string|max:100',
        'email' => 'required|email|unique:users,email',
        'mobile_number' => 'nullable|string|max:50|unique:users,mobile_number',
        'password' => 'required|string|min:8',
        // 'mobile_country_id' => 'nullable|exists:countries,id',
        // 'user_country_id' => 'nullable|exists:countries,id',
    ]);

    // Fetch default status (e.g. 'Active')
    // $defaultStatus = Status::first();

    // Roles are managed elsewhere, so not set here
    $user = User::create([
        'full_name' => $data['full_name'],
        'email' => $data['email'],
        'mobile_number' => $data['mobile_number'] ?? null,
        // 'mobile_country_id' => $data['mobile_country_id'] ?? null,
        // 'user_country_id' => $data['user_country_id'] ?? null,
        'password_hash' => Hash::make($data['password']),
        'cvb_id' => 'CVB' . strtoupper(uniqid()),
        // 'status' => $defaultStatus ? $defaultStatus->id : null,
        'password_created_at' => now(),
        'password_last_changed' => now(),
        // Initialize other optional fields as needed
    ]);

    return response()->json([
        'message' => 'Registered successfully',
        'user' => [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'cvb_id' => $user->cvb_id,
        ]
    ], 201);
}


    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        // dd(DB::connection()->getDatabaseName());

        $user = User::where('email', $credentials['email'])->first();

// dd([
//     'user_found' => $user ? true : false,
//     'email' => $credentials['email'],
//     'db' => DB::connection()->getDatabaseName(),
//     'password_check' => $user ? Hash::check($credentials['password'], $user->password_hash) : null,
//     'stored_hash' => $user ? $user->password_hash : null,
// ]);


        $token = $user->createToken('tenant')->accessToken;

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'cvb_id' => $user->cvb_id,
            ],
        ]);
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

        return response()->json(['message' => 'If your email exists, a reset token has been sent.']);
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