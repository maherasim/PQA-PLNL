<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->string('name', 250);
            $table->uuid('status');
            $table->uuid('country_id');
            $table->integer('identifier_type_id');
            $table->string('identifier_value', 50);
            $table->string('website', 250)->nullable();
            $table->uuid('phone_country_id')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('logo_url', 250)->nullable();
            $table->string('email', 150)->unique();
            $table->string('address', 200);
            $table->string('mailing_address', 200)->nullable();
            $table->text('description')->nullable();
            $table->timestampTz('created_at')->default(DB::raw('now()'));
            $table->uuid('created_by');
            $table->timestampTz('updated_at')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestampTz('excluded_at')->default(DB::raw('now()'))->nullable();
            $table->uuid('excluded_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
