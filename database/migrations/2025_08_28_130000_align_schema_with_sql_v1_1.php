<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure required enum types exist
        DB::unprepared(<<<'SQL'
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'document_type') THEN
        CREATE TYPE document_type AS ENUM ('Certificate','License','Contract');
    END IF;
END$$;
SQL);

        // documents
        if (!Schema::hasTable('documents')) {
            Schema::create('documents', function (Blueprint $table) {
                $table->smallIncrements('id');
                $table->string('name', 250);
                $table->enum('type', ['Certificate','License','Contract']);
                $table->text('description')->nullable();
                $table->json('required_fields')->nullable();
                $table->integer('max_file_size_mb')->default(10);
                $table->string('allowed_extensions', 255)->default('pdf,doc,docx,jpg,jpeg,png');
                $table->boolean('is_active')->default(true);
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();
            });
        }

        // identifier_types
        if (!Schema::hasTable('identifier_types')) {
            Schema::create('identifier_types', function (Blueprint $table) {
                $table->smallIncrements('id');
                $table->string('code', 20)->unique();
                $table->string('name', 100);
                $table->text('description')->nullable();
                $table->string('format_pattern', 200)->nullable();
                $table->string('validation_regex', 500)->nullable();
                $table->uuid('country_id')->nullable();
                $table->boolean('is_global')->default(false);
                $table->boolean('is_active')->default(false);
                $table->integer('priority_score')->default(50);
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();
            });
        }

        // categories (hierarchy reference table)
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->unsignedBigInteger('id')->primary();
                $table->unsignedBigInteger('parent_key')->nullable();
                $table->string('code', 20);
                $table->string('title', 500);
                $table->boolean('is_active')->default(true);
                $table->integer('level');
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();
            });
        }

        // status_transitions
        if (!Schema::hasTable('status_transitions')) {
            Schema::create('status_transitions', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
                $table->string('scope', 50);
                $table->uuid('from_status_id')->nullable();
                $table->uuid('to_status_id');
                $table->boolean('is_active')->default(true);
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();
            });
        }

        // organization_categories
        if (!Schema::hasTable('organization_categories')) {
            Schema::create('organization_categories', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
                $table->uuid('organization_id');
                $table->unsignedBigInteger('category_key');
                $table->boolean('is_primary')->default(false);
                $table->boolean('is_active')->default(true);
                $table->string('expertise_level', 20)->nullable();
                $table->date('start_date');
                $table->text('description')->nullable();
                $table->timestampTz('created_at')->useCurrent();
                $table->uuid('created_by');
                $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();
                $table->uuid('updated_by')->nullable();
            });
        }

        // vendor_invitations
        if (!Schema::hasTable('vendor_invitations')) {
            Schema::create('vendor_invitations', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
                $table->uuid('tenant_id');
                $table->uuid('existing_user_id')->nullable();
                $table->uuid('existing_organization_id')->nullable();
                $table->string('temp_company_name', 255)->nullable();
                $table->string('temp_vendor_manager_name', 255)->nullable();
                $table->string('temp_vendor_manager_email', 255)->nullable();
                $table->string('invitation_token', 255)->unique();
                $table->uuid('status');
                $table->timestamp('expires_at');
                $table->timestampTz('created_at')->useCurrent();
                $table->uuid('created_by');
                $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();
                $table->uuid('updated_by')->nullable();
                $table->timestampTz('canceled_at')->nullable();
                $table->uuid('canceled_by')->nullable();
            });
        }

        // countries: add columns and indexes
        if (Schema::hasTable('countries')) {
            Schema::table('countries', function (Blueprint $table) {
                if (!Schema::hasColumn('countries', 'name_common')) {
                    $table->string('name_common', 100)->nullable();
                }
                if (!Schema::hasColumn('countries', 'name_official')) {
                    $table->string('name_official', 200)->nullable();
                }
                if (!Schema::hasColumn('countries', 'iso_alpha2')) {
                    $table->char('iso_alpha2', 2)->nullable();
                }
                if (!Schema::hasColumn('countries', 'iso_alpha3')) {
                    $table->char('iso_alpha3', 3)->nullable();
                }
                if (!Schema::hasColumn('countries', 'iso_numeric')) {
                    $table->char('iso_numeric', 3)->nullable();
                }
            });
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS idx_countries_iso_alpha2 ON countries (iso_alpha2)');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS idx_countries_iso_alpha3 ON countries (iso_alpha3)');
        }

        // status: add columns
        if (Schema::hasTable('status')) {
            Schema::table('status', function (Blueprint $table) {
                if (!Schema::hasColumn('status', 'scope')) {
                    $table->string('scope', 50)->nullable();
                }
                if (!Schema::hasColumn('status', 'code')) {
                    $table->string('code', 50)->nullable();
                }
                if (!Schema::hasColumn('status', 'description')) {
                    $table->string('description', 150)->nullable();
                }
                if (!Schema::hasColumn('status', 'is_initial')) {
                    $table->boolean('is_initial')->default(false);
                }
                if (!Schema::hasColumn('status', 'is_active')) {
                    $table->boolean('is_active')->default(true);
                }
                if (!Schema::hasColumn('status', 'created_at')) {
                    $table->timestampTz('created_at')->useCurrent();
                }
                if (!Schema::hasColumn('status', 'updated_at')) {
                    $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();
                }
            });
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS idx_status_scope_code ON status (scope, code)');
        }

        // users: add columns
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'email_verified')) {
                    $table->boolean('email_verified')->default(false);
                }
                if (!Schema::hasColumn('users', 'last_organization_id')) {
                    $table->uuid('last_organization_id')->nullable();
                }
                if (!Schema::hasColumn('users', 'excluded_at')) {
                    $table->timestampTz('excluded_at')->nullable();
                }
                if (!Schema::hasColumn('users', 'excluded_by')) {
                    $table->uuid('excluded_by')->nullable();
                }
            });
            DB::unprepared(<<<'SQL'
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint con
        JOIN pg_class rel ON rel.oid = con.conrelid
        WHERE con.contype='f' AND rel.relname='users' AND con.conname='users_last_organization_id_foreign'
    ) THEN
        ALTER TABLE users ADD CONSTRAINT users_last_organization_id_foreign FOREIGN KEY (last_organization_id) REFERENCES organizations(id);
    END IF;
END$$;
SQL);
        }

        // organizations: add columns
        if (Schema::hasTable('organizations')) {
            Schema::table('organizations', function (Blueprint $table) {
                if (!Schema::hasColumn('organizations', 'name')) {
                    $table->string('name', 250)->nullable();
                }
                if (!Schema::hasColumn('organizations', 'identifier_type_id')) {
                    $table->smallInteger('identifier_type_id')->nullable();
                }
                if (!Schema::hasColumn('organizations', 'identifier_value')) {
                    $table->string('identifier_value', 50)->nullable();
                }
                if (!Schema::hasColumn('organizations', 'address')) {
                    $table->string('address', 200)->nullable();
                }
            });
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS idx_id_type_company ON organizations (country_id, identifier_type_id, identifier_value)');
        }

        // organization_documents: add columns aligning to SQL
        if (Schema::hasTable('organization_documents')) {
            Schema::table('organization_documents', function (Blueprint $table) {
                if (!Schema::hasColumn('organization_documents', 'document_id')) {
                    $table->smallInteger('document_id');
                }
                if (!Schema::hasColumn('organization_documents', 'title')) {
                    $table->string('title', 255);
                }
                if (!Schema::hasColumn('organization_documents', 'description')) {
                    $table->text('description')->nullable();
                }
                if (!Schema::hasColumn('organization_documents', 's3_bucket')) {
                    $table->string('s3_bucket', 255);
                }
                if (!Schema::hasColumn('organization_documents', 's3_key')) {
                    $table->string('s3_key', 500);
                }
                if (!Schema::hasColumn('organization_documents', 's3_region')) {
                    $table->string('s3_region', 50);
                }
                if (!Schema::hasColumn('organization_documents', 's3_url')) {
                    $table->text('s3_url')->nullable();
                }
                if (!Schema::hasColumn('organization_documents', 'file_extension')) {
                    $table->string('file_extension', 10);
                }
                if (!Schema::hasColumn('organization_documents', 'file_size_bytes')) {
                    $table->bigInteger('file_size_bytes');
                }
                if (!Schema::hasColumn('organization_documents', 'issue_date')) {
                    $table->date('issue_date')->nullable();
                }
                if (!Schema::hasColumn('organization_documents', 'issuer')) {
                    $table->string('issuer', 255)->nullable();
                }
                if (!Schema::hasColumn('organization_documents', 'document_number')) {
                    $table->string('document_number', 100)->nullable();
                }
                if (!Schema::hasColumn('organization_documents', 'custom_metadata')) {
                    $table->json('custom_metadata')->nullable();
                }
                if (!Schema::hasColumn('organization_documents', 'previous_document_id')) {
                    $table->uuid('previous_document_id')->nullable();
                }
                if (!Schema::hasColumn('organization_documents', 'uploaded_at')) {
                    $table->timestamp('uploaded_at')->useCurrent();
                }
            });
            DB::statement('CREATE INDEX IF NOT EXISTS idx_documents_org_type ON organization_documents (organization_id, document_id)');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS idx_documents_s3_location ON organization_documents (s3_bucket, s3_key)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_documents_previous_id ON organization_documents (previous_document_id)');
        }

        // Indexes required by SQL dump
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS idx_users_email_unique ON users (email)');
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS unique_org_category ON organization_categories (organization_id, category_key)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_org_active_categories ON organization_categories (organization_id, is_active)');

        // Foreign keys for new/altered tables
        DB::unprepared(<<<'SQL'
DO $$
BEGIN
    -- identifier_types.country_id -> countries.id
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint WHERE conname = 'identifier_types_country_id_foreign'
    ) THEN
        ALTER TABLE identifier_types ADD CONSTRAINT identifier_types_country_id_foreign FOREIGN KEY (country_id) REFERENCES countries(id);
    END IF;

    -- categories.parent_key -> categories.id
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint WHERE conname = 'categories_parent_key_foreign'
    ) THEN
        ALTER TABLE categories ADD CONSTRAINT categories_parent_key_foreign FOREIGN KEY (parent_key) REFERENCES categories(id);
    END IF;

    -- status_transitions from/to -> status.id
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint WHERE conname = 'status_transitions_from_status_id_foreign'
    ) THEN
        ALTER TABLE status_transitions ADD CONSTRAINT status_transitions_from_status_id_foreign FOREIGN KEY (from_status_id) REFERENCES status(id);
    END IF;
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint WHERE conname = 'status_transitions_to_status_id_foreign'
    ) THEN
        ALTER TABLE status_transitions ADD CONSTRAINT status_transitions_to_status_id_foreign FOREIGN KEY (to_status_id) REFERENCES status(id);
    END IF;

    -- organizations.identifier_type_id -> identifier_types.id
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint WHERE conname = 'organizations_identifier_type_id_foreign'
    ) THEN
        ALTER TABLE organizations ADD CONSTRAINT organizations_identifier_type_id_foreign FOREIGN KEY (identifier_type_id) REFERENCES identifier_types(id);
    END IF;

    -- organization_documents relations
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint WHERE conname = 'organization_documents_document_id_foreign'
    ) THEN
        ALTER TABLE organization_documents ADD CONSTRAINT organization_documents_document_id_foreign FOREIGN KEY (document_id) REFERENCES documents(id);
    END IF;
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint WHERE conname = 'organization_documents_previous_document_id_foreign'
    ) THEN
        ALTER TABLE organization_documents ADD CONSTRAINT organization_documents_previous_document_id_foreign FOREIGN KEY (previous_document_id) REFERENCES organization_documents(id);
    END IF;

    -- organization_categories relations
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint WHERE conname = 'organization_categories_organization_id_foreign'
    ) THEN
        ALTER TABLE organization_categories ADD CONSTRAINT organization_categories_organization_id_foreign FOREIGN KEY (organization_id) REFERENCES organizations(id);
    END IF;
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint WHERE conname = 'organization_categories_category_key_foreign'
    ) THEN
        ALTER TABLE organization_categories ADD CONSTRAINT organization_categories_category_key_foreign FOREIGN KEY (category_key) REFERENCES categories(id);
    END IF;
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint WHERE conname = 'organization_categories_created_by_foreign'
    ) THEN
        ALTER TABLE organization_categories ADD CONSTRAINT organization_categories_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id);
    END IF;
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint WHERE conname = 'organization_categories_updated_by_foreign'
    ) THEN
        ALTER TABLE organization_categories ADD CONSTRAINT organization_categories_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES users(id);
    END IF;

    -- vendor_invitations relations
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint WHERE conname = 'vendor_invitations_tenant_id_foreign'
    ) THEN
        ALTER TABLE vendor_invitations ADD CONSTRAINT vendor_invitations_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES tenants(id);
    END IF;
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint WHERE conname = 'vendor_invitations_existing_user_id_foreign'
    ) THEN
        ALTER TABLE vendor_invitations ADD CONSTRAINT vendor_invitations_existing_user_id_foreign FOREIGN KEY (existing_user_id) REFERENCES users(id);
    END IF;
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint WHERE conname = 'vendor_invitations_existing_organization_id_foreign'
    ) THEN
        ALTER TABLE vendor_invitations ADD CONSTRAINT vendor_invitations_existing_organization_id_foreign FOREIGN KEY (existing_organization_id) REFERENCES organizations(id);
    END IF;
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint WHERE conname = 'vendor_invitations_status_foreign'
    ) THEN
        ALTER TABLE vendor_invitations ADD CONSTRAINT vendor_invitations_status_foreign FOREIGN KEY (status) REFERENCES status(id);
    END IF;
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint WHERE conname = 'vendor_invitations_created_by_foreign'
    ) THEN
        ALTER TABLE vendor_invitations ADD CONSTRAINT vendor_invitations_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id);
    END IF;
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint WHERE conname = 'vendor_invitations_updated_by_foreign'
    ) THEN
        ALTER TABLE vendor_invitations ADD CONSTRAINT vendor_invitations_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES users(id);
    END IF;
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint WHERE conname = 'vendor_invitations_canceled_by_foreign'
    ) THEN
        ALTER TABLE vendor_invitations ADD CONSTRAINT vendor_invitations_canceled_by_foreign FOREIGN KEY (canceled_by) REFERENCES users(id);
    END IF;
END$$;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_invitations');
        Schema::dropIfExists('organization_categories');
        Schema::dropIfExists('status_transitions');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('identifier_types');
        Schema::dropIfExists('documents');

        // Do not drop added columns to avoid data loss; enums left as-is
    }
};

