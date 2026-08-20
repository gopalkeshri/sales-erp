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
            'state_code' => 'nullable|string|max:5',
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
            'shipping_state_code' => 'nullable|string|max:5',
            'shipping_country' => 'nullable|string|max:100',
            'shipping_postal_code' => 'nullable|string|max:20',
            'type' => 'nullable|in:enterprise,mid_market,small_business',
            'status' => 'nullable|in:active,inactive,prospect',
            'assigned_to' => 'nullable|exists:users,id',
            'territory_id' => 'nullable|exists:territories,id',
            'tags' => 'nullable|array',
            'notes' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|in:net_15,net_30,net_60,net_90,due_on_receipt',
            'currency' => 'nullable|string|size:3',
            'contact_name' => 'nullable|string|max:191',
            'contact_email' => 'nullable|email|max:191',
            'contact_phone' => 'nullable|string|max:20',
            'contact_designation' => 'nullable|string|max:100',
        ]);

        // Auto-extract State Code & PAN from GSTIN if valid
        if (!empty($validated['gst_number'])) {
            $gstinData = \App\Services\GstService::validateGstin($validated['gst_number']);
            if ($gstinData['valid']) {
                $validated['state_code'] = $validated['state_code'] ?? $gstinData['state_code'];
                $validated['pan_number'] = $validated['pan_number'] ?? $gstinData['pan'];
                if (empty($validated['address_state'])) {
                    $validated['address_state'] = $gstinData['state_name'];
                }
            }
        }

        if (!empty($validated['address_state']) && empty($validated['state_code'])) {
            $validated['state_code'] = \App\Services\GstService::getCodeByState($validated['address_state']);
        }

        $contactName = $validated['contact_name'] ?? null;
        $contactEmail = $validated['contact_email'] ?? null;
        $contactPhone = $validated['contact_phone'] ?? null;
        $contactDesignation = $validated['contact_designation'] ?? 'Primary Contact';
        unset($validated['contact_name'], $validated['contact_email'], $validated['contact_phone'], $validated['contact_designation']);

        $validated['created_by'] = $request->user() ? $request->user()->id : null;
        $customer = Customer::create($validated);

        if ($contactName) {
            $nameParts = explode(' ', trim($contactName), 2);
            Contact::create([
                'customer_id' => $customer->id,
                'first_name' => $nameParts[0],
                'last_name' => $nameParts[1] ?? '',
                'email' => $contactEmail ?: $customer->email,
                'phone' => $contactPhone ?: $customer->phone,
                'designation' => $contactDesignation,
                'is_primary' => true,
            ]);
        }

        return response()->json($customer->load('assignedUser', 'territory', 'contacts'), 201);
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
            'invoices.payments',
        ])->findOrFail($id);

        $totalInvoiced = (float) $customer->invoices->sum('total');
        $totalPaid = (float) $customer->invoices->sum('amount_paid');
        $balanceDue = (float) $customer->invoices->sum('balance_due');

        $data = $customer->toArray();
        $data['ledger_summary'] = [
            'total_invoiced' => $totalInvoiced,
            'total_paid' => $totalPaid,
            'balance_due' => $balanceDue,
            'credit_available' => max(0, (float)$customer->credit_limit - $balanceDue),
        ];

        return response()->json($data);
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
            'state_code' => 'nullable|string|max:5',
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
            'shipping_state_code' => 'nullable|string|max:5',
            'shipping_country' => 'nullable|string|max:100',
            'shipping_postal_code' => 'nullable|string|max:20',
            'type' => 'nullable|in:enterprise,mid_market,small_business',
            'status' => 'nullable|in:active,inactive,prospect',
            'assigned_to' => 'nullable|exists:users,id',
            'territory_id' => 'nullable|exists:territories,id',
            'tags' => 'nullable|array',
            'notes' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|in:net_15,net_30,net_60,net_90,due_on_receipt',
            'currency' => 'nullable|string|size:3',
        ]);

        if (!empty($validated['gst_number'])) {
            $gstinData = \App\Services\GstService::validateGstin($validated['gst_number']);
            if ($gstinData['valid']) {
                $validated['state_code'] = $validated['state_code'] ?? $gstinData['state_code'];
                $validated['pan_number'] = $validated['pan_number'] ?? $gstinData['pan'];
            }
        }

        if (!empty($validated['address_state']) && empty($validated['state_code'])) {
            $validated['state_code'] = \App\Services\GstService::getCodeByState($validated['address_state']);
        }

        $customer->update($validated);
        return response()->json($customer->fresh(['assignedUser', 'territory', 'contacts']));
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
