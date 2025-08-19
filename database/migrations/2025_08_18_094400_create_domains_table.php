<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->string('domain', 255);
            $table->uuid('tenant_id');
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();
            $table->timestampTz('deleted_at')->nullable();
            
            $table->foreign('tenant_id')->references('id')->on('tenants');
        });
        
        // Populate the domains table with existing tenant domains
        DB::statement('INSERT INTO domains (id, domain, tenant_id, created_at, updated_at) 
                      SELECT uuid_generate_v4(), domain, id, created_at, updated_at 
                      FROM tenants 
                      WHERE domain IS NOT NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};