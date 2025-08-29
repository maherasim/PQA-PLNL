<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('roles_permissions')) {
            return;
        }

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

    public function down(): void
    {
        Schema::dropIfExists('roles_permissions');
    }
};

