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
        $startDate = $startDate ? substr($startDate, 0, 10) : now()->startOfYear()->toDateString();
        $endDate = $endDate ? substr($endDate, 0, 10) : now()->toDateString();

        $closedWon = Opportunity::where('stage', 'closed_won')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('actual_close_date', [$startDate, $endDate])
                    ->orWhere(function ($sub) use ($startDate, $endDate) {
                        $sub->whereNull('actual_close_date')
                            ->whereDate('updated_at', '>=', $startDate)
                            ->whereDate('updated_at', '<=', $endDate);
                    });
            })
            ->sum('amount');

        $invoiced = Invoice::whereDate('invoice_date', '>=', $startDate)->whereDate('invoice_date', '<=', $endDate)->sum('total');
        $paid = Invoice::where('status', 'paid')->whereDate('invoice_date', '>=', $startDate)->whereDate('invoice_date', '<=', $endDate)->sum('total');
        $outstanding = Invoice::whereIn('status', ['draft', 'sent', 'partial', 'overdue'])
            ->whereDate('invoice_date', '>=', $startDate)
            ->whereDate('invoice_date', '<=', $endDate)
            ->sum('balance_due');

        $totalInvoicesCount = Invoice::whereDate('invoice_date', '>=', $startDate)->whereDate('invoice_date', '<=', $endDate)->count();
        $paidInvoicesCount = Invoice::where('status', 'paid')->whereDate('invoice_date', '>=', $startDate)->whereDate('invoice_date', '<=', $endDate)->count();
        $collectionRate = $invoiced > 0 ? round(($paid / $invoiced) * 100, 1) : 0;

        return [
            'period' => ['start' => $startDate, 'end' => $endDate],
            'closed_won_amount' => (float) $closedWon,
            'total_invoiced' => (float) $invoiced,
            'total_collected' => (float) $paid,
            'outstanding_balance' => (float) $outstanding,
            'total_invoices_count' => $totalInvoicesCount,
            'paid_invoices_count' => $paidInvoicesCount,
            'collection_rate' => $collectionRate,
        ];
    }

    public function getTaxSummary(?string $startDate = null, ?string $endDate = null): array
    {
        $startDate = $startDate ? substr($startDate, 0, 10) : now()->startOfYear()->toDateString();
        $endDate = $endDate ? substr($endDate, 0, 10) : now()->toDateString();

        $query = Invoice::whereDate('invoice_date', '>=', $startDate)->whereDate('invoice_date', '<=', $endDate);

        $totalTaxable = (float) (clone $query)->sum('subtotal');
        $totalCgst = (float) (clone $query)->sum('cgst_total');
        $totalSgst = (float) (clone $query)->sum('sgst_total');
        $totalIgst = (float) (clone $query)->sum('igst_total');
        $totalTax = (float) (clone $query)->sum('tax_total');
        $totalGross = (float) (clone $query)->sum('total');
        $totalPaid = (float) (clone $query)->where('status', 'paid')->sum('total');
        $totalPendingTax = (float) (clone $query)->where('status', '!=', 'paid')->sum('tax_total');
        $invoicesCount = (clone $query)->count();

        return [
            'period' => ['start' => $startDate, 'end' => $endDate],
            'invoices_count' => $invoicesCount,
            'taxable_value' => $totalTaxable,
            'cgst_total' => $totalCgst,
            'sgst_total' => $totalSgst,
            'igst_total' => $totalIgst,
            'tax_total' => $totalTax,
            'gross_total' => $totalGross,
            'total_paid' => $totalPaid,
            'pending_tax' => $totalPendingTax,
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
                $dealsWon = (int) $rep->closed_won_count;
                $totalSales = (float) ($rep->total_sales ?: 0);
                $avgDealSize = $dealsWon > 0 ? round($totalSales / $dealsWon, 2) : 0;
                return [
                    'id' => $rep->id,
                    'name' => $rep->name,
                    'email' => $rep->email,
                    'role' => $rep->role,
                    'deals_won' => $dealsWon,
                    'total_sales' => $totalSales,
                    'avg_deal_size' => $avgDealSize,
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
                    ->where(function ($q) use ($start, $end) {
                        $q->whereBetween('actual_close_date', [$start, $end])
                            ->orWhere(function ($sub) use ($start, $end) {
                                $sub->whereNull('actual_close_date')
                                    ->whereBetween('updated_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
                            });
                    })
                    ->sum('amount');
            }

            $invoiced = (float) Invoice::whereBetween('invoice_date', [$start, $end])->sum('total');

            $pipeline = (float) Opportunity::whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
                ->sum('amount');

            $trends[] = [
                'period' => $yearMonth,
                'label' => $label,
                'revenue' => $revenue,
                'invoiced' => $invoiced,
                'pipeline_added' => $pipeline,
            ];
        }

        return $trends;
    }
}
