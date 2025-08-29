<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('organizations')) {
            return;
        }

        Schema::create('organizations', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->string('company_name', 255);
            $table->string('website', 255)->nullable();
            $table->uuid('country_id')->nullable();
            $table->uuid('phone_country_id')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('logo_url', 255)->nullable();
            $table->string('email', 255);
            $table->text('description')->nullable();
            $table->uuid('industry_id');
            $table->text('mailing_address')->nullable();
            $table->uuid('status');
            $table->string('cvb_registration_number', 100);
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable();
            $table->timestampTz('deleted_at')->nullable();
            $table->uuid('created_by');
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
        });

        DB::statement('CREATE UNIQUE INDEX idx_vendors_email_unique ON organizations (email)');
        DB::statement('CREATE UNIQUE INDEX organizations_cvb_registration_number_unique ON organizations (cvb_registration_number)');
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};

