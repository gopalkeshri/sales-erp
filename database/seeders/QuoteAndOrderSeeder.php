<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class QuoteAndOrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::with('contacts')->get();
        $opportunities = Opportunity::all();
        $products = Product::all()->keyBy('sku');
        $reps = User::where('role', 'sales_rep')->get();

        // 1. Quotes
        $quotesData = [
            [
                'quote_number' => 'QT-202608-0001',
                'customer_index' => 0,
                'status' => 'sent',
                'valid_until' => now()->addDays(20)->toDateString(),
                'items' => [
                    ['sku' => 'SW-ERP-ENT', 'qty' => 2, 'unit_price' => 12000.00, 'discount' => 5.0, 'tax' => 10.0],
                    ['sku' => 'SW-SEC-SHIELD', 'qty' => 4, 'unit_price' => 4500.00, 'discount' => 0.0, 'tax' => 10.0],
                ],
            ],
            [
                'quote_number' => 'QT-202608-0002',
                'customer_index' => 1,
                'status' => 'accepted',
                'valid_until' => now()->addDays(15)->toDateString(),
                'items' => [
                    ['sku' => 'HW-SRV-R900', 'qty' => 2, 'unit_price' => 28500.00, 'discount' => 10.0, 'tax' => 18.0],
                    ['sku' => 'HW-NET-SW48G', 'qty' => 2, 'unit_price' => 8900.00, 'discount' => 0.0, 'tax' => 18.0],
                ],
            ],
            [
                'quote_number' => 'QT-202608-0003',
                'customer_index' => 2,
                'status' => 'converted',
                'valid_until' => now()->subDays(5)->toDateString(),
                'items' => [
                    ['sku' => 'HW-WRK-PRO16', 'qty' => 10, 'unit_price' => 3200.00, 'discount' => 5.0, 'tax' => 18.0],
                    ['sku' => 'SV-IMPL-PACK', 'qty' => 1, 'unit_price' => 7500.00, 'discount' => 0.0, 'tax' => 10.0],
                ],
            ],
        ];

        $createdQuotes = [];
        foreach ($quotesData as $qData) {
            $customer = $customers[$qData['customer_index']] ?? $customers->first();
            $contact = $customer->primaryContact ?? $customer->contacts->first();
            $rep = $customer->assignedUser ?? $reps->first();

            $quote = Quote::create([
                'quote_number' => $qData['quote_number'],
                'customer_id' => $customer->id,
                'contact_id' => $contact ? $contact->id : null,
                'opportunity_id' => $opportunities->first()->id ?? null,
                'status' => $qData['status'],
                'valid_until' => $qData['valid_until'],
                'currency' => 'USD',
                'terms_conditions' => 'Standard 30 days validity. Software warranties subject to SLA.',
                'assigned_to' => $rep ? $rep->id : null,
                'territory_id' => $customer->territory_id,
                'created_by' => $rep ? $rep->id : null,
            ]);

            $subtotal = 0;
            $discountTotal = 0;
            $taxTotal = 0;

            foreach ($qData['items'] as $item) {
                $p = $products[$item['sku']] ?? null;
                if ($p) {
                    $base = $item['qty'] * $item['unit_price'];
                    $disc = $base * ($item['discount'] / 100);
                    $afterDisc = $base - $disc;
                    $tax = $afterDisc * ($item['tax'] / 100);
                    $total = $afterDisc + $tax;

                    QuoteItem::create([
                        'quote_id' => $quote->id,
                        'product_id' => $p->id,
                        'description' => $p->name,
                        'quantity' => $item['qty'],
                        'unit_price' => $item['unit_price'],
                        'discount_percent' => $item['discount'],
                        'tax_rate' => $item['tax'],
                        'total' => $total,
                    ]);

                    $subtotal += $base;
                    $discountTotal += $disc;
                    $taxTotal += $tax;
                }
            }

            $quote->update([
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'tax_total' => $taxTotal,
                'total' => ($subtotal - $discountTotal) + $taxTotal,
            ]);

            $createdQuotes[] = $quote;
        }

        // 2. Orders
        $ordersData = [
            [
                'order_number' => 'ORD-202608-0001',
                'customer_index' => 2,
                'quote_id' => $createdQuotes[2]->id ?? null,
                'status' => 'delivered',
                'expected_delivery_date' => now()->subDays(5)->toDateString(),
                'actual_delivery_date' => now()->subDays(3)->toDateString(),
                'items' => [
                    ['sku' => 'HW-WRK-PRO16', 'qty' => 10, 'unit_price' => 3200.00, 'discount' => 5.0, 'tax' => 18.0],
                    ['sku' => 'SV-IMPL-PACK', 'qty' => 1, 'unit_price' => 7500.00, 'discount' => 0.0, 'tax' => 10.0],
                ],
            ],
            [
                'order_number' => 'ORD-202608-0002',
                'customer_index' => 0,
                'quote_id' => null,
                'status' => 'processing',
                'expected_delivery_date' => now()->addDays(5)->toDateString(),
                'items' => [
                    ['sku' => 'HW-SRV-R900', 'qty' => 1, 'unit_price' => 28500.00, 'discount' => 0.0, 'tax' => 18.0],
                    ['sku' => 'HW-NET-SW48G', 'qty' => 1, 'unit_price' => 8900.00, 'discount' => 0.0, 'tax' => 18.0],
                ],
            ],
        ];

        foreach ($ordersData as $oData) {
            $customer = $customers[$oData['customer_index']] ?? $customers->first();
            $contact = $customer->primaryContact ?? $customer->contacts->first();
            $rep = $customer->assignedUser ?? $reps->first();

            $order = Order::create([
                'order_number' => $oData['order_number'],
                'customer_id' => $customer->id,
                'contact_id' => $contact ? $contact->id : null,
                'quote_id' => $oData['quote_id'],
                'status' => $oData['status'],
                'billing_street' => $customer->billing_street,
                'billing_city' => $customer->billing_city,
                'billing_state' => $customer->billing_state,
                'billing_country' => $customer->billing_country,
                'billing_postal_code' => $customer->billing_postal_code,
                'shipping_street' => $customer->shipping_street,
                'shipping_city' => $customer->shipping_city,
                'shipping_state' => $customer->shipping_state,
                'shipping_country' => $customer->shipping_country,
                'shipping_postal_code' => $customer->shipping_postal_code,
                'currency' => 'USD',
                'payment_terms' => $customer->payment_terms,
                'expected_delivery_date' => $oData['expected_delivery_date'],
                'actual_delivery_date' => $oData['actual_delivery_date'] ?? null,
                'assigned_to' => $rep ? $rep->id : null,
                'territory_id' => $customer->territory_id,
                'created_by' => $rep ? $rep->id : null,
            ]);

            if ($oData['quote_id']) {
                Quote::where('id', $oData['quote_id'])->update(['converted_to_order_id' => $order->id]);
            }

            $subtotal = 0;
            $discountTotal = 0;
            $taxTotal = 0;

            foreach ($oData['items'] as $item) {
                $p = $products[$item['sku']] ?? null;
                if ($p) {
                    $base = $item['qty'] * $item['unit_price'];
                    $disc = $base * ($item['discount'] / 100);
                    $afterDisc = $base - $disc;
                    $tax = $afterDisc * ($item['tax'] / 100);
                    $total = $afterDisc + $tax;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $p->id,
                        'description' => $p->name,
                        'quantity' => $item['qty'],
                        'unit_price' => $item['unit_price'],
                        'discount_percent' => $item['discount'],
                        'tax_rate' => $item['tax'],
                        'total' => $total,
                        'shipped_quantity' => ($oData['status'] === 'delivered') ? $item['qty'] : 0,
                    ]);

                    $subtotal += $base;
                    $discountTotal += $disc;
                    $taxTotal += $tax;
                }
            }

            $order->update([
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'tax_total' => $taxTotal,
                'total' => ($subtotal - $discountTotal) + $taxTotal,
            ]);
        }
    }
}
