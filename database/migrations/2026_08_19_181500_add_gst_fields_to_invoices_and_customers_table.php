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
        // 1. Add GST fields to Customers table
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'state_code')) {
                $table->string('state_code', 5)->nullable()->after('address_state');
            }
            if (!Schema::hasColumn('customers', 'shipping_state_code')) {
                $table->string('shipping_state_code', 5)->nullable()->after('shipping_state');
            }
        });

        // 2. Add GST fields to Invoices table
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'place_of_supply')) {
                $table->string('place_of_supply', 100)->nullable()->after('type');
            }
            if (!Schema::hasColumn('invoices', 'state_code')) {
                $table->string('state_code', 5)->nullable()->after('place_of_supply');
            }
            if (!Schema::hasColumn('invoices', 'gst_type')) {
                $table->enum('gst_type', ['intra_state', 'inter_state', 'export', 'exempt'])->default('intra_state')->after('state_code');
            }
            if (!Schema::hasColumn('invoices', 'cgst_total')) {
                $table->decimal('cgst_total', 15, 2)->default(0.00)->after('tax_total');
            }
            if (!Schema::hasColumn('invoices', 'sgst_total')) {
                $table->decimal('sgst_total', 15, 2)->default(0.00)->after('cgst_total');
            }
            if (!Schema::hasColumn('invoices', 'igst_total')) {
                $table->decimal('igst_total', 15, 2)->default(0.00)->after('sgst_total');
            }
            if (!Schema::hasColumn('invoices', 'is_reverse_charge')) {
                $table->boolean('is_reverse_charge')->default(false)->after('igst_total');
            }
        });

        // 3. Add GST fields to Invoice Items table
        Schema::table('invoice_items', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_items', 'hsn_code')) {
                $table->string('hsn_code', 20)->nullable()->after('description');
            }
            if (!Schema::hasColumn('invoice_items', 'taxable_value')) {
                $table->decimal('taxable_value', 15, 2)->default(0.00)->after('discount_percent');
            }
            if (!Schema::hasColumn('invoice_items', 'cgst_rate')) {
                $table->decimal('cgst_rate', 5, 2)->default(0.00)->after('tax_rate');
            }
            if (!Schema::hasColumn('invoice_items', 'cgst_amount')) {
                $table->decimal('cgst_amount', 15, 2)->default(0.00)->after('cgst_rate');
            }
            if (!Schema::hasColumn('invoice_items', 'sgst_rate')) {
                $table->decimal('sgst_rate', 5, 2)->default(0.00)->after('cgst_amount');
            }
            if (!Schema::hasColumn('invoice_items', 'sgst_amount')) {
                $table->decimal('sgst_amount', 15, 2)->default(0.00)->after('sgst_rate');
            }
            if (!Schema::hasColumn('invoice_items', 'igst_rate')) {
                $table->decimal('igst_rate', 5, 2)->default(0.00)->after('sgst_amount');
            }
            if (!Schema::hasColumn('invoice_items', 'igst_amount')) {
                $table->decimal('igst_amount', 15, 2)->default(0.00)->after('igst_rate');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn([
                'hsn_code',
                'taxable_value',
                'cgst_rate',
                'cgst_amount',
                'sgst_rate',
                'sgst_amount',
                'igst_rate',
                'igst_amount',
            ]);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'place_of_supply',
                'state_code',
                'gst_type',
                'cgst_total',
                'sgst_total',
                'igst_total',
                'is_reverse_charge',
            ]);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['state_code', 'shipping_state_code']);
        });
    }
};
