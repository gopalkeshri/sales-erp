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
        // 1. Roles table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });

        // 2. Permissions table
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('module', 50);
            $table->string('action', 50);
            $table->timestamps();

            $table->index('module', 'idx_module');
            $table->index('action', 'idx_action');
        });

        // 3. Territories table
        Schema::create('territories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_territory_id')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->string('region', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->json('postal_codes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('parent_territory_id')->references('id')->on('territories')->onDelete('set null');
            $table->index('parent_territory_id', 'idx_territory_parent');
            $table->index('region', 'idx_territory_region');
            $table->index('is_active', 'idx_territory_active');
        });

        // 4. Teams table
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->unsignedBigInteger('territory_id')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('territory_id')->references('id')->on('territories')->onDelete('set null');
            $table->index('territory_id', 'idx_team_territory');
            $table->index('is_active', 'idx_team_active');
        });

        // 5. Users table
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->string('email', 191)->unique();
            $table->string('password', 255);
            $table->enum('role', ['admin', 'manager', 'sales_rep'])->default('sales_rep');
            $table->unsignedBigInteger('territory_id')->nullable();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('avatar', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('territory_id')->references('id')->on('territories')->onDelete('set null');
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('set null');
            $table->index('role', 'idx_role');
            $table->index('territory_id', 'idx_territory_user');
            $table->index('team_id', 'idx_team_user');
            $table->index('is_active', 'idx_active_user');
        });

        // Add manager foreign keys now that users table exists
        Schema::table('territories', function (Blueprint $table) {
            $table->foreign('manager_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->foreign('manager_id')->references('id')->on('users')->onDelete('set null');
        });

        // 6. Role User pivot table
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['role_id', 'user_id'], 'unique_role_user');
        });

        // 7. Permission Role pivot table
        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['permission_id', 'role_id'], 'unique_permission_role');
        });

        // Password Reset Tokens
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 191)->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Sessions
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('role_user');

        Schema::table('teams', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
        });

        Schema::table('territories', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
        });

        Schema::dropIfExists('users');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('territories');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
