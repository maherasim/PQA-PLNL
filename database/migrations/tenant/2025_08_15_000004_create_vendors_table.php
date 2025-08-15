<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First create the enum type if it doesn't exist
        DB::unprepared("CREATE TYPE vendor_type_enum AS ENUM ('supplier', 'contractor', 'both')");
        
        Schema::create('vendors', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->string('company_name', 255);
            $table->string('website', 255)->nullable();
            $table->uuid('country_id')->nullable();
            $table->uuid('phone_country_id')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('logo_url', 255)->nullable();
            $table->string('email', 255)->unique();
            $table->text('description')->nullable();
            $table->string('industry', 100)->nullable();
            $table->text('mailing_address')->nullable();
            $table->uuid('status');
            $table->string('vendor_type')->default('supplier');
            $table->timestampTz('created_at')->default(DB::raw('now()'));
            $table->uuid('created_by');
            $table->timestampTz('updated_at')->default(DB::raw('now()'));
            $table->uuid('updated_by')->nullable();
            $table->timestampTz('deleted_at')->nullable();
            $table->uuid('deleted_by')->nullable();
            
            // Add foreign key constraints
            $table->foreign('country_id')->references('id')->on('countries');
            $table->foreign('phone_country_id')->references('id')->on('countries');
            $table->foreign('status')->references('id')->on('status');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        
        // Add the enum type constraint
        DB::statement("ALTER TABLE vendors ALTER COLUMN vendor_type TYPE vendor_type_enum USING vendor_type::vendor_type_enum");
        
        // Create indexes
        Schema::table('vendors', function (Blueprint $table) {
            $table->unique('email');
            $table->index('vendor_type', 'idx_vendors_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
        DB::unprepared("DROP TYPE IF EXISTS vendor_type_enum");
    }
};