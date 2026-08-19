<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Inventory;
use App\Models\Quote;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Commission;
use App\Models\Territory;
use App\Models\Team;
use App\Models\User;
use App\Models\Activity;
use App\Services\ReportService;
use App\Services\OpportunityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalesErpController extends Controller
{
    protected ReportService $reportService;
    protected OpportunityService $oppService;

    public function __construct(ReportService $reportService, OpportunityService $oppService)
    {
        $this->reportService = $reportService;
        $this->oppService = $oppService;
    }

    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $metrics = $this->reportService->getDashboardMetrics($currentUser);
        $pipeline = $this->oppService->getPipelineSummary();
        $revenueTrends = $this->reportService->getRevenueTrends(6);
        $topPerformers = $this->reportService->getTopPerformers(5);
        $territoryPerformance = $this->reportService->getTerritoryPerformance();
        $productPerformance = $this->reportService->getProductPerformance();

        $leads = Lead::with(['customer', 'assignedUser'])->orderBy('created_at', 'desc')->get();
        $opportunities = Opportunity::with(['customer', 'assignedUser', 'opportunityProducts.product'])->orderBy('created_at', 'desc')->get();
        $customers = Customer::with(['contacts', 'assignedUser', 'territory'])->orderBy('company_name')->get();
        $quotes = Quote::with(['customer', 'assignedUser', 'items.product'])->orderBy('created_at', 'desc')->get();
        $orders = Order::with(['customer', 'assignedUser', 'items.product', 'invoices'])->orderBy('created_at', 'desc')->get();
        $invoices = Invoice::with(['customer', 'assignedUser', 'items.product', 'payments'])->orderBy('created_at', 'desc')->get();
        $inventory = Inventory::with(['product', 'warehouse'])->get();
        $products = Product::where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $commissions = Commission::with(['user', 'adjustments'])->orderBy('period', 'desc')->get();
        $territories = Territory::with(['manager', 'parent'])->get();
        $users = User::where('is_active', true)->get();
        $activities = Activity::with('performer')->orderBy('created_at', 'desc')->limit(15)->get();

        return view('erp.index', compact(
            'currentUser',
            'metrics',
            'pipeline',
            'revenueTrends',
            'topPerformers',
            'territoryPerformance',
            'productPerformance',
            'leads',
            'opportunities',
            'customers',
            'quotes',
            'orders',
            'invoices',
            'inventory',
            'products',
            'warehouses',
            'commissions',
            'territories',
            'users',
            'activities'
        ));
    }

    public function switchUser(Request $request)
    {
        $userId = $request->get('user_id');
        $user = User::findOrFail($userId);
        Auth::login($user);
        return redirect()->route('erp.dashboard')->with('success', "Switched user to {$user->name} ({$user->role})");
    }
}
