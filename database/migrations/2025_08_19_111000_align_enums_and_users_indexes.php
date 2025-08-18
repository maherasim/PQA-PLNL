<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Align subscription_plans.billing_cycle to use PostgreSQL enum type
        DB::unprepared(<<<'SQL'
DO $$
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_name = 'subscription_plans' AND column_name = 'billing_cycle'
    ) THEN
        BEGIN
            ALTER TABLE subscription_plans
            ALTER COLUMN billing_cycle TYPE billing_cycle_enum USING billing_cycle::billing_cycle_enum,
            ALTER COLUMN billing_cycle SET DEFAULT 'monthly'::billing_cycle_enum;
        EXCEPTION WHEN others THEN
            -- ignore if already the correct type
        END;
    END IF;
END$$;
SQL);

        // Align vendors.vendor_type to use PostgreSQL enum type
        DB::unprepared(<<<'SQL'
DO $$
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_name = 'vendors' AND column_name = 'vendor_type'
    ) THEN
        BEGIN
            ALTER TABLE vendors
            ALTER COLUMN vendor_type TYPE vendor_type_enum USING vendor_type::vendor_type_enum;
        EXCEPTION WHEN others THEN
            -- ignore if already the correct type
        END;
    END IF;
END$$;
SQL);

        // Ensure named unique indexes on users
        DB::unprepared(<<<'SQL'
DO $$
BEGIN
    -- Drop default unique constraints if they exist to avoid duplicate unique indexes
    IF EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'users_email_unique') THEN
        ALTER TABLE users DROP CONSTRAINT users_email_unique;
    END IF;
    IF EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'users_cvb_id_unique') THEN
        ALTER TABLE users DROP CONSTRAINT users_cvb_id_unique;
    END IF;
END$$;
SQL);

        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS idx_users_email_unique ON users (email)');
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS idx_users_cvb_unique ON users (cvb_id)');
    }

    public function down(): void
    {
        // Revert named indexes (keeping uniqueness enforced by named indexes may be preferred)
        DB::statement('DROP INDEX IF EXISTS idx_users_email_unique');
        DB::statement('DROP INDEX IF EXISTS idx_users_cvb_unique');
    }
};

