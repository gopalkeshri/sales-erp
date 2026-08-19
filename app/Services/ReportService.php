<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Territory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getDashboardMetrics(?User $user = null): array
    {
        $oppQuery = Opportunity::query();
        $orderQuery = Order::query();
        $invoiceQuery = Invoice::query();
        $leadQuery = Lead::query();
        $customerQuery = Customer::query();

        if ($user && $user->role === 'sales_rep') {
            $oppQuery->where('assigned_to', $user->id);
            $orderQuery->where('assigned_to', $user->id);
            $invoiceQuery->where('assigned_to', $user->id);
            $leadQuery->where('assigned_to', $user->id);
            $customerQuery->where('assigned_to', $user->id);
        }

        $totalRevenue = (float) (clone $invoiceQuery)->where('status', 'paid')->sum('total');
        if ($totalRevenue <= 0) {
            $totalRevenue = (float) (clone $oppQuery)->where('stage', 'closed_won')->sum('amount');
        }

        $totalPipeline = (float) (clone $oppQuery)->whereNotIn('stage', ['closed_won', 'closed_lost'])->sum('amount');
        $weightedPipeline = (float) (clone $oppQuery)->whereNotIn('stage', ['closed_won', 'closed_lost'])->sum('expected_revenue');
        $activeDeals = (clone $oppQuery)->whereNotIn('stage', ['closed_won', 'closed_lost'])->count();
        $closedWonDeals = (clone $oppQuery)->where('stage', 'closed_won')->count();
        $totalLeads = (clone $leadQuery)->count();
        $newLeads = (clone $leadQuery)->where('status', 'new')->count();
        $totalCustomers = (clone $customerQuery)->count();
        $pendingOrders = (clone $orderQuery)->whereIn('status', ['pending', 'confirmed', 'processing'])->count();
        $overdueInvoices = (clone $invoiceQuery)->where('due_date', '<', now()->toDateString())->where('status', '!=', 'paid')->count();

        // Win rate calculation
        $totalClosed = (clone $oppQuery)->whereIn('stage', ['closed_won', 'closed_lost'])->count();
        $winRate = $totalClosed > 0 ? round(($closedWonDeals / $totalClosed) * 100, 1) : 0;

        return [
            'total_revenue' => $totalRevenue,
            'total_pipeline' => $totalPipeline,
            'weighted_pipeline' => $weightedPipeline,
            'active_deals' => $activeDeals,
            'closed_won_deals' => $closedWonDeals,
            'win_rate' => $winRate,
            'total_leads' => $totalLeads,
            'new_leads' => $newLeads,
            'total_customers' => $totalCustomers,
            'pending_orders' => $pendingOrders,
            'overdue_invoices' => $overdueInvoices,
        ];
    }

    public function getSalesSummary(?string $startDate = null, ?string $endDate = null): array
    {
        $startDate = $startDate ?: now()->startOfYear()->toDateString();
        $endDate = $endDate ?: now()->toDateString();

        $closedWon = Opportunity::where('stage', 'closed_won')
            ->whereBetween('actual_close_date', [$startDate, $endDate])
            ->sum('amount');

        $invoiced = Invoice::whereBetween('invoice_date', [$startDate, $endDate])->sum('total');
        $paid = Invoice::where('status', 'paid')->whereBetween('invoice_date', [$startDate, $endDate])->sum('total');
        $outstanding = Invoice::whereIn('status', ['draft', 'sent', 'partial', 'overdue'])
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('balance_due');

        return [
            'period' => ['start' => $startDate, 'end' => $endDate],
            'closed_won_amount' => (float) $closedWon,
            'total_invoiced' => (float) $invoiced,
            'total_collected' => (float) $paid,
            'outstanding_balance' => (float) $outstanding,
        ];
    }

    public function getTopPerformers(int $limit = 5): array
    {
        return User::where('role', '!=', 'admin')
            ->withCount(['opportunities as closed_won_count' => function ($q) {
                $q->where('stage', 'closed_won');
            }])
            ->withSum(['opportunities as total_sales' => function ($q) {
                $q->where('stage', 'closed_won');
            }], 'amount')
            ->orderByDesc('total_sales')
            ->limit($limit)
            ->get()
            ->map(function ($rep) {
                return [
                    'id' => $rep->id,
                    'name' => $rep->name,
                    'email' => $rep->email,
                    'role' => $rep->role,
                    'deals_won' => (int) $rep->closed_won_count,
                    'total_sales' => (float) ($rep->total_sales ?: 0),
                ];
            })
            ->toArray();
    }

    public function getTerritoryPerformance(): array
    {
        return Territory::withCount('customers')
            ->withSum(['opportunities as total_sales' => function ($q) {
                $q->where('stage', 'closed_won');
            }], 'amount')
            ->withSum(['opportunities as active_pipeline' => function ($q) {
                $q->whereNotIn('stage', ['closed_won', 'closed_lost']);
            }], 'amount')
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'name' => $t->name,
                    'region' => $t->region,
                    'customers_count' => $t->customers_count,
                    'total_sales' => (float) ($t->total_sales ?: 0),
                    'active_pipeline' => (float) ($t->active_pipeline ?: 0),
                ];
            })
            ->toArray();
    }

    public function getProductPerformance(): array
    {
        return Product::withCount('inventories')
            ->get()
            ->map(function ($product) {
                $soldCount = (int) DB::table('order_items')
                    ->where('product_id', $product->id)
                    ->sum('quantity');

                $soldRevenue = (float) DB::table('order_items')
                    ->where('product_id', $product->id)
                    ->sum('total');

                return [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'category' => $product->category,
                    'unit_price' => (float) $product->unit_price,
                    'total_stock' => $product->total_stock,
                    'units_sold' => $soldCount,
                    'revenue_generated' => $soldRevenue,
                ];
            })
            ->sortByDesc('revenue_generated')
            ->values()
            ->toArray();
    }

    public function getRevenueTrends(int $months = 6): array
    {
        $trends = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $yearMonth = $date->format('Y-m');
            $label = $date->format('M Y');
            $start = $date->copy()->startOfMonth()->toDateString();
            $end = $date->copy()->endOfMonth()->toDateString();

            $revenue = (float) Invoice::where('status', 'paid')
                ->whereBetween('invoice_date', [$start, $end])
                ->sum('total');

            if ($revenue <= 0) {
                $revenue = (float) Opportunity::where('stage', 'closed_won')
                    ->whereBetween('actual_close_date', [$start, $end])
                    ->sum('amount');
            }

            $pipeline = (float) Opportunity::whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
                ->sum('amount');

            $trends[] = [
                'period' => $yearMonth,
                'label' => $label,
                'revenue' => $revenue,
                'pipeline_added' => $pipeline,
            ];
        }

        return $trends;
    }
}
