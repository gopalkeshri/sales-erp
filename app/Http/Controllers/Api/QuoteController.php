<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Services\QuoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    protected QuoteService $quoteService;

    public function __construct(QuoteService $quoteService)
    {
        $this->quoteService = $quoteService;
    }

    public function index(Request $request): JsonResponse
    {
        $query = Quote::with(['customer', 'contact', 'opportunity', 'assignedUser']);

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
            $query->where('quote_number', 'like', "%{$s}%")
                  ->orWhereHas('customer', function ($q) use ($s) {
                      $q->where('company_name', 'like', "%{$s}%");
                  });
        }

        $quotes = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));
        return response()->json($quotes);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'quote_number' => 'nullable|string|unique:quotes,quote_number|max:20',
            'customer_id' => 'required|exists:customers,id',
            'contact_id' => 'nullable|exists:contacts,id',
            'opportunity_id' => 'nullable|exists:opportunities,id',
            'status' => 'nullable|in:draft,sent,accepted,rejected,expired,converted',
            'valid_until' => 'nullable|date',
            'currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
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

        $quote = $this->quoteService->createQuote($validated, $items, $request->user());
        return response()->json($quote, 201);
    }

    public function show(int $id): JsonResponse
    {
        $quote = Quote::with(['customer.contacts', 'contact', 'opportunity', 'assignedUser', 'items.product'])->findOrFail($id);
        return response()->json($quote);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $quote = Quote::findOrFail($id);
        $validated = $request->validate([
            'status' => 'nullable|in:draft,sent,accepted,rejected,expired,converted',
            'valid_until' => 'nullable|date',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'territory_id' => 'nullable|exists:territories,id',
        ]);

        $quote->update($validated);
        return response()->json($quote->fresh(['customer', 'contact', 'items.product']));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $quote = Quote::findOrFail($id);
        $quote->delete();
        return response()->json(['message' => 'Quote deleted successfully.']);
    }

    public function send(Request $request, int $id): JsonResponse
    {
        $quote = Quote::findOrFail($id);
        $quote->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return response()->json(['message' => 'Quote sent to customer successfully.', 'quote' => $quote]);
    }

    public function accept(Request $request, int $id): JsonResponse
    {
        $quote = Quote::findOrFail($id);
        $quote->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        return response()->json(['message' => 'Quote accepted.', 'quote' => $quote]);
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $quote = Quote::findOrFail($id);
        $quote->update([
            'status' => 'rejected',
            'rejected_at' => now(),
        ]);

        return response()->json(['message' => 'Quote marked as rejected.', 'quote' => $quote]);
    }

    public function convertToOrder(Request $request, int $id): JsonResponse
    {
        $quote = Quote::with('items')->findOrFail($id);
        $order = $this->quoteService->convertToOrder($quote, $request->user());

        return response()->json([
            'message' => 'Quote successfully converted to order.',
            'order' => $order,
        ]);
    }

    public function pdfData(int $id): JsonResponse
    {
        $quote = Quote::with(['customer.contacts', 'contact', 'opportunity', 'assignedUser', 'items.product'])->findOrFail($id);
        $settings = \App\Models\Setting::getAllKeyValue();

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

        return response()->json([
            'quote' => $quote,
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
                'is_intra_state' => ($quote->gst_type === 'intra_state' || $buyerStateCode === $companyStateCode),
            ],
        ]);
    }
}
