<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('permissions')) {
            return;
        }

        Schema::create('permissions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->string('permissions_name', 100);
            $table->jsonb('modules_auth')->default(DB::raw("'{}'::jsonb"));
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};

