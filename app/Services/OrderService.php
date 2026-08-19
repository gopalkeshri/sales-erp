<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

class OrderService
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function generateOrderNumber(): string
    {
        $prefix = 'ORD-' . date('Ym') . '-';
        $lastOrder = Order::where('order_number', 'like', $prefix . '%')->orderBy('id', 'desc')->first();
        $nextNumber = $lastOrder ? str_pad((int)substr($lastOrder->order_number, -4) + 1, 4, '0', STR_PAD_LEFT) : '0001';
        return $prefix . $nextNumber;
    }

    public function createOrder(array $data, array $items = [], ?User $user = null): Order
    {
        return DB::transaction(function () use ($data, $items, $user) {
            if (empty($data['order_number'])) {
                $data['order_number'] = $this->generateOrderNumber();
            }

            $data['created_by'] = $user ? $user->id : ($data['created_by'] ?? null);
            $order = Order::create($data);

            $subtotal = 0;
            $discountTotal = 0;
            $taxTotal = 0;

            foreach ($items as $item) {
                $qty = (int) ($item['quantity'] ?? 1);
                $unitPrice = (float) ($item['unit_price'] ?? 0);
                $discountPercent = (float) ($item['discount_percent'] ?? 0);
                $taxRate = (float) ($item['tax_rate'] ?? 0);

                $itemBase = $qty * $unitPrice;
                $itemDiscount = $itemBase * ($discountPercent / 100);
                $afterDiscount = $itemBase - $itemDiscount;
                $itemTax = $afterDiscount * ($taxRate / 100);
                $itemTotal = $afterDiscount + $itemTax;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'discount_percent' => $discountPercent,
                    'tax_rate' => $taxRate,
                    'total' => $itemTotal,
                    'shipped_quantity' => 0,
                ]);

                $subtotal += $itemBase;
                $discountTotal += $itemDiscount;
                $taxTotal += $itemTax;
            }

            $shippingCost = (float) ($data['shipping_cost'] ?? 0);
            $order->update([
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'tax_total' => $taxTotal,
                'shipping_cost' => $shippingCost,
                'total' => ($subtotal - $discountTotal) + $taxTotal + $shippingCost,
            ]);

            if ($user) {
                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'create',
                    'entity_type' => 'order',
                    'entity_id' => $order->id,
                    'new_values' => $order->toArray(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            return $order->load('items.product', 'customer', 'contact');
        });
    }

    public function updateStatus(Order $order, string $newStatus, ?int $warehouseId = null, ?User $user = null): Order
    {
        return DB::transaction(function () use ($order, $newStatus, $warehouseId, $user) {
            $oldStatus = $order->status;
            $order->update(['status' => $newStatus]);

            // If order transitioned to processing or shipped and warehouse is provided, deduct stock
            if (in_array($newStatus, ['processing', 'shipped']) && !in_array($oldStatus, ['processing', 'shipped', 'delivered']) && $warehouseId) {
                foreach ($order->items as $item) {
                    try {
                        $this->inventoryService->stockOut(
                            $item->product_id,
                            $warehouseId,
                            $item->quantity,
                            'order_fulfillment',
                            $order->id,
                            "Fulfilled for Order {$order->order_number}",
                            $user
                        );
                        $item->update(['shipped_quantity' => $item->quantity]);
                    } catch (\Exception $e) {
                        // Log inventory exception without breaking transaction if soft
                    }
                }
            }

            if ($newStatus === 'delivered' && empty($order->actual_delivery_date)) {
                $order->update(['actual_delivery_date' => now()->toDateString()]);
            }

            return $order->fresh(['items.product', 'customer', 'contact', 'invoices']);
        });
    }

    public function generateInvoice(Order $order, ?User $user = null): Invoice
    {
        return DB::transaction(function () use ($order, $user) {
            $invoicePrefix = 'INV-' . date('Ym') . '-';
            $lastInvoice = Invoice::where('invoice_number', 'like', $invoicePrefix . '%')->orderBy('id', 'desc')->first();
            $nextNum = $lastInvoice ? str_pad((int)substr($lastInvoice->invoice_number, -4) + 1, 4, '0', STR_PAD_LEFT) : '0001';
            $invoiceNumber = $invoicePrefix . $nextNum;

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'contact_id' => $order->contact_id,
                'status' => 'draft',
                'type' => 'sales',
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'subtotal' => $order->subtotal,
                'discount_total' => $order->discount_total,
                'tax_total' => $order->tax_total,
                'total' => $order->total,
                'amount_paid' => 0.00,
                'balance_due' => $order->total,
                'currency' => $order->currency,
                'payment_terms' => $order->payment_terms ?: 'net_30',
                'assigned_to' => $order->assigned_to,
                'territory_id' => $order->territory_id,
                'notes' => $order->notes,
                'created_by' => $user ? $user->id : $order->created_by,
            ]);

            foreach ($order->items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item->product_id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount_percent' => $item->discount_percent,
                    'tax_rate' => $item->tax_rate,
                    'total' => $item->total,
                ]);
            }

            return $invoice->load('items.product', 'customer', 'contact', 'order');
        });
    }
}
