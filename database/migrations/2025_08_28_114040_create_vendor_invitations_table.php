<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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
            $table->timestampTz('created_at')->default(DB::raw('now()'));
            $table->uuid('created_by');
            $table->timestampTz('updated_at')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestampTz('canceled_at')->nullable();
            $table->uuid('canceled_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_invitations');
    }
};
