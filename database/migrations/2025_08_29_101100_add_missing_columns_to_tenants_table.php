<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		if (!Schema::hasTable('tenants')) {
			return;
		}

		Schema::table('tenants', function (Blueprint $table) {
			if (!Schema::hasColumn('tenants', 'organization_id')) {
				$table->uuid('organization_id')->nullable();
			}
			if (!Schema::hasColumn('tenants', 'status')) {
				$table->uuid('status');
			}
			if (!Schema::hasColumn('tenants', 'domain')) {
				$table->string('domain', 255)->nullable();
			}
			if (!Schema::hasColumn('tenants', 'db_address')) {
				$table->string('db_address', 100)->nullable();
			}
			if (!Schema::hasColumn('tenants', 'db_name')) {
				$table->string('db_name', 50)->nullable();
			}
			if (!Schema::hasColumn('tenants', 'created_at')) {
				$table->timestampTz('created_at')->useCurrent();
			}
			if (!Schema::hasColumn('tenants', 'updated_at')) {
				$table->timestampTz('updated_at')->nullable();
			}
			if (!Schema::hasColumn('tenants', 'deleted_at')) {
				$table->timestampTz('deleted_at')->nullable();
			}
			if (!Schema::hasColumn('tenants', 'created_by')) {
				$table->uuid('created_by');
			}
			if (!Schema::hasColumn('tenants', 'updated_by')) {
				$table->uuid('updated_by')->nullable();
			}
			if (!Schema::hasColumn('tenants', 'deleted_by')) {
				$table->uuid('deleted_by')->nullable();
			}
		});
	}

	public function down(): void
	{
		// no-op; do not drop columns to preserve data
	}
};