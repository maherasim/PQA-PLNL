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
        Schema::create('questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('section_id')
            ->constrained('questionnaire_sections')
            ->onDelete('cascade'); 
            $table->foreignId('question_type_id')
            ->constrained('question_types')
            ->onDelete('cascade');
            $table->string('code', 20)->nullable();
            $table->text('question_text');
            $table->text('help_text')->nullable();
            $table->string('placeholder_text', 255)->nullable();
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order');
            $table->jsonb('validation_rules')->nullable();
            $table->jsonb('options')->nullable();
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
