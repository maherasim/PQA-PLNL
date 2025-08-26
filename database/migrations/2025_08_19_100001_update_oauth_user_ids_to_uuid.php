<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    
    public function up(): void
    {
        // No-op: we standardize on bigint user_id to match users.id
    }

    public function down(): void
    {
        // No-op
    }
};

