<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['customer', 'contact', 'opportunity', 'assignedUser', 'invoices']);

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
            $query->where('order_number', 'like', "%{$s}%")
                  ->orWhereHas('customer', function ($q) use ($s) {
                      $q->where('company_name', 'like', "%{$s}%");
                  });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));
        return response()->json($orders);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_number' => 'nullable|string|unique:orders,order_number|max:20',
            'customer_id' => 'required|exists:customers,id',
            'contact_id' => 'nullable|exists:contacts,id',
            'opportunity_id' => 'nullable|exists:opportunities,id',
            'quote_id' => 'nullable|exists:quotes,id',
            'status' => 'nullable|in:pending,confirmed,processing,shipped,delivered,cancelled',
            'billing_street' => 'nullable|string|max:255',
            'billing_city' => 'nullable|string|max:100',
            'billing_state' => 'nullable|string|max:100',
            'billing_country' => 'nullable|string|max:100',
            'billing_postal_code' => 'nullable|string|max:20',
            'shipping_street' => 'nullable|string|max:255',
            'shipping_city' => 'nullable|string|max:100',
            'shipping_state' => 'nullable|string|max:100',
            'shipping_country' => 'nullable|string|max:100',
            'shipping_postal_code' => 'nullable|string|max:20',
            'shipping_cost' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'payment_terms' => 'nullable|string|max:50',
            'expected_delivery_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'territory_id' => 'nullable|exists:territories,id',
            'notes' => 'nullable|string',
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

        $order = $this->orderService->createOrder($validated, $items, $request->user());
        return response()->json($order, 201);
    }

    public function show(int $id): JsonResponse
    {
        $order = Order::with(['customer.contacts', 'contact', 'opportunity', 'quote', 'assignedUser', 'items.product', 'invoices.payments'])->findOrFail($id);
        return response()->json($order);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $validated = $request->validate([
            'status' => 'nullable|in:pending,confirmed,processing,shipped,delivered,cancelled',
            'payment_terms' => 'nullable|string|max:50',
            'expected_delivery_date' => 'nullable|date',
            'actual_delivery_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $order->update($validated);
        return response()->json($order->fresh(['customer', 'contact', 'items.product']));
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $order = Order::with('items')->findOrFail($id);
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
            'warehouse_id' => 'nullable|exists:warehouses,id',
        ]);

        $order = $this->orderService->updateStatus($order, $validated['status'], $validated['warehouse_id'] ?? null, $request->user());
        return response()->json([
            'message' => "Order status updated to {$validated['status']}.",
            'order' => $order,
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $order->delete();
        return response()->json(['message' => 'Order deleted successfully.']);
    }

    public function generateInvoice(Request $request, int $id): JsonResponse
    {
        $order = Order::with('items')->findOrFail($id);
        $invoice = $this->orderService->generateInvoice($order, $request->user());

        return response()->json([
            'message' => 'Invoice created successfully from order.',
            'invoice' => $invoice,
        ]);
    }
}
