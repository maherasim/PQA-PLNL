<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tenants')) {
            Schema::table('tenants', function (Blueprint $table) {
                if (!Schema::hasColumn('tenants', 'name')) {
                    $table->string('name')->nullable();
                }
                if (!Schema::hasColumn('tenants', 'database')) {
                    $table->string('database')->unique();
                }
                if (!Schema::hasColumn('tenants', 'is_active')) {
                    $table->boolean('is_active')->default(true);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tenants')) {
            Schema::table('tenants', function (Blueprint $table) {
                if (Schema::hasColumn('tenants', 'is_active')) {
                    $table->dropColumn('is_active');
                }
                if (Schema::hasColumn('tenants', 'database')) {
                    $table->dropColumn('database');
                }
                if (Schema::hasColumn('tenants', 'name')) {
                    $table->dropColumn('name');
                }
            });
        }
    }
};