CREATE TYPE "vendor_type_enum" AS ENUM (
  'supplier',
  'contractor',
  'both'
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

CREATE TABLE "vendors" (
  "id" uuid PRIMARY KEY DEFAULT (uuid_generate_v4()),
  "company_name" varchar(255) NOT NULL,
  "website" varchar(255),
  "country_id" uuid,
  "phone_country_id" uuid,
  "phone" varchar(50),
  "logo_url" varchar(255),
  "email" varchar(255) UNIQUE NOT NULL,
  "description" text,
  "industry" varchar(100),
  "mailing_address" text,
  "status" uuid NOT NULL,
  "vendor_type" vendor_type_enum NOT NULL DEFAULT 'supplier',
  "created_at" timestamptz NOT NULL DEFAULT (now()),
  "created_by" uuid NOT NULL,
  "updated_at" timestamptz DEFAULT (now()),
  "updated_by" uuid,
  "deleted_at" timestamptz,
  "deleted_by" uuid
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
  "role" uuid NOT NULL,
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

CREATE TABLE "roles" (
  "id" uuid PRIMARY KEY DEFAULT (uuid_generate_v4()),
  "role_name" varchar(50) NOT NULL,
  "description" text,
  "key_responsibilities" text
);

CREATE UNIQUE INDEX "idx_countries_code_unique" ON "countries" ("code");

CREATE UNIQUE INDEX "idx_vendors_email_unique" ON "vendors" ("email");

CREATE INDEX "idx_vendors_type" ON "vendors" ("vendor_type");

CREATE UNIQUE INDEX "idx_users_email_unique" ON "users" ("email");

CREATE UNIQUE INDEX "idx_users_cvb_unique" ON "users" ("cvb_id");

COMMENT ON COLUMN "countries"."id" IS 'UUID v4 - Low volume reference table';

COMMENT ON COLUMN "countries"."phone_code" IS 'CHECK (phone_code >= 1 AND phone_code <= 999)';

COMMENT ON COLUMN "status"."id" IS 'UUID v4 - Low volume reference table';

COMMENT ON COLUMN "status"."status_name" IS 'Active, Blocked, Pending Registration';

COMMENT ON COLUMN "industry"."id" IS 'UUID v4 - Low volume reference table';

COMMENT ON COLUMN "industry"."industry_name" IS 'Active, Blocked, Pending Registration';

COMMENT ON TABLE "vendors" IS 'External organization representative registering for qualification';

COMMENT ON COLUMN "vendors"."id" IS 'UUID v4 - Sensitive vendor data requires maximum security';

COMMENT ON COLUMN "vendors"."industry" IS 'This might be a domain table';

COMMENT ON COLUMN "users"."id" IS 'UUID v4 - Sensitive user data requires maximum security';

COMMENT ON COLUMN "users"."cvb_id" IS 'Central Vendor Bureau ID - Required for compliance';

COMMENT ON COLUMN "users"."password_hash" IS 'bcrypt/Argon2id hashed - NEVER plain text';

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
  vendor_admin              // Vendor organization representative
  vendor_user               // Vendor user
  auditor                   // Auditor role ';

ALTER TABLE "vendors" ADD FOREIGN KEY ("country_id") REFERENCES "countries" ("id");

ALTER TABLE "vendors" ADD FOREIGN KEY ("phone_country_id") REFERENCES "countries" ("id");

ALTER TABLE "vendors" ADD FOREIGN KEY ("status") REFERENCES "status" ("id");

ALTER TABLE "vendors" ADD FOREIGN KEY ("created_by") REFERENCES "users" ("id");

ALTER TABLE "vendors" ADD FOREIGN KEY ("updated_by") REFERENCES "users" ("id");

ALTER TABLE "vendors" ADD FOREIGN KEY ("deleted_by") REFERENCES "users" ("id");

ALTER TABLE "users" ADD FOREIGN KEY ("user_country_id") REFERENCES "countries" ("id");

ALTER TABLE "users" ADD FOREIGN KEY ("mobile_country_id") REFERENCES "countries" ("id");

ALTER TABLE "users" ADD FOREIGN KEY ("status") REFERENCES "status" ("id");

ALTER TABLE "users" ADD FOREIGN KEY ("role") REFERENCES "roles" ("id");

ALTER TABLE "users" ADD FOREIGN KEY ("created_by") REFERENCES "users" ("id");

ALTER TABLE "users" ADD FOREIGN KEY ("updated_by") REFERENCES "users" ("id");

ALTER TABLE "users" ADD FOREIGN KEY ("deleted_by") REFERENCES "users" ("id");
