<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Enable uuid-ossp extension
        DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp";');
    }

    public function down(): void
    {
        // Optionally disable it (usually not needed in prod)
        DB::statement('DROP EXTENSION IF EXISTS "uuid-ossp";');
    }
};
