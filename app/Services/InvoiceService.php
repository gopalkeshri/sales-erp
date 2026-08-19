<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . date('Ym') . '-';
        $lastInvoice = Invoice::where('invoice_number', 'like', $prefix . '%')->orderBy('id', 'desc')->first();
        $nextNumber = $lastInvoice ? str_pad((int)substr($lastInvoice->invoice_number, -4) + 1, 4, '0', STR_PAD_LEFT) : '0001';
        return $prefix . $nextNumber;
    }

    public function createInvoice(array $data, array $items = [], ?User $user = null): Invoice
    {
        return DB::transaction(function () use ($data, $items, $user) {
            if (empty($data['invoice_number'])) {
                $data['invoice_number'] = $this->generateInvoiceNumber();
            }

            if (empty($data['invoice_date'])) {
                $data['invoice_date'] = now()->toDateString();
            }
            if (empty($data['due_date'])) {
                $data['due_date'] = now()->addDays(30)->toDateString();
            }

            $data['created_by'] = $user ? $user->id : ($data['created_by'] ?? null);
            $invoice = Invoice::create($data);

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

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
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

            $total = ($subtotal - $discountTotal) + $taxTotal;

            $invoice->update([
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'tax_total' => $taxTotal,
                'total' => $total,
                'balance_due' => $total,
            ]);

            if ($user) {
                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'create',
                    'entity_type' => 'invoice',
                    'entity_id' => $invoice->id,
                    'new_values' => $invoice->toArray(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            return $invoice->load('items.product', 'customer', 'contact');
        });
    }

    public function recordPayment(Invoice $invoice, array $paymentData, ?User $user = null): Payment
    {
        return DB::transaction(function () use ($invoice, $paymentData, $user) {
            $paymentData['invoice_id'] = $invoice->id;
            $paymentData['created_by'] = $user ? $user->id : null;
            if (empty($paymentData['payment_date'])) {
                $paymentData['payment_date'] = now()->toDateString();
            }

            $payment = Payment::create($paymentData);

            $invoice->recalculatePaymentStatus();

            if ($user) {
                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'create',
                    'entity_type' => 'payment',
                    'entity_id' => $payment->id,
                    'new_values' => $payment->toArray(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            return $payment->load('invoice.customer');
        });
    }

    public function getOverdueInvoices()
    {
        return Invoice::where('due_date', '<', now()->toDateString())
            ->where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->with(['customer', 'assignedUser'])
            ->get();
    }
}
