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
        Schema::create('questionnaire_submissions', function (Blueprint $table) {
           $table->foreignId('vendor_id')   // this creates an unsignedBigInteger
            ->constrained('vendors')
            ->onDelete('cascade');
            $table->foreignUuid('questionnaire_id')
            ->constrained('questionnaires')
            ->onDelete('cascade');
            $table->foreignUuid('organization_id')
            ->constrained('organizations')
            ->onDelete('cascade');
            $table->foreignId('status_id')
            ->constrained('status')
            ->onDelete('cascade');      
            $table->string('status', 20)->nullable();
            $table->timestampTz('submitted_at')->nullable();
            $table->timestampTz('reviewed_at')->nullable();
            $table->text('reviewer_notes')->nullable();
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questionnaire_submissions');
    }
};
