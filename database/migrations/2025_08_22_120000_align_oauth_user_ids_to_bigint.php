<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Align OAuth tables' user_id to bigint to match central users.id
        if (Schema::hasTable('oauth_access_tokens')) {
            try {
                DB::statement("ALTER TABLE oauth_access_tokens ALTER COLUMN user_id DROP NOT NULL");
            } catch (Throwable $e) {}
            try {
                DB::statement("ALTER TABLE oauth_access_tokens ALTER COLUMN user_id TYPE bigint USING NULL");
            } catch (Throwable $e) {}
        }

        if (Schema::hasTable('oauth_auth_codes')) {
            try {
                DB::statement("ALTER TABLE oauth_auth_codes ALTER COLUMN user_id DROP NOT NULL");
            } catch (Throwable $e) {}
            try {
                DB::statement("ALTER TABLE oauth_auth_codes ALTER COLUMN user_id TYPE bigint USING NULL");
            } catch (Throwable $e) {}
        }

        if (Schema::hasTable('oauth_device_codes')) {
            try {
                DB::statement("ALTER TABLE oauth_device_codes ALTER COLUMN user_id DROP NOT NULL");
            } catch (Throwable $e) {}
            try {
                DB::statement("ALTER TABLE oauth_device_codes ALTER COLUMN user_id TYPE bigint USING NULL");
            } catch (Throwable $e) {}
        }
    }

    public function down(): void
    {
        // No-op: converting back to uuid is not supported here
    }

    public function getConnection(): ?string
    {
        return $this->connection ?? config('passport.connection');
    }
};