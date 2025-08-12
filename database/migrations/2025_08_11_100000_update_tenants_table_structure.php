<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure the tenants table exists
        if (!Schema::hasTable('tenants')) {
            Schema::create('tenants', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('database')->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
            return;
        }

        // If table exists, ensure required columns exist
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'id')) {
                $table->id()->first();
            }
            if (!Schema::hasColumn('tenants', 'name')) {
                $table->string('name')->after('id');
            }
            if (!Schema::hasColumn('tenants', 'database')) {
                $table->string('database')->unique()->after('name');
            }
            if (!Schema::hasColumn('tenants', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('database');
            }
            if (!Schema::hasColumn('tenants', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op for safety
    }
};