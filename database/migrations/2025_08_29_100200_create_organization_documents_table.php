<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('organization_documents')) {
            return;
        }

        Schema::create('organization_documents', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('organization_id');
            $table->uuid('status');
            $table->string('document_type');
            $table->string('name', 255);
            $table->string('original_filename', 255);
            $table->string('file_path', 500);
            $table->bigInteger('file_size');
            $table->string('mime_type', 100);
            $table->string('checksum', 64);
            $table->uuid('uploaded_by');
            $table->uuid('reviewed_by')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_required')->default(true);
            $table->boolean('is_shareable')->default(false);
            $table->integer('version')->default(1);
            $table->uuid('previous_version_id')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_documents');
    }
};

