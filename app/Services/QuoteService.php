<?php

namespace App\Services;

use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuoteService
{
    public function generateQuoteNumber(): string
    {
        $prefix = Setting::get('quote_prefix', 'QT-') . date('Ym') . '-';
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

            $customer = Customer::findOrFail($data['customer_id']);

            // Determine State Code of Supplier and Buyer
            $supplierStateCode = Setting::get('company_state_code', '27');
            $buyerStateCode = $data['state_code'] ?? ($customer->state_code ?? null);

            if (empty($buyerStateCode) && !empty($customer->gst_number)) {
                $gstinData = GstService::validateGstin($customer->gst_number);
                if ($gstinData['valid']) {
                    $buyerStateCode = $gstinData['state_code'];
                }
            }

            if (empty($buyerStateCode) && !empty($customer->address_state)) {
                $buyerStateCode = GstService::getCodeByState($customer->address_state);
            }

            $buyerStateCode = $buyerStateCode ?: $supplierStateCode;

            $data['state_code'] = $buyerStateCode;
            $data['place_of_supply'] = $data['place_of_supply'] ?? ($customer->address_state ?: GstService::getStateByCode($buyerStateCode));
            $data['currency'] = $data['currency'] ?? Setting::get('default_currency', 'INR');
            $data['created_by'] = $user ? $user->id : ($data['created_by'] ?? null);

            $quote = Quote::create($data);

            $subtotal = 0;
            $discountTotal = 0;
            $taxTotal = 0;
            $cgstTotal = 0;
            $sgstTotal = 0;
            $igstTotal = 0;
            $gstType = 'intra_state';

            foreach ($items as $item) {
                $product = !empty($item['product_id']) ? Product::find($item['product_id']) : null;
                $qty = (int) ($item['quantity'] ?? 1);
                $unitPrice = (float) ($item['unit_price'] ?? ($product ? $product->unit_price : 0));
                $discountPercent = (float) ($item['discount_percent'] ?? 0);
                $taxRate = (float) ($item['tax_rate'] ?? ($product ? $product->tax_rate : 18.00));
                $hsnCode = $item['hsn_code'] ?? ($product ? $product->hsn_code : null);

                $itemBase = $qty * $unitPrice;
                $itemDiscount = round($itemBase * ($discountPercent / 100), 2);
                $taxableValue = round($itemBase - $itemDiscount, 2);

                // Calculate GST Split
                $gstCalc = GstService::calculateGst($taxableValue, $taxRate, $supplierStateCode, $buyerStateCode);
                $gstType = $gstCalc['gst_type'];

                $itemTotal = round($taxableValue + $gstCalc['total_tax'], 2);

                QuoteItem::create([
                    'quote_id' => $quote->id,
                    'product_id' => $item['product_id'],
                    'description' => $item['description'] ?? ($product ? $product->name : null),
                    'hsn_code' => $hsnCode,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'discount_percent' => $discountPercent,
                    'taxable_value' => $taxableValue,
                    'tax_rate' => $taxRate,
                    'cgst_rate' => $gstCalc['cgst_rate'],
                    'cgst_amount' => $gstCalc['cgst_amount'],
                    'sgst_rate' => $gstCalc['sgst_rate'],
                    'sgst_amount' => $gstCalc['sgst_amount'],
                    'igst_rate' => $gstCalc['igst_rate'],
                    'igst_amount' => $gstCalc['igst_amount'],
                    'total' => $itemTotal,
                ]);

                $subtotal += $itemBase;
                $discountTotal += $itemDiscount;
                $taxTotal += $gstCalc['total_tax'];
                $cgstTotal += $gstCalc['cgst_amount'];
                $sgstTotal += $gstCalc['sgst_amount'];
                $igstTotal += $gstCalc['igst_amount'];
            }

            $quote->update([
                'gst_type' => $gstType,
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'tax_total' => $taxTotal,
                'cgst_total' => $cgstTotal,
                'sgst_total' => $sgstTotal,
                'igst_total' => $igstTotal,
                'total' => round(($subtotal - $discountTotal) + $taxTotal, 2),
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
            $orderPrefix = Setting::get('order_prefix', 'SO-') . date('Ym') . '-';
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
                'billing_street' => $customer ? ($customer->billing_street ?: $customer->address_street) : null,
                'billing_city' => $customer ? ($customer->billing_city ?: $customer->address_city) : null,
                'billing_state' => $customer ? ($customer->billing_state ?: $customer->address_state) : null,
                'billing_country' => $customer ? ($customer->billing_country ?: $customer->address_country) : null,
                'billing_postal_code' => $customer ? ($customer->billing_postal_code ?: $customer->postal_code) : null,
                'shipping_street' => $customer ? ($customer->shipping_street ?: $customer->address_street) : null,
                'shipping_city' => $customer ? ($customer->shipping_city ?: $customer->address_city) : null,
                'shipping_state' => $customer ? ($customer->shipping_state ?: $customer->address_state) : null,
                'shipping_country' => $customer ? ($customer->shipping_country ?: $customer->address_country) : null,
                'shipping_postal_code' => $customer ? ($customer->shipping_postal_code ?: $customer->postal_code) : null,
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
