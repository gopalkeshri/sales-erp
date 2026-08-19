<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Contact;
use App\Models\Order;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Customer::with(['assignedUser', 'territory', 'primaryContact']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }
        if ($request->filled('territory_id')) {
            $query->where('territory_id', $request->territory_id);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('company_name', 'like', "%{$s}%")
                  ->orWhere('trade_name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('gst_number', 'like', "%{$s}%");
            });
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));
        return response()->json($customers);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:191',
            'trade_name' => 'nullable|string|max:191',
            'gst_number' => 'nullable|string|max:20',
            'pan_number' => 'nullable|string|max:20',
            'industry' => 'nullable|string|max:100',
            'website' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:191',
            'address_street' => 'nullable|string|max:255',
            'address_city' => 'nullable|string|max:100',
            'address_state' => 'nullable|string|max:100',
            'address_country' => 'nullable|string|max:100',
            'address_postal_code' => 'nullable|string|max:20',
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
            'type' => 'nullable|in:enterprise,mid_market,small_business',
            'status' => 'nullable|in:active,inactive,prospect',
            'assigned_to' => 'nullable|exists:users,id',
            'territory_id' => 'nullable|exists:territories,id',
            'tags' => 'nullable|array',
            'notes' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|in:net_30,net_60,due_on_receipt',
            'currency' => 'nullable|string|size:3',
        ]);

        $validated['created_by'] = $request->user() ? $request->user()->id : null;
        $customer = Customer::create($validated);

        return response()->json($customer->load('assignedUser', 'territory'), 201);
    }

    public function show(int $id): JsonResponse
    {
        $customer = Customer::with([
            'contacts',
            'assignedUser',
            'territory',
            'opportunities',
            'quotes',
            'orders',
            'invoices',
        ])->findOrFail($id);

        return response()->json($customer);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);
        $validated = $request->validate([
            'company_name' => 'sometimes|required|string|max:191',
            'trade_name' => 'nullable|string|max:191',
            'gst_number' => 'nullable|string|max:20',
            'pan_number' => 'nullable|string|max:20',
            'industry' => 'nullable|string|max:100',
            'website' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:191',
            'address_street' => 'nullable|string|max:255',
            'address_city' => 'nullable|string|max:100',
            'address_state' => 'nullable|string|max:100',
            'address_country' => 'nullable|string|max:100',
            'address_postal_code' => 'nullable|string|max:20',
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
            'type' => 'nullable|in:enterprise,mid_market,small_business',
            'status' => 'nullable|in:active,inactive,prospect',
            'assigned_to' => 'nullable|exists:users,id',
            'territory_id' => 'nullable|exists:territories,id',
            'tags' => 'nullable|array',
            'notes' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|in:net_30,net_60,due_on_receipt',
            'currency' => 'nullable|string|size:3',
        ]);

        $customer->update($validated);
        return response()->json($customer->fresh(['assignedUser', 'territory']));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
        return response()->json(['message' => 'Customer deleted successfully.']);
    }

    public function contacts(int $id): JsonResponse
    {
        $contacts = Contact::where('customer_id', $id)->get();
        return response()->json($contacts);
    }

    public function orders(int $id): JsonResponse
    {
        $orders = Order::where('customer_id', $id)->with('items.product')->orderBy('created_at', 'desc')->get();
        return response()->json($orders);
    }

    public function invoices(int $id): JsonResponse
    {
        $invoices = Invoice::where('customer_id', $id)->with('payments')->orderBy('created_at', 'desc')->get();
        return response()->json($invoices);
    }
}
