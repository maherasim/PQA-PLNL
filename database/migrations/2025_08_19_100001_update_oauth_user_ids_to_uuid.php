<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Convert oauth tables user_id to uuid where needed (PostgreSQL syntax)
        try {
            DB::statement("ALTER TABLE oauth_access_tokens ALTER COLUMN user_id TYPE uuid USING NULLIF(user_id::text, '')::uuid");
        } catch (Throwable $e) {}

        try {
            DB::statement("ALTER TABLE oauth_auth_codes ALTER COLUMN user_id TYPE uuid USING user_id::uuid");
        } catch (Throwable $e) {}

        try {
            DB::statement("ALTER TABLE oauth_device_codes ALTER COLUMN user_id TYPE uuid USING NULLIF(user_id::text, '')::uuid");
        } catch (Throwable $e) {}
    }

    public function down(): void
    {
        // No-op: reverting to integer would lose data
    }
};

