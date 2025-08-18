<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add created_at/updated_at to core reference tables if missing
        foreach (['status', 'industry', 'roles', 'permissions'] as $table) {
            Schema::table($table, function (Blueprint $table) use ($table) {
                if (!Schema::hasColumn($table, 'created_at')) {
                    $table->timestampTz('created_at')->useCurrent();
                }
                if (!Schema::hasColumn($table, 'updated_at')) {
                    // Default now for consistency with other tables
                    $table->timestampTz('updated_at')->default(DB::raw('now()'));
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['status', 'industry', 'roles', 'permissions'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                if (Schema::hasColumn($table->getTable(), 'updated_at')) {
                    $table->dropColumn('updated_at');
                }
                if (Schema::hasColumn($table->getTable(), 'created_at')) {
                    $table->dropColumn('created_at');
                }
            });
        }
    }
};

