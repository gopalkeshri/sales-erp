<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function index(Request $request): JsonResponse
    {
        $query = Invoice::with(['customer', 'contact', 'order', 'assignedUser', 'payments']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('invoice_number', 'like', "%{$s}%")
                  ->orWhereHas('customer', function ($q) use ($s) {
                      $q->where('company_name', 'like', "%{$s}%");
                  });
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));
        return response()->json($invoices);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invoice_number' => 'nullable|string|unique:invoices,invoice_number|max:20',
            'order_id' => 'nullable|exists:orders,id',
            'customer_id' => 'required|exists:customers,id',
            'contact_id' => 'nullable|exists:contacts,id',
            'status' => 'nullable|in:draft,sent,paid,partial,overdue,cancelled',
            'type' => 'nullable|in:sales,proforma,credit_note',
            'invoice_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'currency' => 'nullable|string|size:3',
            'payment_terms' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'territory_id' => 'nullable|exists:territories,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $items = $validated['items'];
        unset($validated['items']);

        $invoice = $this->invoiceService->createInvoice($validated, $items, $request->user());
        return response()->json($invoice, 201);
    }

    public function show(int $id): JsonResponse
    {
        $invoice = Invoice::with(['customer.contacts', 'contact', 'order', 'assignedUser', 'items.product', 'payments.creator'])->findOrFail($id);
        return response()->json($invoice);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $invoice = Invoice::findOrFail($id);
        $validated = $request->validate([
            'status' => 'nullable|in:draft,sent,paid,partial,overdue,cancelled',
            'due_date' => 'nullable|date',
            'payment_terms' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $invoice->update($validated);
        return response()->json($invoice->fresh(['customer', 'contact', 'items.product', 'payments']));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();
        return response()->json(['message' => 'Invoice deleted successfully.']);
    }

    public function send(Request $request, int $id): JsonResponse
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->update(['status' => 'sent']);
        return response()->json(['message' => 'Invoice sent to customer successfully.', 'invoice' => $invoice]);
    }

    public function recordPayment(Request $request, int $id): JsonResponse
    {
        $invoice = Invoice::findOrFail($id);
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'nullable|date',
            'payment_method' => 'required|in:cash,bank_transfer,check,credit_card,upi,other',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $payment = $this->invoiceService->recordPayment($invoice, $validated, $request->user());

        return response()->json([
            'message' => 'Payment recorded successfully.',
            'payment' => $payment,
            'invoice' => $invoice->fresh(['payments', 'customer']),
        ], 201);
    }

    public function overdue(): JsonResponse
    {
        $overdue = $this->invoiceService->getOverdueInvoices();
        return response()->json($overdue);
    }

    public function pdfData(int $id): JsonResponse
    {
        $invoice = Invoice::with(['customer.contacts', 'contact', 'order', 'assignedUser', 'items.product', 'payments'])->findOrFail($id);
        $settings = \App\Models\Setting::getAllKeyValue();

        $companyStateCode = $settings['company_state_code'] ?? '27';
        $companyState = $settings['company_state'] ?? 'Maharashtra';
        $buyerStateCode = $invoice->state_code ?? $invoice->customer->state_code ?? $companyStateCode;
        $buyerState = $invoice->place_of_supply ?: (\App\Services\GstService::getStateByCode($buyerStateCode) ?? $invoice->customer->address_state);

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

        return response()->json([
            'invoice' => $invoice,
            'amount_in_words' => $amountInWords,
            'hsn_summary' => array_values($hsnSummary),
            'company' => [
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
            ],
            'buyer' => [
                'state' => $buyerState,
                'state_code' => $buyerStateCode,
                'is_intra_state' => ($invoice->gst_type === 'intra_state' || $buyerStateCode === $companyStateCode),
            ],
        ]);
    }
}
