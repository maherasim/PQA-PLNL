<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('domains')) {
            return;
        }

        Schema::table('domains', function (Blueprint $table) {
            // Drop existing foreign key if present
            try {
                $table->dropForeign(['tenant_id']);
            } catch (\Throwable $e) {
                // ignore
            }
        });

        // Change column to string to match tenants.id (stancl default)
        Schema::table('domains', function (Blueprint $table) {
            try {
                $table->string('tenant_id')->change();
            } catch (\Throwable $e) {
                // Some drivers may not support change(); if so, manual intervention may be needed
            }
        });

        Schema::table('domains', function (Blueprint $table) {
            try {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');
            } catch (\Throwable $e) {
                // ignore if already exists
            }
        });
    }

    public function down(): void
    {
        // No-op to avoid type flapping in down migrations
    }
};