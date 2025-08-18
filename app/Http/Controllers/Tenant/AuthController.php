<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Status;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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

        $user = User::where('email', $credentials['email'])->first();
        if (!$user || !Hash::check($credentials['password'], $user->password_hash)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

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
}