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
        Schema::create('vendors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('company_name', 255);
            $table->string('website', 255)->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('phone_country_id')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('logo_url', 255)->nullable();
            $table->string('email', 255);
            $table->text('description')->nullable();
            $table->string('industry', 100)->nullable();
            $table->text('mailing_address')->nullable();
            $table->unsignedBigInteger('status');
            $table->string('vendor_type', 50)->default('supplier');
            $table->timestampTz('created_at')->default(DB::raw('now()'));
            $table->unsignedBigInteger('created_by');
            $table->timestampTz('updated_at')->default(DB::raw('now()'));
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestampTz('deleted_at')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            
            // Add foreign key constraints
            $table->foreign('country_id')->references('id')->on('countries');
            $table->foreign('phone_country_id')->references('id')->on('countries');
            $table->foreign('status')->references('id')->on('status');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        
        // Enforce allowed values for vendor_type
        DB::statement("ALTER TABLE vendors ADD CONSTRAINT chk_vendor_type CHECK (vendor_type IN ('supplier','contractor','both'))");
        
        // Create indexes
        Schema::table('vendors', function (Blueprint $table) {
            $table->unique('email', 'idx_vendors_email_unique');
            $table->index('vendor_type', 'idx_vendors_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};