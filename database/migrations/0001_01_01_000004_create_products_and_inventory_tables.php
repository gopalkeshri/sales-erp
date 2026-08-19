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
        // 1. Products table
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 50)->unique();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('category', 100)->nullable();
            $table->string('subcategory', 100)->nullable();
            $table->enum('type', ['product', 'service'])->default('product');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('cost_price', 15, 2)->default(0.00);
            $table->string('currency', 3)->default('USD');
            $table->string('unit', 20)->default('piece');
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->string('hsn_code', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('min_stock_level')->default(0);
            $table->integer('reorder_point')->default(0);
            $table->string('image', 255)->nullable();
            $table->json('attributes')->nullable();
            $table->timestamps();

            $table->index('sku', 'idx_sku');
            $table->index('category', 'idx_category');
            $table->index('type', 'idx_type_product');
            $table->index('is_active', 'idx_active_product');
        });

        // 2. Warehouses table
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('code', 20)->unique();
            $table->string('street', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('manager_id')->references('id')->on('users')->onDelete('set null');
            $table->index('code', 'idx_code');
            $table->index('is_active', 'idx_active_warehouse');
        });

        // 3. Inventory table
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->timestamp('last_restocked_at')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->unique(['product_id', 'warehouse_id'], 'unique_product_warehouse');
            $table->index('warehouse_id', 'idx_warehouse_inventory');
        });

        // 4. Inventory Transactions table
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->enum('type', ['in', 'out', 'transfer', 'adjustment']);
            $table->integer('quantity');
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('set null');

            $table->index('product_id', 'idx_product_trans');
            $table->index('warehouse_id', 'idx_warehouse_trans');
            $table->index('type', 'idx_type_trans');
            $table->index('created_at', 'idx_created_trans');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('inventory');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('products');
    }
};
