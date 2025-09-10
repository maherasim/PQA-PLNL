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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('tenant_id');
            $table->uuid('plan_id');
            $table->uuid('status');
            $table->timestamp('subscription_period_start');
            $table->timestamp('subscription_period_end');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->timestampTz('trial_ends_at')->nullable();
            $table->timestampTz('created_at')->default(DB::raw('now()'));
            $table->uuid('created_by');
            $table->timestampTz('updated_at')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestampTz('canceled_at')->nullable();
            $table->uuid('canceled_by')->nullable();

            $table->primary(['tenant_id', 'plan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
