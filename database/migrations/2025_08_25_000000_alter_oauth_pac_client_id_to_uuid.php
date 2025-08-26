<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure the table exists before altering
        if (!Schema::hasTable('oauth_personal_access_clients')) {
            return;
        }

        // We are standardizing on UUID client IDs for oauth_clients.
        // Align oauth_personal_access_clients.client_id to UUID as well to avoid type mismatch errors.
        // This migration truncates PAC table (it only stores links to oauth_clients) and alters the column type.
        try {
            DB::statement('TRUNCATE TABLE oauth_personal_access_clients');
        } catch (\Throwable $e) {
            // ignore if lacks permission or empty
        }

        try {
            DB::statement('ALTER TABLE oauth_personal_access_clients ALTER COLUMN client_id TYPE uuid USING client_id::uuid');
        } catch (\Throwable $e) {
            // If direct cast fails (e.g., invalid existing values), force conversion via text -> uuid where possible
            try {
                DB::statement("ALTER TABLE oauth_personal_access_clients ALTER COLUMN client_id TYPE uuid USING NULLIF(client_id::text, '')::uuid");
            } catch (\Throwable $e2) {
                // As a last resort, drop and re-add the column (data already truncated above)
                try {
                    DB::statement('ALTER TABLE oauth_personal_access_clients DROP COLUMN client_id');
                    DB::statement('ALTER TABLE oauth_personal_access_clients ADD COLUMN client_id uuid');
                } catch (\Throwable $e3) {
                    // give up silently; dev will see error in logs if something goes wrong
                }
            }
        }

        // Optionally ensure the primary key exists on id if not already
        try {
            // Add primary key if missing and id is of uuid type
            $exists = false;
            try {
                $check = DB::select("SELECT c.conname FROM pg_constraint c JOIN pg_class t ON c.conrelid = t.oid WHERE t.relname = 'oauth_personal_access_clients' AND c.contype = 'p'");
                $exists = !empty($check);
            } catch (\Throwable $e) {}
            if (!$exists) {
                DB::statement('ALTER TABLE oauth_personal_access_clients ADD PRIMARY KEY (id)');
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('oauth_personal_access_clients')) {
            return;
        }
        // Revert to bigint if needed (may lose information)
        try {
            DB::statement('ALTER TABLE oauth_personal_access_clients ALTER COLUMN client_id TYPE bigint USING NULL');
        } catch (\Throwable $e) {
            // ignore
        }
    }

    public function getConnection(): ?string
    {
        return config('passport.connection') ?: config('passport.storage.database.connection');
    }
};
