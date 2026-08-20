<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceMultiPagePrintTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('role', 'admin')->first()
            ?? User::create([
                'name' => 'System Administrator',
                'email' => 'admin_print@saleserp.com',
                'password' => bcrypt('password123'),
                'role' => 'admin',
                'is_active' => true,
            ]);

        $this->customer = Customer::firstOrCreate(
            ['company_name' => 'Bharat Tech Solutions Ltd.'],
            [
                'customer_type' => 'enterprise',
                'industry' => 'Information Technology',
                'gst_number' => '27AAACB9999P1Z1',
                'pan_number' => 'AAACB9999P',
                'state_code' => '27',
                'billing_street' => 'Floor 8, DLF Cyber City, Phase III',
                'billing_city' => 'Gurugram',
                'billing_state' => 'Haryana',
                'address_street' => 'Floor 8, DLF Cyber City, Phase III',
                'address_city' => 'Gurugram',
                'address_state' => 'Haryana',
                'status' => 'active',
            ]
        );

        $this->actingAs($this->admin);
    }

    public function test_invoice_with_25_products_renders_multi_page_pdf_data_correctly(): void
    {
        // 1. Create an invoice with 25 product items
        $invoice = Invoice::create([
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-2026-MULTI-001',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => 'sent',
            'gst_type' => 'intra_state',
            'state_code' => '27',
            'place_of_supply' => 'Maharashtra',
            'subtotal' => 0,
            'discount_total' => 0,
            'tax_total' => 0,
            'cgst_total' => 0,
            'sgst_total' => 0,
            'igst_total' => 0,
            'total' => 0,
            'amount_paid' => 0,
            'balance_due' => 0,
        ]);

        $subtotal = 0;
        $taxTotal = 0;

        for ($i = 1; $i <= 25; $i++) {
            $product = Product::create([
                'name' => "Industrial Hardware Component Item #{$i}",
                'sku' => "SKU-CMP-{$i}",
                'hsn_code' => $i % 2 === 0 ? '84713010' : '85044090',
                'unit_price' => 1000.00 * $i,
                'tax_rate' => 18.00,
                'unit' => 'pcs',
                'category' => 'hardware',
                'is_active' => true,
            ]);

            $qty = 2;
            $unitPrice = 1000.00 * $i;
            $taxable = $qty * $unitPrice;
            $cgst = $taxable * 0.09;
            $sgst = $taxable * 0.09;
            $itemTotal = $taxable + $cgst + $sgst;

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $product->id,
                'description' => "Precision Component High Grade Line Item #{$i}",
                'hsn_code' => $product->hsn_code,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'discount_percent' => 0,
                'taxable_value' => $taxable,
                'tax_rate' => 18.00,
                'cgst_rate' => 9.00,
                'cgst_amount' => $cgst,
                'sgst_rate' => 9.00,
                'sgst_amount' => $sgst,
                'igst_rate' => 0,
                'igst_amount' => 0,
                'total' => $itemTotal,
            ]);

            $subtotal += $taxable;
            $taxTotal += ($cgst + $sgst);
        }

        $invoice->update([
            'subtotal' => $subtotal,
            'tax_total' => $taxTotal,
            'cgst_total' => $taxTotal / 2,
            'sgst_total' => $taxTotal / 2,
            'total' => $subtotal + $taxTotal,
            'balance_due' => $subtotal + $taxTotal,
        ]);

        // 2. Test API endpoint returns all 25 items and HSN groupings
        $apiResponse = $this->getJson("/api/invoices/{$invoice->id}/pdf");
        $apiResponse->assertStatus(200);
        $apiResponse->assertJsonStructure([
            'invoice' => ['items'],
            'amount_in_words',
            'hsn_summary',
            'company' => ['name', 'gstin', 'pan', 'bank_name', 'bank_account_no', 'bank_ifsc', 'upi_id'],
            'buyer' => ['state', 'state_code', 'is_intra_state'],
        ]);

        $this->assertCount(25, $apiResponse->json('invoice.items'));
        $this->assertCount(2, $apiResponse->json('hsn_summary'));
        $this->assertNotEmpty($apiResponse->json('amount_in_words'));

        // 3. Test Standalone Print Blade Route /invoices/{id}/print
        $printResponse = $this->get("/invoices/{$invoice->id}/print");
        $printResponse->assertStatus(200);
        $printResponse->assertSee('TAX INVOICE');
        $printResponse->assertSee('INV-2026-MULTI-001');
        $printResponse->assertSee('Bharat Tech Solutions Ltd.');
        $printResponse->assertSee('25 Line Items');
        $printResponse->assertSee('Precision Component High Grade Line Item #1');
        $printResponse->assertSee('Precision Component High Grade Line Item #25');
        $printResponse->assertSee('HSN / SAC Tax Breakdown:');
        $printResponse->assertSee('Electronic Bank Settlement & UPI:', false);
        $printResponse->assertSee('Authorized Signatory');
    }

    public function test_inter_state_multi_product_invoice_shows_igst(): void
    {
        $serviceProduct = Product::create([
            'name' => 'Enterprise Cloud Hosting Service',
            'sku' => 'SRV-CLOUD-01',
            'hsn_code' => '998315',
            'unit_price' => 50000.00,
            'tax_rate' => 18.00,
            'unit' => 'month',
            'category' => 'service',
            'is_active' => true,
        ]);

        $invoice = Invoice::create([
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-2026-IGST-002',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'status' => 'sent',
            'gst_type' => 'inter_state',
            'state_code' => '06',
            'place_of_supply' => 'Haryana',
            'subtotal' => 50000,
            'tax_total' => 9000,
            'cgst_total' => 0,
            'sgst_total' => 0,
            'igst_total' => 9000,
            'total' => 59000,
            'balance_due' => 59000,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_id' => $serviceProduct->id,
            'description' => 'Enterprise Cloud Hosting Service',
            'hsn_code' => '998315',
            'quantity' => 1,
            'unit_price' => 50000,
            'discount_percent' => 0,
            'taxable_value' => 50000,
            'tax_rate' => 18.00,
            'cgst_rate' => 0,
            'cgst_amount' => 0,
            'sgst_rate' => 0,
            'sgst_amount' => 0,
            'igst_rate' => 18.00,
            'igst_amount' => 9000,
            'total' => 59000,
        ]);

        $response = $this->get("/invoices/{$invoice->id}/print");
        $response->assertStatus(200);
        $response->assertSee('Inter-State (IGST)');
        $response->assertSee('998315');
        $response->assertSee('59,000.00');
    }
}
