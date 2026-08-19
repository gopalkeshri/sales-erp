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
        // 1. Commissions table
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('period', 20);
            $table->enum('period_type', ['monthly', 'quarterly', 'yearly']);
            $table->decimal('total_sales', 15, 2)->default(0.00);
            $table->decimal('commission_rate', 5, 2)->default(0.00);
            $table->decimal('commission_amount', 15, 2)->default(0.00);
            $table->decimal('bonus_amount', 15, 2)->default(0.00);
            $table->enum('status', ['pending', 'approved', 'paid'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');

            $table->index('user_id', 'idx_user_commission');
            $table->index('period', 'idx_period_commission');
            $table->index('status', 'idx_status_commission');
            $table->unique(['user_id', 'period', 'period_type'], 'unique_user_period');
        });

        // 2. Commission Adjustments table
        Schema::create('commission_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('commission_id');
            $table->enum('type', ['bonus', 'penalty', 'adjustment']);
            $table->decimal('amount', 15, 2);
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('commission_id')->references('id')->on('commissions')->onDelete('cascade');
            $table->index('commission_id', 'idx_commission_adj');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_adjustments');
        Schema::dropIfExists('commissions');
    }
};
