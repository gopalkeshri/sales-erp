<?php

namespace App\Services;

use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuoteService
{
    public function generateQuoteNumber(): string
    {
        $prefix = 'QT-' . date('Ym') . '-';
        $lastQuote = Quote::where('quote_number', 'like', $prefix . '%')->orderBy('id', 'desc')->first();

        if ($lastQuote) {
            $lastNumber = (int) substr($lastQuote->quote_number, -4);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return $prefix . $nextNumber;
    }

    public function createQuote(array $data, array $items = [], ?User $user = null): Quote
    {
        return DB::transaction(function () use ($data, $items, $user) {
            if (empty($data['quote_number'])) {
                $data['quote_number'] = $this->generateQuoteNumber();
            }

            $data['created_by'] = $user ? $user->id : ($data['created_by'] ?? null);
            $quote = Quote::create($data);

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

                QuoteItem::create([
                    'quote_id' => $quote->id,
                    'product_id' => $item['product_id'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'discount_percent' => $discountPercent,
                    'tax_rate' => $taxRate,
                    'total' => $itemTotal,
                ]);

                $subtotal += $itemBase;
                $discountTotal += $itemDiscount;
                $taxTotal += $itemTax;
            }

            $quote->update([
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'tax_total' => $taxTotal,
                'total' => ($subtotal - $discountTotal) + $taxTotal,
            ]);

            if ($user) {
                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'create',
                    'entity_type' => 'quote',
                    'entity_id' => $quote->id,
                    'new_values' => $quote->toArray(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            return $quote->load('items.product', 'customer', 'contact', 'opportunity');
        });
    }

    public function convertToOrder(Quote $quote, ?User $user = null): Order
    {
        return DB::transaction(function () use ($quote, $user) {
            $customer = $quote->customer;

            // Generate Order Number
            $orderPrefix = 'ORD-' . date('Ym') . '-';
            $lastOrder = Order::where('order_number', 'like', $orderPrefix . '%')->orderBy('id', 'desc')->first();
            $nextNum = $lastOrder ? str_pad((int)substr($lastOrder->order_number, -4) + 1, 4, '0', STR_PAD_LEFT) : '0001';
            $orderNumber = $orderPrefix . $nextNum;

            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $quote->customer_id,
                'contact_id' => $quote->contact_id,
                'opportunity_id' => $quote->opportunity_id,
                'quote_id' => $quote->id,
                'status' => 'confirmed',
                'billing_street' => $customer ? $customer->billing_street : null,
                'billing_city' => $customer ? $customer->billing_city : null,
                'billing_state' => $customer ? $customer->billing_state : null,
                'billing_country' => $customer ? $customer->billing_country : null,
                'billing_postal_code' => $customer ? $customer->billing_postal_code : null,
                'shipping_street' => $customer ? $customer->shipping_street : null,
                'shipping_city' => $customer ? $customer->shipping_city : null,
                'shipping_state' => $customer ? $customer->shipping_state : null,
                'shipping_country' => $customer ? $customer->shipping_country : null,
                'shipping_postal_code' => $customer ? $customer->shipping_postal_code : null,
                'subtotal' => $quote->subtotal,
                'discount_total' => $quote->discount_total,
                'tax_total' => $quote->tax_total,
                'shipping_cost' => 0.00,
                'total' => $quote->total,
                'currency' => $quote->currency,
                'payment_terms' => $customer ? $customer->payment_terms : 'due_on_receipt',
                'assigned_to' => $quote->assigned_to,
                'territory_id' => $quote->territory_id,
                'notes' => $quote->notes,
                'created_by' => $user ? $user->id : $quote->created_by,
            ]);

            foreach ($quote->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount_percent' => $item->discount_percent,
                    'tax_rate' => $item->tax_rate,
                    'total' => $item->total,
                    'shipped_quantity' => 0,
                ]);
            }

            $quote->update([
                'status' => 'converted',
                'converted_to_order_id' => $order->id,
                'accepted_at' => now(),
            ]);

            return $order->load('items.product', 'customer', 'contact');
        });
    }
}
