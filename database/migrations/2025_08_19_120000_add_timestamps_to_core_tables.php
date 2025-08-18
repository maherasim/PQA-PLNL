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
        foreach (['status', 'industry', 'roles', 'permissions'] as $tableName) {
            Schema::table($tableName, function (Blueprint $tbl) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'created_at')) {
                    $tbl->timestampTz('created_at')->useCurrent();
                }
                if (!Schema::hasColumn($tableName, 'updated_at')) {
                    $tbl->timestampTz('updated_at')->default(DB::raw('now()'));
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['status', 'industry', 'roles', 'permissions'] as $tableName) {
            Schema::table($tableName, function (Blueprint $tbl) use ($tableName) {
                if (Schema::hasColumn($tableName, 'updated_at')) {
                    $tbl->dropColumn('updated_at');
                }
                if (Schema::hasColumn($tableName, 'created_at')) {
                    $tbl->dropColumn('created_at');
                }
            });
        }
    }
};

