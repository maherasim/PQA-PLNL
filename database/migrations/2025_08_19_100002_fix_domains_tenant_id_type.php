<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try {
            DB::statement("ALTER TABLE domains ALTER COLUMN tenant_id TYPE uuid USING tenant_id::uuid");
        } catch (Throwable $e) {}
    }

    public function down(): void
    {
        // No-op
    }
};

