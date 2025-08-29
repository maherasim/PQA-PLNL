<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Match organizations to SQL: drop extra columns added previously
        if (Schema::hasTable('organizations')) {
            DB::unprepared(<<<'SQL'
ALTER TABLE organizations
    DROP COLUMN IF EXISTS name,
    DROP COLUMN IF EXISTS identifier_type_id,
    DROP COLUMN IF EXISTS identifier_value,
    DROP COLUMN IF EXISTS address;
SQL);

            // Ensure composite unique index from other migration is removed if present
            DB::unprepared(<<<'SQL'
DO $$
BEGIN
    IF EXISTS (
        SELECT 1 FROM pg_class c WHERE c.relname = 'idx_id_type_company'
    ) THEN
        DROP INDEX idx_id_type_company;
    END IF;
END$$;
SQL);

            // Restore expected unique index name on organizations(email)
            DB::unprepared(<<<'SQL'
DO $$
BEGIN
    IF EXISTS (
        SELECT 1 FROM pg_class c WHERE c.relname = 'idx_organizations_email_unique'
    ) THEN
        ALTER INDEX idx_organizations_email_unique RENAME TO idx_vendors_email_unique;
    END IF;
    IF NOT EXISTS (
        SELECT 1 FROM pg_class c WHERE c.relname = 'idx_vendors_email_unique'
    ) THEN
        CREATE UNIQUE INDEX idx_vendors_email_unique ON organizations (email);
    END IF;
END$$;
SQL);
        }

        // Match organization_documents to SQL: drop non-SQL columns
        if (Schema::hasTable('organization_documents')) {
            DB::unprepared(<<<'SQL'
ALTER TABLE organization_documents
    DROP COLUMN IF EXISTS document_id,
    DROP COLUMN IF EXISTS title,
    DROP COLUMN IF EXISTS s3_bucket,
    DROP COLUMN IF EXISTS s3_key,
    DROP COLUMN IF EXISTS s3_region,
    DROP COLUMN IF EXISTS s3_url,
    DROP COLUMN IF EXISTS file_extension,
    DROP COLUMN IF EXISTS file_size_bytes,
    DROP COLUMN IF EXISTS issue_date,
    DROP COLUMN IF EXISTS issuer,
    DROP COLUMN IF EXISTS document_number,
    DROP COLUMN IF EXISTS custom_metadata,
    DROP COLUMN IF EXISTS uploaded_at;
SQL);

            // Drop indexes specific to removed columns if they exist
            DB::unprepared(<<<'SQL'
DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM pg_class c WHERE c.relname = 'idx_documents_org_type') THEN
        DROP INDEX idx_documents_org_type;
    END IF;
    IF EXISTS (SELECT 1 FROM pg_class c WHERE c.relname = 'idx_documents_s3_location') THEN
        DROP INDEX idx_documents_s3_location;
    END IF;
    IF EXISTS (SELECT 1 FROM pg_class c WHERE c.relname = 'idx_documents_previous_id') THEN
        DROP INDEX idx_documents_previous_id;
    END IF;
END$$;
SQL);
        }
    }

    public function down(): void
    {
        // No-op safe down to avoid reintroducing invalid columns
    }
};

