<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('oauth_auth_codes')) {
            Schema::create('oauth_auth_codes', function (Blueprint $table) {
                $table->string('id', 100)->primary();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('client_id');
                $table->text('scopes')->nullable();
                $table->boolean('revoked');
                $table->dateTime('expires_at')->nullable();
            });
        }

        if (!Schema::hasTable('oauth_access_tokens')) {
            Schema::create('oauth_access_tokens', function (Blueprint $table) {
                $table->string('id', 100)->primary();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('client_id');
                $table->string('name')->nullable();
                $table->text('scopes')->nullable();
                $table->boolean('revoked');
                $table->timestamps();
                $table->dateTime('expires_at')->nullable();
            });
        }

        if (!Schema::hasTable('oauth_refresh_tokens')) {
            Schema::create('oauth_refresh_tokens', function (Blueprint $table) {
                $table->string('id', 100)->primary();
                $table->string('access_token_id', 100)->index();
                $table->boolean('revoked');
                $table->dateTime('expires_at')->nullable();
            });
        }

        if (!Schema::hasTable('oauth_clients')) {
            Schema::create('oauth_clients', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('name');
                $table->string('secret', 100)->nullable();
                $table->string('provider')->nullable();
                $table->text('redirect');
                $table->boolean('personal_access_client');
                $table->boolean('password_client');
                $table->boolean('revoked');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('oauth_personal_access_clients')) {
            Schema::create('oauth_personal_access_clients', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('client_id');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('oauth_personal_access_clients');
        Schema::dropIfExists('oauth_clients');
        Schema::dropIfExists('oauth_refresh_tokens');
        Schema::dropIfExists('oauth_access_tokens');
        Schema::dropIfExists('oauth_auth_codes');
    }
};