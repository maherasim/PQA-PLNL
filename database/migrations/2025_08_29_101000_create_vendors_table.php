<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vendors')) {
            return;
        }

        // ensure enum exists
        DB::unprepared(<<<'SQL'
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'vendor_type_enum') THEN
        CREATE TYPE vendor_type_enum AS ENUM ('supplier', 'contractor', 'both');
    END IF;
END$$;
SQL);

        Schema::create('vendors', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('tenant_id');
            $table->uuid('organization_id');
            $table->enum('vendor_type', ['supplier','contractor','both']);
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

        DB::statement('CREATE UNIQUE INDEX idx_tenant_organization_unique ON vendors (tenant_id, organization_id)');
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};

