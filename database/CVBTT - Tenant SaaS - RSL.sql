CREATE TYPE "vendor_type_enum" AS ENUM (
  'supplier',
  'contractor',
  'both'
);

CREATE TYPE "billing_cycle_enum" AS ENUM (
  'monthly',
  'quarterly',
  'yearly'
);

CREATE TYPE "address_type_enum" AS ENUM (
  'headquarters',
  'billing',
  'operational',
  'warehouse',
  'branch'
);

CREATE TABLE "countries" (
  "id" uuid PRIMARY KEY DEFAULT (uuid_generate_v4()),
  "name" varchar(100) NOT NULL,
  "code" varchar(3) UNIQUE NOT NULL,
  "phone_code" smallint,
  "currency_code" varchar(3),
  "is_active" boolean DEFAULT true,
  "created_at" timestamptz NOT NULL DEFAULT (now()),
  "updated_at" timestamptz DEFAULT (now())
);

CREATE TABLE "status" (
  "id" uuid PRIMARY KEY DEFAULT (uuid_generate_v4()),
  "status_name" varchar(50) NOT NULL
);

CREATE TABLE "industry" (
  "id" uuid PRIMARY KEY DEFAULT (uuid_generate_v4()),
  "industry_name" varchar(50) NOT NULL
);

CREATE TABLE "supply_categories" (
  "id" uuid PRIMARY KEY DEFAULT (uuid_generate_v4()),
  "tenant_id" uuid NOT NULL,
  "name" varchar(255) NOT NULL,
  "code" varchar(50) NOT NULL,
  "description" text,
  "parent_id" uuid,
  "level" integer NOT NULL DEFAULT 1,
  "is_active" boolean NOT NULL DEFAULT true,
  "requires_special_qualification" boolean NOT NULL DEFAULT false,
  "created_at" timestamp NOT NULL DEFAULT (now()),
  "updated_at" timestamp DEFAULT (now()),
  "deleted_at" timestamp
);

CREATE TABLE "users" (
  "id" uuid PRIMARY KEY DEFAULT (uuid_generate_v4()),
  "email" varchar(255) UNIQUE NOT NULL,
  "email_verified_at" timestamptz,
  "full_name" varchar(100) NOT NULL,
  "user_country_id" uuid,
  "mobile_country_id" uuid,
  "mobile_number" varchar(50),
  "status" uuid NOT NULL,
  "last_login_at" timestamptz,
  "cvb_id" varchar(50) UNIQUE NOT NULL,
  "cvb_number" varchar(50),
  "password_hash" varchar(255) NOT NULL,
  "password_created_at" timestamptz DEFAULT (now()),
  "password_last_changed" timestamptz DEFAULT (now()),
  "password_expires_at" timestamptz,
  "password_change_required" boolean DEFAULT false,
  "failed_login_attempts" integer DEFAULT 0,
  "last_failed_login" timestamptz,
  "account_locked_until" timestamptz,
  "last_successful_login" timestamptz,
  "created_at" timestamptz NOT NULL DEFAULT (now()),
  "updated_at" timestamptz DEFAULT (now()),
  "deleted_at" timestamptz,
  "created_by" uuid,
  "updated_by" uuid,
  "deleted_by" uuid
);

CREATE TABLE "organizations" (
  "id" uuid PRIMARY KEY DEFAULT (uuid_generate_v4()),
  "company_name" varchar(255) NOT NULL,
  "website" varchar(255),
  "country_id" uuid,
  "phone_country_id" uuid,
  "phone" varchar(50),
  "logo_url" varchar(255),
  "email" varchar(255) UNIQUE NOT NULL,
  "description" text,
  "industry_id" uuid NOT NULL,
  "mailing_address" text,
  "status" uuid NOT NULL,
  "cvb_registration_number" varchar(100) UNIQUE NOT NULL,
  "created_at" timestamptz NOT NULL DEFAULT (now()),
  "updated_at" timestamptz,
  "deleted_at" timestamptz,
  "created_by" uuid NOT NULL,
  "updated_by" uuid,
  "deleted_by" uuid
);

CREATE TABLE "organization_addresses" (
  "id" uuid PRIMARY KEY DEFAULT (uuid_generate_v4()),
  "organization_id" uuid NOT NULL,
  "type" varchar NOT NULL,
  "status" uuid NOT NULL,
  "address_line_1" varchar(255) NOT NULL,
  "address_line_2" varchar(255),
  "city" varchar(100) NOT NULL,
  "state" varchar(100) NOT NULL,
  "postal_code" varchar(20) NOT NULL,
  "country_id" uuid NOT NULL,
  "is_primary" boolean NOT NULL DEFAULT false,
  "is_mail_address" boolean NOT NULL DEFAULT false,
  "created_at" timestampz NOT NULL DEFAULT (now()),
  "updated_at" timestampz,
  "created_by" uuid NOT NULL,
  "updated_by" uuid
);

CREATE TABLE "organization_documents" (
  "id" uuid PRIMARY KEY DEFAULT (uuid_generate_v4()),
  "organization_id" uuid NOT NULL,
  "status" uuid NOT NULL,
  "document_type" varchar NOT NULL,
  "name" varchar(255) NOT NULL,
  "original_filename" varchar(255) NOT NULL,
  "file_path" varchar(500) NOT NULL,
  "file_size" bigint NOT NULL,
  "mime_type" varchar(100) NOT NULL,
  "checksum" varchar(64) NOT NULL,
  "uploaded_by" uuid NOT NULL,
  "reviewed_by" uuid,
  "expires_at" timestamp,
  "is_required" boolean NOT NULL DEFAULT true,
  "is_shareable" boolean NOT NULL DEFAULT false,
  "version" integer NOT NULL DEFAULT 1,
  "previous_version_id" uuid,
  "rejection_reason" text,
  "reviewed_at" timestamp,
  "created_at" timestamp NOT NULL DEFAULT (now()),
  "updated_at" timestamp DEFAULT (now())
);

CREATE TABLE "tenants" (
  "id" uuid PRIMARY KEY DEFAULT (uuid_generate_v4()),
  "organization_id" uuid,
  "status" uuid NOT NULL,
  "domain" varchar(255),
  "db_address" varchar(100),
  "db_name" varchar(50),
  "created_at" timestamptz NOT NULL DEFAULT (now()),
  "updated_at" timestamptz DEFAULT (now()),
  "deleted_at" timestamptz,
  "created_by" uuid NOT NULL,
  "updated_by" uuid,
  "deleted_by" uuid
);

CREATE TABLE "subscription_plans" (
  "id" uuid PRIMARY KEY DEFAULT (uuid_generate_v4()),
  "name" varchar(255) NOT NULL,
  "description" text,
  "price" decimal(10,2) NOT NULL,
  "billing_cycle" billing_cycle_enum NOT NULL DEFAULT 'monthly',
  "trial_days" integer DEFAULT 0,
  "status" uuid NOT NULL,
  "created_at" timestamptz NOT NULL DEFAULT (now()),
  "updated_at" timestamptz,
  "deleted_at" timestamptz,
  "created_by" uuid NOT NULL,
  "updated_by" uuid,
  "deleted_by" uuid
);

CREATE TABLE "subscriptions" (
  "tenant_id" uuid,
  "plan_id" uuid,
  "status" uuid NOT NULL,
  "subscription_period_start" timestamp NOT NULL,
  "subscription_period_end" timestamp NOT NULL,
  "amount" decimal(10,2) NOT NULL,
  "currency" varchar(3) NOT NULL DEFAULT 'USD',
  "trial_ends_at" timestampz,
  "created_at" timestamptz NOT NULL DEFAULT (now()),
  "updated_at" timestamptz,
  "canceled_at" timestamptz,
  "created_by" uuid NOT NULL,
  "updated_by" uuid,
  "canceled_by" uuid,
  PRIMARY KEY ("tenant_id", "plan_id")
);

CREATE TABLE "user_organization_role_mapping" (
  "organization_id" uuid,
  "user_id" uuid,
  "role_id" uuid,
  "status" uuid NOT NULL,
  "start_date" timestampz NOT NULL DEFAULT (now()),
  "end_date" timestampz,
  "created_at" timestampz NOT NULL DEFAULT (now()),
  "granted_at" timestampz,
  "updated_at" timestampz,
  "created_by" uuid NOT NULL,
  "granted_by" uuid,
  "updated_by" uuid,
  PRIMARY KEY ("organization_id", "user_id", "role_id")
);

CREATE TABLE "roles" (
  "id" uuid PRIMARY KEY DEFAULT (uuid_generate_v4()),
  "role_name" varchar(50) NOT NULL,
  "description" text,
  "key_responsibilities" text
);

CREATE TABLE "permissions" (
  "id" uuid PRIMARY KEY DEFAULT (uuid_generate_v4()),
  "permissions_name" varchar(100) NOT NULL,
  "modules_auth" jsonb DEFAULT '{}'
);

CREATE TABLE "roles_permissions" (
  "role_id" uuid,
  "permissions_id" uuid,
  "status" uuid NOT NULL,
  "created_at" timestamptz NOT NULL DEFAULT (now()),
  "updated_at" timestamptz,
  "canceled_at" timestamptz,
  "created_by" uuid NOT NULL,
  "updated_by" uuid,
  "canceled_by" uuid,
  PRIMARY KEY ("role_id", "permissions_id")
);

CREATE TABLE "vendors" (
  "id" uuid PRIMARY KEY DEFAULT (uuid_generate_v4()),
  "tenant_id" uuid NOT NULL,
  "organization_id" uuid NOT NULL,
  "vendor_type" vendor_type_enum NOT NULL,
  "status" uuid NOT NULL,
  "invited_by" uuid NOT NULL,
  "invited_at" timestampz NOT NULL DEFAULT (now()),
  "accepted_at" timestampz,
  "accepted_by" uuid,
  "rejected_at" timestampz,
  "rejection_reason" text,
  "expires_at" timestampz,
  "created_at" timestamp NOT NULL DEFAULT (now()),
  "updated_at" timestamp DEFAULT (now()),
  "updated_by" uuid
);

CREATE UNIQUE INDEX "idx_countries_code_unique" ON "countries" ("code");

CREATE UNIQUE INDEX ON "supply_categories" ("tenant_id", "code");

CREATE INDEX ON "supply_categories" ("tenant_id", "parent_id");

CREATE UNIQUE INDEX "idx_users_email_unique" ON "users" ("email");

CREATE UNIQUE INDEX "idx_users_cvb_unique" ON "users" ("cvb_id");

CREATE UNIQUE INDEX "idx_vendors_email_unique" ON "organizations" ("email");

CREATE UNIQUE INDEX "idx_tenant_organization_unique" ON "vendors" ("tenant_id", "organization_id");

COMMENT ON COLUMN "countries"."id" IS 'UUID v4 - Low volume reference table';

COMMENT ON COLUMN "countries"."phone_code" IS 'CHECK (phone_code >= 1 AND phone_code <= 999)';

COMMENT ON COLUMN "status"."id" IS 'UUID v4 - Low volume reference table';

COMMENT ON COLUMN "status"."status_name" IS 'Active, Blocked, Pending Registration';

COMMENT ON COLUMN "industry"."id" IS 'UUID v4 - Low volume reference table';

COMMENT ON COLUMN "industry"."industry_name" IS 'Active, Blocked, Pending Registration';

COMMENT ON TABLE "supply_categories" IS 'Review this table';

COMMENT ON COLUMN "users"."id" IS 'UUID v4 - Sensitive user data requires maximum security';

COMMENT ON COLUMN "users"."cvb_id" IS 'Central Vendor Bureau ID - Required for compliance';

COMMENT ON COLUMN "users"."password_hash" IS 'bcrypt/Argon2id hashed - NEVER plain text';

COMMENT ON TABLE "organizations" IS 'External organization that relates to the CVBTT or other organizations (vendors)';

COMMENT ON COLUMN "organizations"."id" IS 'UUID v4 - Sensitive vendor data requires maximum security';

COMMENT ON COLUMN "organizations"."cvb_registration_number" IS 'Central Vendor Bureau registration - Required';

COMMENT ON TABLE "organization_addresses" IS 'Organization addresses';

COMMENT ON TABLE "organization_documents" IS 'Documentos das organizations - RLS habilitado';

COMMENT ON TABLE "tenants" IS 'Tenant management table';

COMMENT ON COLUMN "tenants"."domain" IS 'Related to subdomain of the tenant';

COMMENT ON COLUMN "tenants"."db_address" IS 'Used in case of different server address for the tenant';

COMMENT ON COLUMN "tenants"."db_name" IS 'Used in case a tenant has its own database (database per tenant)';

COMMENT ON TABLE "subscription_plans" IS 'Avaliable subscription plans';

COMMENT ON TABLE "subscriptions" IS 'Tenants subscriptions';

COMMENT ON TABLE "user_organization_role_mapping" IS 'User role per organization. Need to verify if this cenario will happen';

COMMENT ON COLUMN "roles"."id" IS 'UUID v4 - Low volume reference table';

COMMENT ON COLUMN "roles"."description" IS 'Examples:
  super_admin
  procurement_admin         // CVB - Central Vendor Bureau
  screening_officer         // PQA/CVB - Prequalification Administrator
  technical_evaluator       // SME - Subject Matter Expert
  finance_evaluator         // Internal finance officer
  hsse_evaluator            // Health, Safety, Security & Environment
  legal_compliance_officer  // Legal/Compliance Officer
  scm_head                  // Supply Chain Management Head
  plant_manager             // Plant Manager
  president                 // Final approver
  finance_evaluator         // Internal finance officer
  hsse_evaluator            // Health, Safety, Security & Environment
  legal_compliance_officer  // Legal/Compliance Officer
  scm_head                  // Supply Chain Management Head
  plant_manager             // Plant Manager
  president                 // Final approver
  finance_evaluator         // Internal finance officer
  hsse_evaluator            // Health, Safety, Security & Environment
  legal_compliance_officer  // Legal/Compliance Officer
  scm_head                  // Supply Chain Management Head
  plant_manager             // Plant Manager
  president                 // Final approver
  vendor_admin              // Vendor organization representative
  vendor_user               // Vendor user
  auditor                   // Auditor role ';

COMMENT ON TABLE "permissions" IS 'Contains the permissions verified by the software';

COMMENT ON COLUMN "permissions"."id" IS 'This can be an uuid (generated) or a code associated to a specific module.
Examples:
 - Create a form
 - View a form
 - Authorize a form
 - Create an user
 - Reset user password
';

COMMENT ON COLUMN "permissions"."modules_auth" IS 'Check if this will be a list or a specific transaction';

COMMENT ON TABLE "vendors" IS 'Tenant - vendor relationship';

ALTER TABLE "supply_categories" ADD FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id");

ALTER TABLE "supply_categories" ADD FOREIGN KEY ("parent_id") REFERENCES "supply_categories" ("id");

ALTER TABLE "users" ADD FOREIGN KEY ("user_country_id") REFERENCES "countries" ("id");

ALTER TABLE "users" ADD FOREIGN KEY ("mobile_country_id") REFERENCES "countries" ("id");

ALTER TABLE "users" ADD FOREIGN KEY ("status") REFERENCES "status" ("id");

ALTER TABLE "users" ADD FOREIGN KEY ("created_by") REFERENCES "users" ("id");

ALTER TABLE "users" ADD FOREIGN KEY ("updated_by") REFERENCES "users" ("id");

ALTER TABLE "users" ADD FOREIGN KEY ("deleted_by") REFERENCES "users" ("id");

ALTER TABLE "organizations" ADD FOREIGN KEY ("country_id") REFERENCES "countries" ("id");

ALTER TABLE "organizations" ADD FOREIGN KEY ("phone_country_id") REFERENCES "countries" ("id");

ALTER TABLE "organizations" ADD FOREIGN KEY ("industry_id") REFERENCES "industry" ("id");

ALTER TABLE "organizations" ADD FOREIGN KEY ("status") REFERENCES "status" ("id");

ALTER TABLE "organizations" ADD FOREIGN KEY ("created_by") REFERENCES "users" ("id");

ALTER TABLE "organizations" ADD FOREIGN KEY ("updated_by") REFERENCES "users" ("id");

ALTER TABLE "organizations" ADD FOREIGN KEY ("deleted_by") REFERENCES "users" ("id");

ALTER TABLE "organization_addresses" ADD FOREIGN KEY ("organization_id") REFERENCES "organizations" ("id");

ALTER TABLE "organization_addresses" ADD FOREIGN KEY ("status") REFERENCES "status" ("id");

ALTER TABLE "organization_addresses" ADD FOREIGN KEY ("country_id") REFERENCES "countries" ("id");

ALTER TABLE "organization_addresses" ADD FOREIGN KEY ("created_by") REFERENCES "users" ("id");

ALTER TABLE "organization_addresses" ADD FOREIGN KEY ("updated_by") REFERENCES "users" ("id");

ALTER TABLE "organization_documents" ADD FOREIGN KEY ("organization_id") REFERENCES "organizations" ("id");

ALTER TABLE "organization_documents" ADD FOREIGN KEY ("status") REFERENCES "status" ("id");

ALTER TABLE "organization_documents" ADD FOREIGN KEY ("uploaded_by") REFERENCES "users" ("id");

ALTER TABLE "organization_documents" ADD FOREIGN KEY ("reviewed_by") REFERENCES "users" ("id");

ALTER TABLE "organization_documents" ADD FOREIGN KEY ("previous_version_id") REFERENCES "organization_documents" ("id");

ALTER TABLE "tenants" ADD FOREIGN KEY ("organization_id") REFERENCES "organizations" ("id");

ALTER TABLE "tenants" ADD FOREIGN KEY ("status") REFERENCES "status" ("id");

ALTER TABLE "tenants" ADD FOREIGN KEY ("created_by") REFERENCES "users" ("id");

ALTER TABLE "tenants" ADD FOREIGN KEY ("updated_by") REFERENCES "users" ("id");

ALTER TABLE "tenants" ADD FOREIGN KEY ("deleted_by") REFERENCES "users" ("id");

ALTER TABLE "subscription_plans" ADD FOREIGN KEY ("status") REFERENCES "status" ("id");

ALTER TABLE "subscription_plans" ADD FOREIGN KEY ("created_by") REFERENCES "users" ("id");

ALTER TABLE "subscription_plans" ADD FOREIGN KEY ("updated_by") REFERENCES "users" ("id");

ALTER TABLE "subscription_plans" ADD FOREIGN KEY ("deleted_by") REFERENCES "users" ("id");

ALTER TABLE "subscriptions" ADD FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id");

ALTER TABLE "subscriptions" ADD FOREIGN KEY ("plan_id") REFERENCES "subscription_plans" ("id");

ALTER TABLE "subscriptions" ADD FOREIGN KEY ("created_by") REFERENCES "users" ("id");

ALTER TABLE "subscriptions" ADD FOREIGN KEY ("updated_by") REFERENCES "users" ("id");

ALTER TABLE "subscriptions" ADD FOREIGN KEY ("canceled_by") REFERENCES "users" ("id");

ALTER TABLE "user_organization_role_mapping" ADD FOREIGN KEY ("organization_id") REFERENCES "organizations" ("id");

ALTER TABLE "user_organization_role_mapping" ADD FOREIGN KEY ("user_id") REFERENCES "users" ("id");

ALTER TABLE "user_organization_role_mapping" ADD FOREIGN KEY ("role_id") REFERENCES "roles" ("id");

ALTER TABLE "user_organization_role_mapping" ADD FOREIGN KEY ("status") REFERENCES "status" ("id");

ALTER TABLE "user_organization_role_mapping" ADD FOREIGN KEY ("created_by") REFERENCES "users" ("id");

ALTER TABLE "user_organization_role_mapping" ADD FOREIGN KEY ("granted_by") REFERENCES "users" ("id");

ALTER TABLE "user_organization_role_mapping" ADD FOREIGN KEY ("updated_by") REFERENCES "users" ("id");

ALTER TABLE "roles_permissions" ADD FOREIGN KEY ("role_id") REFERENCES "roles" ("id");

ALTER TABLE "roles_permissions" ADD FOREIGN KEY ("permissions_id") REFERENCES "permissions" ("id");

ALTER TABLE "roles_permissions" ADD FOREIGN KEY ("status") REFERENCES "status" ("id");

ALTER TABLE "roles_permissions" ADD FOREIGN KEY ("created_by") REFERENCES "users" ("id");

ALTER TABLE "roles_permissions" ADD FOREIGN KEY ("updated_by") REFERENCES "users" ("id");

ALTER TABLE "roles_permissions" ADD FOREIGN KEY ("canceled_by") REFERENCES "users" ("id");

ALTER TABLE "vendors" ADD FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id");

ALTER TABLE "vendors" ADD FOREIGN KEY ("organization_id") REFERENCES "organizations" ("id");

ALTER TABLE "vendors" ADD FOREIGN KEY ("status") REFERENCES "status" ("id");

ALTER TABLE "vendors" ADD FOREIGN KEY ("invited_by") REFERENCES "users" ("id");

ALTER TABLE "vendors" ADD FOREIGN KEY ("accepted_by") REFERENCES "users" ("id");

ALTER TABLE "vendors" ADD FOREIGN KEY ("updated_by") REFERENCES "users" ("id");
