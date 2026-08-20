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
use App\Models\Setting;
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
        $salesSummary = $this->reportService->getSalesSummary();
        $taxSummary = $this->reportService->getTaxSummary();

        $leads = Lead::with(['customer', 'assignedUser'])->orderBy('created_at', 'desc')->get();
        $opportunities = Opportunity::with(['customer', 'assignedUser', 'opportunityProducts.product'])->orderBy('created_at', 'desc')->get();
        $customers = Customer::with(['contacts', 'assignedUser', 'territory', 'invoices.payments'])->orderBy('company_name')->get();
        $quotes = Quote::with(['customer', 'assignedUser', 'items.product'])->orderBy('created_at', 'desc')->get();
        $orders = Order::with(['customer', 'assignedUser', 'items.product', 'invoices'])->orderBy('created_at', 'desc')->get();
        $invoices = Invoice::with(['customer.contacts', 'assignedUser', 'items.product', 'payments'])->orderBy('created_at', 'desc')->get();
        $inventory = Inventory::with(['product', 'warehouse'])->get();
        $products = Product::with('inventories.warehouse')->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $commissions = Commission::with(['user', 'adjustments'])->orderBy('period', 'desc')->get();
        $territories = Territory::with(['manager', 'parent'])->get();
        $users = User::where('is_active', true)->get();
        $activities = Activity::with('performer')->orderBy('created_at', 'desc')->limit(15)->get();
        $settings = Setting::getAllKeyValue();
        $indianStates = \App\Services\GstService::getIndianStates();
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
            'server_time' => now()->toDateTimeString(),
            'server_timezone' => config('app.timezone', 'UTC'),
            'database_driver' => config('database.default'),
        ];

        return view('erp.index', compact(
            'currentUser',
            'metrics',
            'pipeline',
            'revenueTrends',
            'topPerformers',
            'territoryPerformance',
            'productPerformance',
            'salesSummary',
            'taxSummary',
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
            'activities',
            'settings',
            'indianStates',
            'systemInfo'
        ));
    }

    public function switchUser(Request $request)
    {
        $userId = $request->get('user_id');
        $user = User::findOrFail($userId);
        Auth::login($user);
        return redirect()->route('erp.dashboard')->with('success', "Switched user to {$user->name} ({$user->role})");
    }

    public function printInvoice(int $id)
    {
        $invoice = Invoice::with(['customer.contacts', 'contact', 'order', 'assignedUser', 'items.product', 'payments'])->findOrFail($id);
        $settings = Setting::getAllKeyValue();

        $companyStateCode = $settings['company_state_code'] ?? '27';
        $companyState = $settings['company_state'] ?? 'Maharashtra';
        $buyerStateCode = $invoice->state_code ?? ($invoice->customer->state_code ?? $companyStateCode);
        $buyerState = $invoice->place_of_supply ?: (\App\Services\GstService::getStateByCode($buyerStateCode) ?? ($invoice->customer->billing_state ?? ($invoice->customer->address_state ?? 'Maharashtra')));

        $amountInWords = \App\Services\GstService::numberToIndianWords((float) $invoice->total);

        // HSN Summary Grouping
        $hsnSummary = [];
        foreach ($invoice->items as $item) {
            $hsn = $item->hsn_code ?: ($item->product->hsn_code ?? 'N/A');
            if (!isset($hsnSummary[$hsn])) {
                $hsnSummary[$hsn] = [
                    'hsn_code' => $hsn,
                    'taxable_value' => 0,
                    'cgst_rate' => $item->cgst_rate,
                    'cgst_amount' => 0,
                    'sgst_rate' => $item->sgst_rate,
                    'sgst_amount' => 0,
                    'igst_rate' => $item->igst_rate,
                    'igst_amount' => 0,
                    'total_tax' => 0,
                ];
            }
            $hsnSummary[$hsn]['taxable_value'] += (float)$item->taxable_value;
            $hsnSummary[$hsn]['cgst_amount'] += (float)$item->cgst_amount;
            $hsnSummary[$hsn]['sgst_amount'] += (float)$item->sgst_amount;
            $hsnSummary[$hsn]['igst_amount'] += (float)$item->igst_amount;
            $hsnSummary[$hsn]['total_tax'] += (float)($item->cgst_amount + $item->sgst_amount + $item->igst_amount);
        }

        $company = [
            'name' => $settings['company_name'] ?? 'Apex Enterprise Solutions Pvt. Ltd.',
            'tagline' => $settings['company_tagline'] ?? 'Enterprise B2B Revenue & GST Fulfillment Platform',
            'address' => $settings['company_address'] ?? '100 Enterprise Tower, BKC Complex',
            'city' => $settings['company_city'] ?? 'Mumbai',
            'state' => $companyState,
            'state_code' => $companyStateCode,
            'postal_code' => $settings['company_postal_code'] ?? '400051',
            'country' => $settings['company_country'] ?? 'India',
            'phone' => $settings['company_phone'] ?? '+91 (22) 6789-0123',
            'email' => $settings['company_email'] ?? 'billing@saleserp.in',
            'gstin' => $settings['tax_id'] ?? '27AAACA1234F1Z5',
            'pan' => $settings['company_pan'] ?? 'AAACA1234F',
            'bank_name' => $settings['bank_name'] ?? 'HDFC Bank Ltd.',
            'bank_account_no' => $settings['bank_account_no'] ?? '50200012345678',
            'bank_ifsc' => $settings['bank_ifsc'] ?? 'HDFC0000123',
            'bank_branch' => $settings['bank_branch'] ?? 'BKC Bandra, Mumbai',
            'upi_id' => $settings['upi_id'] ?? 'apexsolutions@okhdfcbank',
            'currency_symbol' => $settings['currency_symbol'] ?? '₹',
        ];

        $buyer = [
            'state' => $buyerState,
            'state_code' => $buyerStateCode,
            'is_intra_state' => ($invoice->gst_type === 'intra_state' || $buyerStateCode === $companyStateCode),
        ];

        $hsn_summary = array_values($hsnSummary);
        $amount_in_words = $amountInWords;

        return view('erp.invoice-print', compact('invoice', 'company', 'buyer', 'hsn_summary', 'amount_in_words'));
    }

    public function printQuote(int $id)
    {
        $quote = Quote::with(['customer.contacts', 'contact', 'opportunity', 'assignedUser', 'items.product'])->findOrFail($id);
        $settings = Setting::getAllKeyValue();

        $companyStateCode = $settings['company_state_code'] ?? '27';
        $companyState = $settings['company_state'] ?? 'Maharashtra';
        $buyerStateCode = $quote->state_code ?? ($quote->customer->state_code ?? $companyStateCode);
        $buyerState = $quote->place_of_supply ?: (\App\Services\GstService::getStateByCode($buyerStateCode) ?? ($quote->customer->billing_state ?? ($quote->customer->address_state ?? 'Maharashtra')));

        $amountInWords = \App\Services\GstService::numberToIndianWords((float) $quote->total);

        // HSN Summary Grouping
        $hsnSummary = [];
        foreach ($quote->items as $item) {
            $hsn = $item->hsn_code ?: ($item->product->hsn_code ?? 'N/A');
            if (!isset($hsnSummary[$hsn])) {
                $hsnSummary[$hsn] = [
                    'hsn_code' => $hsn,
                    'taxable_value' => 0,
                    'cgst_rate' => $item->cgst_rate,
                    'cgst_amount' => 0,
                    'sgst_rate' => $item->sgst_rate,
                    'sgst_amount' => 0,
                    'igst_rate' => $item->igst_rate,
                    'igst_amount' => 0,
                    'total_tax' => 0,
                ];
            }
            $hsnSummary[$hsn]['taxable_value'] += (float)$item->taxable_value;
            $hsnSummary[$hsn]['cgst_amount'] += (float)$item->cgst_amount;
            $hsnSummary[$hsn]['sgst_amount'] += (float)$item->sgst_amount;
            $hsnSummary[$hsn]['igst_amount'] += (float)$item->igst_amount;
            $hsnSummary[$hsn]['total_tax'] += (float)($item->cgst_amount + $item->sgst_amount + $item->igst_amount);
        }

        $company = [
            'name' => $settings['company_name'] ?? 'Apex Enterprise Solutions Pvt. Ltd.',
            'tagline' => $settings['company_tagline'] ?? 'Enterprise B2B Revenue & GST Fulfillment Platform',
            'address' => $settings['company_address'] ?? '100 Enterprise Tower, BKC Complex',
            'city' => $settings['company_city'] ?? 'Mumbai',
            'state' => $companyState,
            'state_code' => $companyStateCode,
            'postal_code' => $settings['company_postal_code'] ?? '400051',
            'country' => $settings['company_country'] ?? 'India',
            'phone' => $settings['company_phone'] ?? '+91 (22) 6789-0123',
            'email' => $settings['company_email'] ?? 'billing@saleserp.in',
            'gstin' => $settings['tax_id'] ?? '27AAACA1234F1Z5',
            'pan' => $settings['company_pan'] ?? 'AAACA1234F',
            'bank_name' => $settings['bank_name'] ?? 'HDFC Bank Ltd.',
            'bank_account_no' => $settings['bank_account_no'] ?? '50200012345678',
            'bank_ifsc' => $settings['bank_ifsc'] ?? 'HDFC0000123',
            'bank_branch' => $settings['bank_branch'] ?? 'BKC Bandra, Mumbai',
            'upi_id' => $settings['upi_id'] ?? 'apexsolutions@okhdfcbank',
            'currency_symbol' => $settings['currency_symbol'] ?? '₹',
        ];

        $buyer = [
            'state' => $buyerState,
            'state_code' => $buyerStateCode,
            'is_intra_state' => ($quote->gst_type === 'intra_state' || $buyerStateCode === $companyStateCode),
        ];

        $hsn_summary = array_values($hsnSummary);
        $amount_in_words = $amountInWords;

        return view('erp.quote-print', compact('quote', 'company', 'buyer', 'hsn_summary', 'amount_in_words'));
    }
}
