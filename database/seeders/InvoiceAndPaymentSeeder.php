<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class InvoiceAndPaymentSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::with(['items.product', 'customer'])->get();
        $admin = User::where('role', 'admin')->first();

        // 1. Invoices
        foreach ($orders as $order) {
            $status = ($order->status === 'delivered') ? 'paid' : 'sent';
            $invoiceDate = now()->subDays(12)->toDateString();
            $dueDate = now()->addDays(18)->toDateString();

            $invoice = Invoice::create([
                'invoice_number' => 'INV-202608-00' . $order->id,
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'contact_id' => $order->contact_id,
                'status' => $status,
                'type' => 'sales',
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'subtotal' => $order->subtotal,
                'discount_total' => $order->discount_total,
                'tax_total' => $order->tax_total,
                'total' => $order->total,
                'amount_paid' => ($status === 'paid') ? $order->total : 0.00,
                'balance_due' => ($status === 'paid') ? 0.00 : $order->total,
                'currency' => 'USD',
                'payment_terms' => $order->payment_terms,
                'assigned_to' => $order->assigned_to,
                'territory_id' => $order->territory_id,
                'created_by' => $order->created_by,
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

            if ($status === 'paid') {
                Payment::create([
                    'invoice_id' => $invoice->id,
                    'payment_date' => now()->subDays(2)->toDateString(),
                    'amount' => $invoice->total,
                    'payment_method' => 'bank_transfer',
                    'reference_number' => 'WIRE-US-998271',
                    'notes' => 'Full balance wire transfer received and verified.',
                    'created_by' => $admin ? $admin->id : null,
                ]);
            }
        }
    }
}
