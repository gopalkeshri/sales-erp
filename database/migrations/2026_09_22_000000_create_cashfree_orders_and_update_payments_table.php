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
        // 1. Create cashfree_orders table
        Schema::create('cashfree_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->string('order_id', 100)->unique();
            $table->string('cf_order_id', 100)->nullable();
            $table->text('payment_session_id')->nullable();
            $table->decimal('order_amount', 15, 2);
            $table->string('order_currency', 10)->default('INR');
            $table->string('status', 50)->default('ACTIVE'); // ACTIVE, PAID, EXPIRED, TERMINATED
            $table->string('customer_id', 100)->nullable();
            $table->string('customer_name', 255)->nullable();
            $table->string('customer_email', 255)->nullable();
            $table->string('customer_phone', 50)->nullable();
            $table->string('cf_payment_id', 100)->nullable();
            $table->string('payment_method', 100)->nullable();
            $table->string('bank_reference', 150)->nullable();
            $table->timestamp('payment_time')->nullable();
            $table->json('raw_response')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->index('invoice_id', 'idx_cf_orders_invoice');
            $table->index('order_id', 'idx_cf_orders_order_id');
            $table->index('status', 'idx_cf_orders_status');
        });

        // 2. Update payments table to support gateway tracking and extended methods
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'cashfree_order_id')) {
                $table->string('cashfree_order_id', 100)->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('payments', 'transaction_id')) {
                $table->string('transaction_id', 150)->nullable()->after('reference_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'transaction_id')) {
                $table->dropColumn('transaction_id');
            }
            if (Schema::hasColumn('payments', 'cashfree_order_id')) {
                $table->dropColumn('cashfree_order_id');
            }
        });

        Schema::dropIfExists('cashfree_orders');
    }
};
