<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('domains')) {
            return;
        }

        Schema::table('domains', function (Blueprint $table) {
            // Drop existing foreign key if present
            try {
                $table->dropForeign(['tenant_id']);
            } catch (\Throwable $e) {
                // ignore if no FK
            }
        });

        // Modify column type to unsignedBigInteger if not already
        Schema::table('domains', function (Blueprint $table) {
            if (Schema::hasColumn('domains', 'tenant_id')) {
                // Some drivers require raw SQL for altering type; for MySQL this works with doctrine/dbal, otherwise expect fresh installs to have correct type
                try {
                    $table->unsignedBigInteger('tenant_id')->change();
                } catch (\Throwable $e) {
                    // If change() not supported, leave as is for now.
                }
            } else {
                $table->unsignedBigInteger('tenant_id');
            }
        });

        Schema::table('domains', function (Blueprint $table) {
            try {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');
            } catch (\Throwable $e) {
                // ignore if already added
            }
        });
    }

    public function down(): void
    {
        // No-op
    }
};