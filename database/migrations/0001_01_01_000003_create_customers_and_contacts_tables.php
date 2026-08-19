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
        // 1. Customers table
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('company_name', 191);
            $table->string('trade_name', 191)->nullable();
            $table->string('gst_number', 20)->nullable();
            $table->string('pan_number', 20)->nullable();
            $table->string('industry', 100)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 191)->nullable();

            // General Address
            $table->string('address_street', 255)->nullable();
            $table->string('address_city', 100)->nullable();
            $table->string('address_state', 100)->nullable();
            $table->string('address_country', 100)->nullable();
            $table->string('address_postal_code', 20)->nullable();

            // Billing Address
            $table->string('billing_street', 255)->nullable();
            $table->string('billing_city', 100)->nullable();
            $table->string('billing_state', 100)->nullable();
            $table->string('billing_country', 100)->nullable();
            $table->string('billing_postal_code', 20)->nullable();

            // Shipping Address
            $table->string('shipping_street', 255)->nullable();
            $table->string('shipping_city', 100)->nullable();
            $table->string('shipping_state', 100)->nullable();
            $table->string('shipping_country', 100)->nullable();
            $table->string('shipping_postal_code', 20)->nullable();

            $table->enum('type', ['enterprise', 'mid_market', 'small_business'])->default('small_business');
            $table->enum('status', ['active', 'inactive', 'prospect'])->default('prospect');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('territory_id')->nullable();
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('credit_limit', 15, 2)->default(0.00);
            $table->enum('payment_terms', ['net_30', 'net_60', 'due_on_receipt'])->default('due_on_receipt');
            $table->string('currency', 3)->default('USD');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('territory_id')->references('id')->on('territories')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->index('company_name', 'idx_company');
            $table->index('status', 'idx_status');
            $table->index('assigned_to', 'idx_assigned_customer');
            $table->index('territory_id', 'idx_territory_customer');
            $table->index('type', 'idx_type');
        });

        // 2. Contacts table
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 191)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('designation', 100)->nullable();
            $table->string('department', 100)->nullable();
            $table->boolean('is_decision_maker')->default(false);
            $table->boolean('is_primary')->default(false);
            $table->unsignedBigInteger('reports_to')->nullable();
            $table->string('linkedin_url', 255)->nullable();
            $table->string('twitter_url', 255)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('reports_to')->references('id')->on('contacts')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->index('customer_id', 'idx_customer_contact');
            $table->index('email', 'idx_email');
            $table->index('is_primary', 'idx_primary');
            $table->index('is_active', 'idx_active_contact');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('customers');
    }
};
