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
        Schema::create('questionnaire_sections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('questionnaire_id');
            $table->string('code', 10)->nullable();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->integer('sort_order');
            $table->boolean('is_required')->default(false);
            $table->timestampTz('created_at')->nullable();
            $table->foreign('questionnaire_id')
            ->references('id')
            ->on('questionnaires')
            ->onDelete('cascade'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questionnaire_sections');
    }
};
