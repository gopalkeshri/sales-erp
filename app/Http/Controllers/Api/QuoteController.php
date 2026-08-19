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
        $quote = Quote::with(['customer', 'contact', 'assignedUser', 'items.product'])->findOrFail($id);
        return response()->json([
            'quote' => $quote,
            'company' => [
                'name' => config('app.name', 'Sales ERP Corp'),
                'address' => '100 Enterprise Way, Silicon Valley, CA 94025',
                'phone' => '+1 (555) 019-2834',
                'email' => 'sales@saleserp.com',
                'gst_vat' => 'US-ERP-9920194',
            ],
        ]);
    }
}
