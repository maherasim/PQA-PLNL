<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if the tenants table exists with the old structure
        if (Schema::hasTable('tenants')) {
            Schema::table('tenants', function (Blueprint $table) {
                // If the old string id column exists, we need to handle it
                if (Schema::hasColumn('tenants', 'id') && !Schema::hasColumn('tenants', 'name')) {
                    // Drop the foreign key from domains table first
                    Schema::table('domains', function (Blueprint $domainTable) {
                        if (Schema::hasColumn('domains', 'tenant_id')) {
                            $domainTable->dropForeign(['tenant_id']);
                        }
                    });
                    
                    // Drop the old tenants table and recreate with new structure
                    Schema::dropIfExists('tenants');
                    
                    // Recreate with new structure
                    Schema::create('tenants', function (Blueprint $table) {
                        $table->id();
                        $table->string('name');
                        $table->string('domain')->unique();
                        $table->string('database')->unique();
                        $table->boolean('is_active')->default(true);
                        $table->timestamps();
                    });
                    
                    // Recreate the domains table foreign key
                    Schema::table('domains', function (Blueprint $domainTable) {
                        $domainTable->foreign('tenant_id')->references('id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');
                    });
                } else {
                    // If the table already has the new structure, just ensure all columns exist
                    if (!Schema::hasColumn('tenants', 'name')) {
                        $table->string('name');
                    }
                    if (!Schema::hasColumn('tenants', 'domain')) {
                        $table->string('domain')->unique();
                    }
                    if (!Schema::hasColumn('tenants', 'database')) {
                        $table->string('database')->unique();
                    }
                    if (!Schema::hasColumn('tenants', 'is_active')) {
                        $table->boolean('is_active')->default(true);
                    }
                }
            });
        } else {
            // If no tenants table exists, create it with the new structure
            Schema::create('tenants', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('domain')->unique();
                $table->string('database')->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We won't implement a reverse migration for this complex change
        // In a real scenario, you would want to implement this properly
    }
};