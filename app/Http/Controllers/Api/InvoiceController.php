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
        $invoice = Invoice::with(['customer', 'contact', 'order', 'assignedUser', 'items.product', 'payments'])->findOrFail($id);
        return response()->json([
            'invoice' => $invoice,
            'company' => [
                'name' => config('app.name', 'Sales ERP Corp'),
                'address' => '100 Enterprise Way, Silicon Valley, CA 94025',
                'phone' => '+1 (555) 019-2834',
                'email' => 'billing@saleserp.com',
                'gst_vat' => 'US-ERP-9920194',
            ],
        ]);
    }
}
