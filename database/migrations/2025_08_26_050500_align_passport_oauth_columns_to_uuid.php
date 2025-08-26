<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		$changes = [
			'oauth_access_tokens' => [
				'user_id' => 'uuid',
				'client_id' => 'uuid',
			],
			'oauth_auth_codes' => [
				'user_id' => 'uuid',
				'client_id' => 'uuid',
			],
			'oauth_device_codes' => [
				'user_id' => 'uuid',
				'client_id' => 'uuid',
			],
			// oauth_clients.id is already uuid in our schema; keep user_id nullable bigint as-is (Passport doesn't require it)
		];

		foreach ($changes as $table => $columns) {
			if (!Schema::hasTable($table)) {
				continue;
			}
			foreach ($columns as $column => $type) {
				try {
					// Prefer Postgres-safe cast; if not Postgres, let it fail quietly
					DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE {$type} USING NULLIF({$column}::text, '')::{$type}");
				} catch (\Throwable $e) {
					// Try a generic fallback for MySQL (will likely fail if incompatible); ignore errors to be idempotent
					try {
						DB::statement("ALTER TABLE {$table} MODIFY {$column} CHAR(36) NULL");
					} catch (\Throwable $e2) {
						// swallow
					}
				}
			}
		}
	}

	public function down(): void
	{
		// Intentionally left blank: reverting UUID columns back to numeric types is unsafe
	}

	public function getConnection(): ?string
	{
		return config('passport.connection') ?: config('database.default');
	}
};