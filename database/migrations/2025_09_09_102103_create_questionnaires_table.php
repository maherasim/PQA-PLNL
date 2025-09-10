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
        Schema::create('questionnaires', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')
            ->constrained('organizations')
            ->onDelete('cascade');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('questionnaire_type', 50)->nullable();
            $table->string('version', 20);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questionnaires');
    }
};
