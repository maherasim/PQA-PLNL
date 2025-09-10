<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
      Schema::create('organization_documents', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('organization_id');
            $table->smallInteger('document_id');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('s3_bucket', 255);
            $table->string('s3_key', 500);
            $table->string('s3_region', 50);
            $table->text('s3_url')->nullable();
            $table->string('original_filename', 255);
            $table->string('file_extension', 10);
            $table->unsignedBigInteger('file_size_bytes');
            $table->string('mime_type', 100);
            $table->string('checksum', 255)->nullable();
            $table->date('expiration_date')->nullable();
            $table->date('issue_date')->nullable();
            $table->string('issuer', 255)->nullable();
            $table->string('document_number', 100)->nullable();
            $table->json('custom_metadata')->nullable();
            $table->uuid('previous_document_id')->nullable();
            $table->uuid('status');
            $table->uuid('uploaded_by')->nullable();
            $table->timestamp('uploaded_at')->default(DB::raw('now()'));
            $table->uuid('updated_by')->nullable();
            $table->timestamp('updated_at')->default(DB::raw('now()'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_documents');
    }
};
