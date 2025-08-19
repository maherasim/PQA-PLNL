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
        // Schema::table('users', function (Blueprint $table) {
        //     $table->foreign('user_country_id')->references('id')->on('countries');
        //     $table->foreign('mobile_country_id')->references('id')->on('countries');
        //     $table->foreign('status')->references('id')->on('status');
        //     $table->foreign('role')->references('id')->on('roles');
        //     $table->foreign('created_by')->references('id')->on('users');
        //     $table->foreign('updated_by')->references('id')->on('users');
        //     $table->foreign('deleted_by')->references('id')->on('users');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('users', function (Blueprint $table) {
        //     $table->dropForeign(['user_country_id']);
        //     $table->dropForeign(['mobile_country_id']);
        //     $table->dropForeign(['status']);
        //     $table->dropForeign(['role']);
        //     $table->dropForeign(['created_by']);
        //     $table->dropForeign(['updated_by']);
        //     $table->dropForeign(['deleted_by']);
        // });
    }
};