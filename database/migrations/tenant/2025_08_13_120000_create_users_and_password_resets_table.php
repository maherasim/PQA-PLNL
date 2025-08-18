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
        // Drop the existing users table if it exists
        Schema::dropIfExists('users');
        
        // Create the new users table with the required schema
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('email', 255);
            $table->timestampTz('email_verified_at')->nullable();
            $table->string('full_name', 100);
            $table->unsignedBigInteger('user_country_id')->nullable();
            $table->unsignedBigInteger('mobile_country_id')->nullable();
            $table->string('mobile_number', 50)->nullable();
            $table->unsignedBigInteger('status');
            $table->timestampTz('last_login_at')->nullable();
            $table->unsignedBigInteger('role');
            $table->string('cvb_id', 50);
            $table->string('cvb_number', 50)->nullable();
            $table->string('password_hash', 255);
            $table->timestampTz('password_created_at')->default(DB::raw('now()'));
            $table->timestampTz('password_last_changed')->default(DB::raw('now()'));
            $table->timestampTz('password_expires_at')->nullable();
            $table->boolean('password_change_required')->default(false);
            $table->integer('failed_login_attempts')->default(0);
            $table->timestampTz('last_failed_login')->nullable();
            $table->timestampTz('account_locked_until')->nullable();
            $table->timestampTz('last_successful_login')->nullable();
            $table->timestampTz('created_at')->default(DB::raw('now()'));
            $table->timestampTz('updated_at')->default(DB::raw('now()'));
            $table->timestampTz('deleted_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });
        
        // Add foreign key constraints
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('user_country_id')->references('id')->on('countries');
            $table->foreign('mobile_country_id')->references('id')->on('countries');
            $table->foreign('status')->references('id')->on('status');
            $table->foreign('role')->references('id')->on('roles');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        
        // Create indexes
        Schema::table('users', function (Blueprint $table) {
            $table->unique('email', 'idx_users_email_unique');
            $table->unique('cvb_id', 'idx_users_cvb_unique');
        });
        
        // Create password_reset_tokens table if it doesn't exist
        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};