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
        Schema::create('question_responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('submission_id');
            $table->foreignUuid('question_id')
            ->constrained('questions')
            ->onDelete('cascade');
            $table->text('response_text')->nullable();
            $table->decimal('response_number', 15, 2)->nullable();
            $table->date('response_date')->nullable();
            $table->boolean('response_boolean')->nullable();
            $table->jsonb('response_json')->nullable();
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_responses');
    }
};
