<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL: ensure uuid extension exists
        DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');

        // Create enum types if not exists
        DB::unprepared(<<<'SQL'
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'vendor_type_enum') THEN
        CREATE TYPE vendor_type_enum AS ENUM ('supplier', 'contractor', 'both');
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'billing_cycle_enum') THEN
        CREATE TYPE billing_cycle_enum AS ENUM ('monthly', 'quarterly', 'yearly');
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'address_type_enum') THEN
        CREATE TYPE address_type_enum AS ENUM ('headquarters', 'billing', 'operational', 'warehouse', 'branch');
    END IF;
END$$;
SQL);

        // Countries
        if (!Schema::hasTable('countries')) {
            Schema::create('countries', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
                $table->string('name', 100);
                $table->string('code', 3)->unique();
                $table->smallInteger('phone_code')->nullable();
                $table->string('currency_code', 3)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();
            });

            DB::statement("COMMENT ON COLUMN countries.id IS 'UUID v4 - Low volume reference table'");
            DB::statement("COMMENT ON COLUMN countries.phone_code IS 'CHECK (phone_code >= 1 AND phone_code <= 999)'");
            // Explicit check constraint matching the comment
            DB::statement('ALTER TABLE countries ADD CONSTRAINT chk_countries_phone_code CHECK (phone_code IS NULL OR (phone_code >= 1 AND phone_code <= 999))');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS idx_countries_code_unique ON countries (code)');
        }

        // Status
        if (!Schema::hasTable('status')) {
            Schema::create('status', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
                $table->string('status_name', 50);
            });
            DB::statement("COMMENT ON COLUMN status.id IS 'UUID v4 - Low volume reference table'");
            DB::statement("COMMENT ON COLUMN status.status_name IS 'Active, Blocked, Pending Registration'");
        }

        // Industry
        if (!Schema::hasTable('industry')) {
            Schema::create('industry', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
                $table->string('industry_name', 50);
            });
            DB::statement("COMMENT ON COLUMN industry.id IS 'UUID v4 - Low volume reference table'");
            DB::statement("COMMENT ON COLUMN industry.industry_name IS 'Active, Blocked, Pending Registration'");
        }

        // Users (drop incompatible table if present for clean slate)
        if (Schema::hasTable('users')) {
            Schema::drop('users');
        }
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->string('email', 255)->unique();
            $table->timestampTz('email_verified_at')->nullable();
            $table->string('full_name', 100);
            $table->uuid('user_country_id')->nullable();
            $table->uuid('mobile_country_id')->nullable();
            $table->string('mobile_number', 50)->nullable();
            $table->uuid('status');
            $table->timestampTz('last_login_at')->nullable();
            $table->string('cvb_id', 50)->unique();
            $table->string('cvb_number', 50)->nullable();
            $table->string('password_hash', 255);
            $table->timestampTz('password_created_at')->nullable()->useCurrent();
            $table->timestampTz('password_last_changed')->nullable()->useCurrent();
            $table->timestampTz('password_expires_at')->nullable();
            $table->boolean('password_change_required')->default(false);
            $table->integer('failed_login_attempts')->default(0);
            $table->timestampTz('last_failed_login')->nullable();
            $table->timestampTz('account_locked_until')->nullable();
            $table->timestampTz('last_successful_login')->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();
            $table->timestampTz('deleted_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
        });
        DB::statement("COMMENT ON COLUMN users.id IS 'UUID v4 - Sensitive user data requires maximum security'");
        DB::statement("COMMENT ON COLUMN users.cvb_id IS 'Central Vendor Bureau ID - Required for compliance'");
        DB::statement("COMMENT ON COLUMN users.password_hash IS 'bcrypt/Argon2id hashed - NEVER plain text'");

        // Organizations
        if (!Schema::hasTable('organizations')) {
            Schema::create('organizations', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
                $table->string('company_name', 255);
                $table->string('website', 255)->nullable();
                $table->uuid('country_id')->nullable();
                $table->uuid('phone_country_id')->nullable();
                $table->string('phone', 50)->nullable();
                $table->string('logo_url', 255)->nullable();
                $table->string('email', 255)->unique();
                $table->text('description')->nullable();
                $table->uuid('industry_id');
                $table->text('mailing_address')->nullable();
                $table->uuid('status');
                $table->string('cvb_registration_number', 100)->unique();
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->nullable();
                $table->timestampTz('deleted_at')->nullable();
                $table->uuid('created_by');
                $table->uuid('updated_by')->nullable();
                $table->uuid('deleted_by')->nullable();
            });
            DB::statement("COMMENT ON TABLE organizations IS 'External organization that relates to the CVBTT or other organizations (vendors)'");
            DB::statement("COMMENT ON COLUMN organizations.id IS 'UUID v4 - Sensitive vendor data requires maximum security'");
            DB::statement("COMMENT ON COLUMN organizations.cvb_registration_number IS 'Central Vendor Bureau registration - Required'");
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS idx_vendors_email_unique ON organizations (email)');
        }

        // Organization addresses
        if (!Schema::hasTable('organization_addresses')) {
            Schema::create('organization_addresses', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
                $table->uuid('organization_id');
                $table->string('type');
                $table->uuid('status');
                $table->string('address_line_1', 255);
                $table->string('address_line_2', 255)->nullable();
                $table->string('city', 100);
                $table->string('state', 100);
                $table->string('postal_code', 20);
                $table->uuid('country_id');
                $table->boolean('is_primary')->default(false);
                $table->boolean('is_mail_address')->default(false);
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->nullable();
                $table->uuid('created_by');
                $table->uuid('updated_by')->nullable();
            });
            DB::statement("COMMENT ON TABLE organization_addresses IS 'Organization addresses'");
        }

        // Organization documents
        if (!Schema::hasTable('organization_documents')) {
            Schema::create('organization_documents', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
                $table->uuid('organization_id');
                $table->uuid('status');
                $table->string('document_type');
                $table->string('name', 255);
                $table->string('original_filename', 255);
                $table->string('file_path', 500);
                $table->bigInteger('file_size');
                $table->string('mime_type', 100);
                $table->string('checksum', 64);
                $table->uuid('uploaded_by');
                $table->uuid('reviewed_by')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->boolean('is_required')->default(true);
                $table->boolean('is_shareable')->default(false);
                $table->integer('version')->default(1);
                $table->uuid('previous_version_id')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            });
            DB::statement("COMMENT ON TABLE organization_documents IS 'Documentos das organizations - RLS habilitado'");
        }

        // Tenants (central)
        if (Schema::hasTable('tenants')) {
            // Ensure id is uuid and required columns exist
            Schema::table('tenants', function (Blueprint $table) {
                // Convert id to uuid if not already. Direct type alteration requires raw SQL.
            });
            // Raw conversions for PostgreSQL
            DB::beginTransaction();
            try {
                // Change id to uuid if current type is not uuid
                DB::statement("ALTER TABLE tenants ALTER COLUMN id TYPE uuid USING id::uuid");
            } catch (Throwable $e) {
                // ignore if already uuid
            }
            Schema::table('tenants', function (Blueprint $table) {
                if (!Schema::hasColumn('tenants', 'organization_id')) {
                    $table->uuid('organization_id')->nullable();
                }
                if (!Schema::hasColumn('tenants', 'status')) {
                    $table->uuid('status');
                }
                if (!Schema::hasColumn('tenants', 'domain')) {
                    $table->string('domain', 255)->nullable();
                }
                if (!Schema::hasColumn('tenants', 'db_address')) {
                    $table->string('db_address', 100)->nullable();
                }
                if (!Schema::hasColumn('tenants', 'db_name')) {
                    $table->string('db_name', 50)->nullable();
                }
                if (!Schema::hasColumn('tenants', 'deleted_at')) {
                    $table->timestampTz('deleted_at')->nullable();
                }
                if (!Schema::hasColumn('tenants', 'created_by')) {
                    $table->uuid('created_by')->nullable();
                }
                if (!Schema::hasColumn('tenants', 'updated_by')) {
                    $table->uuid('updated_by')->nullable();
                }
                if (!Schema::hasColumn('tenants', 'deleted_by')) {
                    $table->uuid('deleted_by')->nullable();
                }
                if (!Schema::hasColumn('tenants', 'created_at')) {
                    $table->timestampTz('created_at')->useCurrent();
                }
                if (!Schema::hasColumn('tenants', 'updated_at')) {
                    $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();
                }
                if (Schema::hasColumn('tenants', 'data')) {
                    $table->dropColumn('data');
                }
            });
            DB::statement("COMMENT ON TABLE tenants IS 'Tenant management table'");
            DB::statement("COMMENT ON COLUMN tenants.domain IS 'Related to subdomain of the tenant'");
            DB::statement("COMMENT ON COLUMN tenants.db_address IS 'Used in case of different server address for the tenant'");
            DB::statement("COMMENT ON COLUMN tenants.db_name IS 'Used in case a tenant has its own database (database per tenant)'");
        } else {
            Schema::create('tenants', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
                $table->uuid('organization_id')->nullable();
                $table->uuid('status');
                $table->string('domain', 255)->nullable();
                $table->string('db_address', 100)->nullable();
                $table->string('db_name', 50)->nullable();
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();
                $table->timestampTz('deleted_at')->nullable();
                $table->uuid('created_by')->nullable();
                $table->uuid('updated_by')->nullable();
                $table->uuid('deleted_by')->nullable();
            });
            DB::statement("COMMENT ON TABLE tenants IS 'Tenant management table'");
            DB::statement("COMMENT ON COLUMN tenants.domain IS 'Related to subdomain of the tenant'");
            DB::statement("COMMENT ON COLUMN tenants.db_address IS 'Used in case of different server address for the tenant'");
            DB::statement("COMMENT ON COLUMN tenants.db_name IS 'Used in case a tenant has its own database (database per tenant)'");
        }

        // Supply categories
        if (!Schema::hasTable('supply_categories')) {
            Schema::create('supply_categories', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
                $table->uuid('tenant_id');
                $table->string('name', 255);
                $table->string('code', 50);
                $table->text('description')->nullable();
                $table->uuid('parent_id')->nullable();
                $table->integer('level')->default(1);
                $table->boolean('is_active')->default(true);
                $table->boolean('requires_special_qualification')->default(false);
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
                $table->timestamp('deleted_at')->nullable();
            });
            DB::statement("COMMENT ON TABLE supply_categories IS 'Review this table'");
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS supply_categories_tenant_code_unique ON supply_categories (tenant_id, code)');
            DB::statement('CREATE INDEX IF NOT EXISTS supply_categories_tenant_parent_idx ON supply_categories (tenant_id, parent_id)');
        }

        // Subscription plans
        if (!Schema::hasTable('subscription_plans')) {
            Schema::create('subscription_plans', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
                $table->string('name', 255);
                $table->text('description')->nullable();
                $table->decimal('price', 10, 2);
                $table->enum('billing_cycle', ['monthly', 'quarterly', 'yearly'])->default('monthly');
                $table->integer('trial_days')->default(0);
                $table->uuid('status');
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->nullable();
                $table->timestampTz('deleted_at')->nullable();
                $table->uuid('created_by');
                $table->uuid('updated_by')->nullable();
                $table->uuid('deleted_by')->nullable();
            });
            DB::statement("COMMENT ON TABLE subscription_plans IS 'Avaliable subscription plans'");
        }

        // Subscriptions
        if (!Schema::hasTable('subscriptions')) {
            Schema::create('subscriptions', function (Blueprint $table) {
                $table->uuid('tenant_id');
                $table->uuid('plan_id');
                $table->uuid('status');
                $table->timestamp('subscription_period_start');
                $table->timestamp('subscription_period_end');
                $table->decimal('amount', 10, 2);
                $table->string('currency', 3)->default('USD');
                $table->timestampTz('trial_ends_at')->nullable();
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->nullable();
                $table->timestampTz('canceled_at')->nullable();
                $table->uuid('created_by');
                $table->uuid('updated_by')->nullable();
                $table->uuid('canceled_by')->nullable();
                $table->primary(['tenant_id', 'plan_id']);
            });
            DB::statement("COMMENT ON TABLE subscriptions IS 'Tenants subscriptions'");
        }

        // Roles
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
                $table->string('role_name', 50);
                $table->text('description')->nullable();
                $table->text('key_responsibilities')->nullable();
            });
            DB::statement("COMMENT ON COLUMN roles.id IS 'UUID v4 - Low volume reference table'");
            DB::statement("COMMENT ON COLUMN roles.description IS 'Examples:\n  super_admin\n  procurement_admin         // CVB - Central Vendor Bureau\n  screening_officer         // PQA/CVB - Prequalification Administrator\n  technical_evaluator       // SME - Subject Matter Expert\n  finance_evaluator         // Internal finance officer\n  hsse_evaluator            // Health, Safety, Security & Environment\n  legal_compliance_officer  // Legal/Compliance Officer\n  scm_head                  // Supply Chain Management Head\n  plant_manager             // Plant Manager\n  president                 // Final approver\n  finance_evaluator         // Internal finance officer\n  hsse_evaluator            // Health, Safety, Security & Environment\n  legal_compliance_officer  // Legal/Compliance Officer\n  scm_head                  // Supply Chain Management Head\n  plant_manager             // Plant Manager\n  president                 // Final approver\n  finance_evaluator         // Internal finance officer\n  hsse_evaluator            // Health, Safety, Security & Environment\n  legal_compliance_officer  // Legal/Compliance Officer\n  scm_head                  // Supply Chain Management Head\n  plant_manager             // Plant Manager\n  president                 // Final approver\n  vendor_admin              // Vendor organization representative\n  vendor_user               // Vendor user\n  auditor                   // Auditor role '");
        }

        // Permissions
        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
                $table->string('permissions_name', 100);
                $table->jsonb('modules_auth')->default(DB::raw("'{}'::jsonb"));
            });
            DB::statement("COMMENT ON TABLE permissions IS 'Contains the permissions verified by the software'");
            DB::statement("COMMENT ON COLUMN permissions.id IS 'This can be an uuid (generated) or a code associated to a specific module.\nExamples:\n - Create a form\n - View a form\n - Authorize a form\n - Create an user\n - Reset user password\n'");
            DB::statement("COMMENT ON COLUMN permissions.modules_auth IS 'Check if this will be a list or a specific transaction'");
        }

        // Roles-Permissions (pivot)
        if (!Schema::hasTable('roles_permissions')) {
            Schema::create('roles_permissions', function (Blueprint $table) {
                $table->uuid('role_id');
                $table->uuid('permissions_id');
                $table->uuid('status');
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->nullable();
                $table->timestampTz('canceled_at')->nullable();
                $table->uuid('created_by');
                $table->uuid('updated_by')->nullable();
                $table->uuid('canceled_by')->nullable();
                $table->primary(['role_id', 'permissions_id']);
            });
        }

        // Vendors (tenant-organization mapping)
        if (!Schema::hasTable('vendors')) {
            Schema::create('vendors', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
                $table->uuid('tenant_id');
                $table->uuid('organization_id');
                $table->enum('vendor_type', ['supplier', 'contractor', 'both']);
                $table->uuid('status');
                $table->uuid('invited_by');
                $table->timestampTz('invited_at')->useCurrent();
                $table->timestampTz('accepted_at')->nullable();
                $table->uuid('accepted_by')->nullable();
                $table->timestampTz('rejected_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestampTz('expires_at')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
                $table->uuid('updated_by')->nullable();
            });
            DB::statement("COMMENT ON TABLE vendors IS 'Tenant - vendor relationship'");
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS idx_tenant_organization_unique ON vendors (tenant_id, organization_id)');
        }

        // User-Organization-Role mapping
        if (!Schema::hasTable('user_organization_role_mapping')) {
            Schema::create('user_organization_role_mapping', function (Blueprint $table) {
                $table->uuid('organization_id');
                $table->uuid('user_id');
                $table->uuid('role_id');
                $table->uuid('status');
                $table->timestampTz('start_date')->useCurrent();
                $table->timestampTz('end_date')->nullable();
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('granted_at')->nullable();
                $table->timestampTz('updated_at')->nullable();
                $table->uuid('created_by');
                $table->uuid('granted_by')->nullable();
                $table->uuid('updated_by')->nullable();
                $table->primary(['organization_id', 'user_id', 'role_id']);
            });
            DB::statement("COMMENT ON TABLE user_organization_role_mapping IS 'User role per organization. Need to verify if this cenario will happen'");
        }

        // Foreign keys
        Schema::table('supply_categories', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('tenants');
            $table->foreign('parent_id')->references('id')->on('supply_categories');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('user_country_id')->references('id')->on('countries');
            $table->foreign('mobile_country_id')->references('id')->on('countries');
            $table->foreign('status')->references('id')->on('status');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->foreign('country_id')->references('id')->on('countries');
            $table->foreign('phone_country_id')->references('id')->on('countries');
            $table->foreign('industry_id')->references('id')->on('industry');
            $table->foreign('status')->references('id')->on('status');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
        });

        Schema::table('organization_addresses', function (Blueprint $table) {
            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->foreign('status')->references('id')->on('status');
            $table->foreign('country_id')->references('id')->on('countries');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });

        Schema::table('organization_documents', function (Blueprint $table) {
            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->foreign('status')->references('id')->on('status');
            $table->foreign('uploaded_by')->references('id')->on('users');
            $table->foreign('reviewed_by')->references('id')->on('users');
            $table->foreign('previous_version_id')->references('id')->on('organization_documents');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->foreign('status')->references('id')->on('status');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
        });

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->foreign('status')->references('id')->on('status');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('tenants');
            $table->foreign('plan_id')->references('id')->on('subscription_plans');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('canceled_by')->references('id')->on('users');
        });

        Schema::table('user_organization_role_mapping', function (Blueprint $table) {
            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('role_id')->references('id')->on('roles');
            $table->foreign('status')->references('id')->on('status');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('granted_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });

        Schema::table('roles_permissions', function (Blueprint $table) {
            $table->foreign('role_id')->references('id')->on('roles');
            $table->foreign('permissions_id')->references('id')->on('permissions');
            $table->foreign('status')->references('id')->on('status');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('canceled_by')->references('id')->on('users');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('tenants');
            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->foreign('status')->references('id')->on('status');
            $table->foreign('invited_by')->references('id')->on('users');
            $table->foreign('accepted_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        // Drop FKs first to avoid dependency issues
        foreach ([
            'vendors',
            'roles_permissions',
            'user_organization_role_mapping',
            'subscriptions',
            'subscription_plans',
            'tenants',
            'organization_documents',
            'organization_addresses',
            'organizations',
            'users',
            'supply_categories',
            'permissions',
            'roles',
            'industry',
            'status',
            'countries',
        ] as $table) {
            if (Schema::hasTable($table)) {
                // Attempt to drop table; constraints will be dropped automatically in PG due to CASCADE if specified
            }
        }

        // Drop tables in reverse order
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('roles_permissions');
        Schema::dropIfExists('user_organization_role_mapping');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('subscription_plans');
        Schema::dropIfExists('supply_categories');
        Schema::dropIfExists('organization_documents');
        Schema::dropIfExists('organization_addresses');
        Schema::dropIfExists('organizations');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('industry');
        Schema::dropIfExists('status');
        Schema::dropIfExists('countries');

        // Re-add tenants data column if needed is out of scope for down

        // Drop enum types
        DB::unprepared(<<<'SQL'
DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM pg_type WHERE typname = 'vendor_type_enum') THEN
        DROP TYPE vendor_type_enum;
    END IF;
    IF EXISTS (SELECT 1 FROM pg_type WHERE typname = 'billing_cycle_enum') THEN
        DROP TYPE billing_cycle_enum;
    END IF;
    IF EXISTS (SELECT 1 FROM pg_type WHERE typname = 'address_type_enum') THEN
        DROP TYPE address_type_enum;
    END IF;
END$$;
SQL);
    }
};

