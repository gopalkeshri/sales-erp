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
        // 1. Activities table
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['call', 'email', 'meeting', 'task', 'note']);
            $table->string('subject', 255);
            $table->text('description')->nullable();
            $table->string('entity_type', 50);
            $table->unsignedBigInteger('entity_id');
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration')->nullable();
            $table->string('outcome', 100)->nullable();
            $table->timestamps();

            $table->foreign('performed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');

            $table->index(['entity_type', 'entity_id'], 'idx_entity_activity');
            $table->index('performed_by', 'idx_performed_activity');
            $table->index('assigned_to', 'idx_assigned_activity');
            $table->index('scheduled_at', 'idx_scheduled_activity');
            $table->index('type', 'idx_type_activity');
        });

        // 2. Activity Attachments table
        Schema::create('activity_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->string('filename', 255);
            $table->string('path', 500);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('size')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->index('activity_id', 'idx_activity_attach');
        });

        // 3. Audit Logs table
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('action', ['create', 'update', 'delete']);
            $table->string('entity_type', 50);
            $table->unsignedBigInteger('entity_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            $table->index('user_id', 'idx_user_audit');
            $table->index(['entity_type', 'entity_id'], 'idx_entity_audit');
            $table->index('action', 'idx_action_audit');
            $table->index('created_at', 'idx_created_audit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('activity_attachments');
        Schema::dropIfExists('activities');
    }
};
