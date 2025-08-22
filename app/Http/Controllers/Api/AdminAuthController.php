<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
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
		$credentials = $request->validate([
			'email' => 'required|email',
			'password' => 'required|string',
		]);

		$user = User::where('email', $credentials['email'])->first();

		$usePasswordHash = Schema::hasColumn('users', 'password_hash');
		$storedHash = $usePasswordHash ? ($user->password_hash ?? null) : ($user->password ?? null);
		if (!$user || !$storedHash || !Hash::check($credentials['password'], $storedHash)) {
			return response()->json(['message' => 'Invalid credentials'], 401);
		}

		// Ensure a personal access client exists in the central database for the 'users' provider
		$this->ensurePersonalAccessClientExists();

		$token = $user->createToken('admin')->accessToken;

		return response()->json([
			'token_type' => 'Bearer',
			'access_token' => $token,
			'user' => [
				'id' => $user->id,
				'full_name' => $user->full_name,
				'email' => $user->email,
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

	private function ensurePersonalAccessClientExists(): void
	{
		if (!Schema::hasTable('oauth_clients') || !Schema::hasTable('oauth_personal_access_clients')) {
			// Attempt to run pending migrations so required tables exist
			Artisan::call('migrate', ['--force' => true]);
		}

		if (!Schema::hasTable('oauth_clients')) {
			return;
		}

		$personalClient = DB::table('oauth_clients')
			->where('personal_access_client', true)
			->where(function ($q) {
				$q->whereNull('provider')->orWhere('provider', 'users');
			})
			->first();

		if (!$personalClient) {
			$clientId = DB::table('oauth_clients')->insertGetId([
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

			if (Schema::hasTable('oauth_personal_access_clients')) {
				DB::table('oauth_personal_access_clients')->insert([
					'client_id' => $clientId,
					'created_at' => now(),
					'updated_at' => now(),
				]);
			}
			return;
		}

		if (Schema::hasTable('oauth_personal_access_clients')) {
			$centralPacRow = DB::table('oauth_personal_access_clients')
				->where('client_id', $personalClient->id)
				->first();
			if (!$centralPacRow) {
				DB::table('oauth_personal_access_clients')->insert([
					'client_id' => $personalClient->id,
					'created_at' => now(),
					'updated_at' => now(),
				]);
			}
		}
	}
}