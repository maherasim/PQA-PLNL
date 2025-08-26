<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Convert oauth tables user_id to bigint to match users.id
        $tables = [
            'oauth_access_tokens' => 'user_id',
            'oauth_auth_codes' => 'user_id',
            'oauth_device_codes' => 'user_id',
        ];

        foreach ($tables as $table => $column) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            try {
                DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE bigint USING NULLIF({$column}::text, '')::bigint");
            } catch (\Throwable $e) {
                // ignore and continue
            }
        }
    }

    public function down(): void
    {
        // Revert to uuid if needed (not recommended)
        $tables = [
            'oauth_access_tokens' => 'user_id',
            'oauth_auth_codes' => 'user_id',
            'oauth_device_codes' => 'user_id',
        ];
        foreach ($tables as $table => $column) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            try {
                DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE uuid USING NULLIF({$column}::text, '')::uuid");
            } catch (\Throwable $e) {}
        }
    }

    public function getConnection(): ?string
    {
        return config('passport.connection') ?: config('passport.storage.database.connection');
    }
};
