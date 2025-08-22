<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'default_tenant_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->uuid('default_tenant_id')->nullable()->after('email_verified_at');
                $table->index('default_tenant_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'default_tenant_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex(['default_tenant_id']);
                $table->dropColumn('default_tenant_id');
            });
        }
    }
};