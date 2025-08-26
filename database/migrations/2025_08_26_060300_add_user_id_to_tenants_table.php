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
			if (!Schema::hasColumn('tenants', 'user_id')) {
				$table->uuid('user_id')->nullable()->after('status');
			}
		});
	}

	public function down(): void
	{
		if (!Schema::hasTable('tenants')) {
			return;
		}

		Schema::table('tenants', function (Blueprint $table) {
			if (Schema::hasColumn('tenants', 'user_id')) {
				$table->dropColumn('user_id');
			}
		});
	}
};