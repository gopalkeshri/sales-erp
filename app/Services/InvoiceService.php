<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function generateInvoiceNumber(): string
    {
        $prefix = Setting::get('invoice_prefix', 'INV-') . date('Ym') . '-';
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

            $customer = Customer::findOrFail($data['customer_id']);

            // Determine State Code of Supplier and Buyer
            $supplierStateCode = Setting::get('company_state_code', '27');
            $buyerStateCode = $data['state_code'] ?? $customer->state_code ?? null;

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

            $invoice = Invoice::create($data);

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

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
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
                $cgstTotal += $gstCalc['cgst_amount'];
                $sgstTotal += $gstCalc['sgst_amount'];
                $igstTotal += $gstCalc['igst_amount'];
                $taxTotal += $gstCalc['total_tax'];
            }

            $grandTotal = round(($subtotal - $discountTotal) + $taxTotal, 2);

            $invoice->update([
                'subtotal' => round($subtotal, 2),
                'discount_total' => round($discountTotal, 2),
                'cgst_total' => round($cgstTotal, 2),
                'sgst_total' => round($sgstTotal, 2),
                'igst_total' => round($igstTotal, 2),
                'tax_total' => round($taxTotal, 2),
                'gst_type' => $gstType,
                'total' => $grandTotal,
                'balance_due' => $grandTotal,
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
