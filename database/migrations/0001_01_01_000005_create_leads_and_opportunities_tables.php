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
        // 1. Opportunities table
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->enum('stage', ['prospecting', 'qualification', 'proposal', 'negotiation', 'closed_won', 'closed_lost'])->default('prospecting');
            $table->integer('probability')->default(0);
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->decimal('expected_revenue', 15, 2)->default(0.00);
            $table->string('currency', 3)->default('USD');
            $table->date('close_date')->nullable();
            $table->date('actual_close_date')->nullable();
            $table->text('lost_reason')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('territory_id')->nullable();
            $table->json('competitors')->nullable();
            $table->text('decision_criteria')->nullable();
            $table->string('next_step', 255)->nullable();
            $table->json('custom_fields')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('set null');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('set null');
            $table->foreign('territory_id')->references('id')->on('territories')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->index('stage', 'idx_stage');
            $table->index('customer_id', 'idx_customer_opp');
            $table->index('assigned_to', 'idx_assigned_opp');
            $table->index('close_date', 'idx_close_date');
            $table->index('created_at', 'idx_created_opp');
        });

        // 2. Leads table
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->enum('source', ['website', 'referral', 'cold_call', 'trade_show', 'social_media', 'other'])->default('other');
            $table->string('source_detail', 255)->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('company_name', 255)->nullable();
            $table->string('contact_name', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->enum('status', ['new', 'contacted', 'qualified', 'unqualified', 'converted'])->default('new');
            $table->integer('qualification_score')->default(0);
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('territory_id')->nullable();
            $table->decimal('estimated_value', 15, 2)->default(0.00);
            $table->string('currency', 3)->default('USD');
            $table->date('expected_close_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->unsignedBigInteger('converted_to_opportunity_id')->nullable();
            $table->json('custom_fields')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('territory_id')->references('id')->on('territories')->onDelete('set null');
            $table->foreign('converted_to_opportunity_id')->references('id')->on('opportunities')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->index('status', 'idx_status_lead');
            $table->index('assigned_to', 'idx_assigned_lead');
            $table->index('territory_id', 'idx_territory_lead');
            $table->index('source', 'idx_source_lead');
            $table->index('created_at', 'idx_created_lead');
        });

        // Add lead_id foreign key on opportunities
        Schema::table('opportunities', function (Blueprint $table) {
            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('set null');
        });

        // 3. Opportunity Products table
        Schema::create('opportunity_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('opportunity_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount', 15, 2)->default(0.00);
            $table->decimal('total', 15, 2);
            $table->timestamps();

            $table->foreign('opportunity_id')->references('id')->on('opportunities')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            $table->index('opportunity_id', 'idx_opportunity_prod');
            $table->index('product_id', 'idx_product_opp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunity_products');

        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropForeign(['lead_id']);
        });

        Schema::dropIfExists('leads');
        Schema::dropIfExists('opportunities');
    }
};
