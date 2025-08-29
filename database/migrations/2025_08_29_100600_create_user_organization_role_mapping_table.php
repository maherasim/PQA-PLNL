<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_organization_role_mapping')) {
            return;
        }

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
    }

    public function down(): void
    {
        Schema::dropIfExists('user_organization_role_mapping');
    }
};

