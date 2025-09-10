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
       Schema::create('identifier_types', function (Blueprint $table) {
            $table->id(); // SERIAL PRIMARY KEY
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('format_pattern', 200)->nullable();
            $table->string('validation_regex', 500)->nullable();
            $table->uuid('country_id')->nullable();
            $table->boolean('is_global')->default(false);
            $table->boolean('is_active')->default(false);
            $table->integer('priority_score')->default(50);
            $table->timestampsTz(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('identifier_types');
    }
};
