<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\CommissionAdjustment;
use App\Models\Opportunity;
use App\Models\Invoice;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

class CommissionService
{
    public function calculateUserCommission(int $userId, string $period, string $periodType = 'monthly', float $commissionRate = 5.0): Commission
    {
        return DB::transaction(function () use ($userId, $period, $periodType, $commissionRate) {
            // Find start and end date based on period (e.g. "2026-08")
            $startDate = $period . '-01';
            $endDate = date('Y-m-t', strtotime($startDate));

            // Sum closed_won opportunities for the user in this period
            $totalSales = (float) Opportunity::where('assigned_to', $userId)
                ->where('stage', 'closed_won')
                ->whereBetween('actual_close_date', [$startDate, $endDate])
                ->sum('amount');

            // If no opportunities found, also consider paid invoices assigned to user
            if ($totalSales <= 0) {
                $totalSales = (float) Invoice::where('assigned_to', $userId)
                    ->where('status', 'paid')
                    ->whereBetween('invoice_date', [$startDate, $endDate])
                    ->sum('total');
            }

            $commissionAmount = $totalSales * ($commissionRate / 100);

            // Tiered bonus: if sales > $50,000 bonus $1000, if > $100,000 bonus $3000
            $bonus = 0;
            if ($totalSales >= 100000) {
                $bonus = 3000.00;
            } elseif ($totalSales >= 50000) {
                $bonus = 1000.00;
            }

            $commission = Commission::updateOrCreate(
                [
                    'user_id' => $userId,
                    'period' => $period,
                    'period_type' => $periodType,
                ],
                [
                    'total_sales' => $totalSales,
                    'commission_rate' => $commissionRate,
                    'commission_amount' => $commissionAmount,
                    'bonus_amount' => $bonus,
                ]
            );

            return $commission->fresh(['user', 'approver', 'adjustments']);
        });
    }

    public function approveCommission(Commission $commission, User $approver): Commission
    {
        $commission->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        return $commission->fresh(['user', 'approver', 'adjustments']);
    }

    public function markPaid(Commission $commission): Commission
    {
        $commission->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return $commission->fresh(['user', 'approver', 'adjustments']);
    }

    public function addAdjustment(Commission $commission, string $type, float $amount, ?string $reason = null): CommissionAdjustment
    {
        return CommissionAdjustment::create([
            'commission_id' => $commission->id,
            'type' => $type,
            'amount' => $amount,
            'reason' => $reason,
            'created_at' => now(),
        ]);
    }
}
