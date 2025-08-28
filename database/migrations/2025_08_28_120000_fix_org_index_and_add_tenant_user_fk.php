<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rename misnamed unique index on organizations(email) if present
        DB::unprepared(<<<'SQL'
DO $$
BEGIN
    IF EXISTS (
        SELECT 1
        FROM pg_class c
        JOIN pg_namespace n ON n.oid = c.relnamespace
        WHERE c.relkind = 'i'
          AND c.relname = 'idx_vendors_email_unique'
    ) THEN
        ALTER INDEX idx_vendors_email_unique RENAME TO idx_organizations_email_unique;
    END IF;
END$$;
SQL);

        // Ensure the correct unique index exists if none present
        DB::unprepared(<<<'SQL'
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM pg_class c
        JOIN pg_namespace n ON n.oid = c.relnamespace
        WHERE c.relkind = 'i'
          AND c.relname = 'idx_organizations_email_unique'
    ) THEN
        CREATE UNIQUE INDEX idx_organizations_email_unique ON organizations (email);
    END IF;
END$$;
SQL);

        // Add foreign key for tenants.user_id to users.id if both exist and FK not present
        if (Schema::hasTable('tenants') && Schema::hasColumn('tenants', 'user_id') && Schema::hasTable('users')) {
            DB::unprepared(<<<'SQL'
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM pg_constraint con
        JOIN pg_class rel ON rel.oid = con.conrelid
        JOIN pg_namespace nsp ON nsp.oid = rel.relnamespace
        WHERE con.contype = 'f'
          AND rel.relname = 'tenants'
          AND con.conname = 'tenants_user_id_foreign'
    ) THEN
        ALTER TABLE tenants
        ADD CONSTRAINT tenants_user_id_foreign
        FOREIGN KEY (user_id) REFERENCES users(id);
    END IF;
END$$;
SQL);
        }
    }

    public function down(): void
    {
        // Drop FK if present
        if (Schema::hasTable('tenants')) {
            DB::unprepared(<<<'SQL'
DO $$
BEGIN
    IF EXISTS (
        SELECT 1
        FROM pg_constraint con
        JOIN pg_class rel ON rel.oid = con.conrelid
        WHERE con.contype = 'f'
          AND rel.relname = 'tenants'
          AND con.conname = 'tenants_user_id_foreign'
    ) THEN
        ALTER TABLE tenants DROP CONSTRAINT tenants_user_id_foreign;
    END IF;
END$$;
SQL);
        }

        // Optionally rename index back
        DB::unprepared(<<<'SQL'
DO $$
BEGIN
    IF EXISTS (
        SELECT 1
        FROM pg_class c
        WHERE c.relkind = 'i' AND c.relname = 'idx_organizations_email_unique'
    ) THEN
        ALTER INDEX idx_organizations_email_unique RENAME TO idx_vendors_email_unique;
    END IF;
END$$;
SQL);
    }
};

