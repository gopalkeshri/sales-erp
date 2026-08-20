<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\GstService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GstInvoiceAndProductTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected string $token;
    protected Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::where('email', 'admin@saleserp.com')->first();
        $this->token = $this->user->createToken('gst-test')->plainTextToken;

        $this->warehouse = Warehouse::firstOrCreate([
            'name' => 'Mumbai Central Fulfillment Hub',
            'code' => 'WH-MUM-01',
        ], [
            'address' => 'Bandra Kurla Complex, Mumbai',
            'is_active' => true,
        ]);

        Setting::set('company_name', 'Bharat Enterprise Cloud ERP', 'string');
        Setting::set('company_state', 'Maharashtra', 'string');
        Setting::set('company_state_code', '27', 'string');
        Setting::set('company_gstin', '27AABCB1234F1Z8', 'string');
        Setting::set('company_pan', 'AABCB1234F', 'string');
        Setting::set('currency_symbol', '₹', 'string');
    }

    public function test_gst_service_validates_and_extracts_state_and_pan(): void
    {
        $gstService = new GstService();

        $validGstin = '27AAACA1234F1Z5'; // Maharashtra
        $res = $gstService->validateGstin($validGstin);

        $this->assertTrue($res['valid']);
        $this->assertEquals('27', $res['state_code']);
        $this->assertEquals('Maharashtra', $res['state_name']);
        $this->assertEquals('AAACA1234F', $res['pan']);

        $invalidGstin = '123INVALID';
        $resInvalid = $gstService->validateGstin($invalidGstin);
        $this->assertFalse($resInvalid['valid']);
    }

    public function test_gst_service_converts_number_to_indian_rupees_words(): void
    {
        $gstService = new GstService();

        $words = $gstService->numberToIndianWords(150250.75);
        $this->assertStringContainsString('One Lakh Fifty Thousand Two Hundred Fifty', $words);
        $this->assertStringContainsString('Seventy Five Paise', $words);
    }

    public function test_product_creation_with_gst_and_initial_inventory(): void
    {
        $payload = [
            'sku' => 'IND-SERVER-101',
            'name' => 'High Performance AI Server Unit',
            'description' => 'Indian datacenter certified',
            'category' => 'Hardware',
            'type' => 'product',
            'unit' => 'Pcs',
            'hsn_code' => '8471',
            'tax_rate' => 18.00,
            'unit_price' => 125000.00,
            'cost_price' => 95000.00,
            'min_stock_level' => 3,
            'initial_quantity' => 10,
            'warehouse_id' => $this->warehouse->id,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/products', $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('sku', 'IND-SERVER-101');
        $response->assertJsonPath('hsn_code', '8471');
        $response->assertJsonPath('tax_rate', '18.00');

        $product = Product::where('sku', 'IND-SERVER-101')->first();
        $this->assertNotNull($product);
        $this->assertEquals(10, $product->total_stock);
    }

    public function test_customer_creation_auto_extracts_state_code_and_pan(): void
    {
        $payload = [
            'company_name' => 'Karnataka Tech Solutions Pvt Ltd',
            'trade_name' => 'KT-Solutions',
            'gst_number' => '29AAACK9999M1Z3', // Karnataka State 29
            'type' => 'enterprise',
            'industry' => 'Cloud & SaaS',
            'credit_limit' => 800000.00,
            'payment_terms' => 'net_30',
            'contact_name' => 'Suresh Kumar',
            'contact_email' => 'suresh@kt-solutions.in',
            'contact_phone' => '+91 98800 12345',
            'billing_street' => 'Outer Ring Road, Bellandur',
            'billing_city' => 'Bengaluru',
            'billing_postal_code' => '560103',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/customers', $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('state_code', '29');
        $response->assertJsonPath('pan_number', 'AAACK9999M');

        $customer = Customer::where('gst_number', '29AAACK9999M1Z3')->first();
        $this->assertNotNull($customer);
        $this->assertEquals('29', $customer->state_code);
        $this->assertCount(1, $customer->contacts);
    }

    public function test_intra_state_gst_invoice_splits_into_cgst_and_sgst(): void
    {
        // Buyer in Maharashtra (27) - Intra-State
        $customer = Customer::create([
            'company_name' => 'Mumbai Digital Labs',
            'state_code' => '27',
            'address_state' => 'Maharashtra',
            'gst_number' => '27AAACD5555N1Z1',
            'type' => 'enterprise',
            'status' => 'active',
        ]);

        $product = Product::create([
            'sku' => 'SRV-CONSULT',
            'name' => 'Enterprise Cloud Consulting',
            'type' => 'service',
            'hsn_code' => '9983',
            'tax_rate' => 18.00,
            'unit_price' => 100000.00,
            'is_active' => true,
        ]);

        $payload = [
            'customer_id' => $customer->id,
            'state_code' => '27',
            'type' => 'sales',
            'invoice_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'sent',
            'items' => [
                [
                    'product_id' => $product->id,
                    'hsn_code' => '9983',
                    'quantity' => 1,
                    'unit_price' => 100000.00,
                    'discount_percent' => 10.0, // Taxable = 90,000
                    'tax_rate' => 18.00,        // 9% CGST (8,100) + 9% SGST (8,100) = 16,200 Tax
                ]
            ]
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/invoices', $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('gst_type', 'intra_state');
        $response->assertJsonPath('state_code', '27');
        $this->assertEquals(8100.0, (float)$response->json('cgst_total'));
        $this->assertEquals(8100.0, (float)$response->json('sgst_total'));
        $this->assertEquals(0.0, (float)$response->json('igst_total'));
        $this->assertEquals(106200.0, (float)$response->json('total'));
    }

    public function test_inter_state_gst_invoice_applies_100_percent_igst(): void
    {
        // Buyer in Karnataka (29) - Inter-State (Supplier is 27)
        $customer = Customer::create([
            'company_name' => 'Bengaluru Tech Park Hub',
            'state_code' => '29',
            'address_state' => 'Karnataka',
            'gst_number' => '29AAACK9999M1Z3',
            'type' => 'enterprise',
            'status' => 'active',
        ]);

        $product = Product::create([
            'sku' => 'HW-MODEM-10',
            'name' => 'Enterprise 5G Gateway',
            'type' => 'product',
            'hsn_code' => '8517',
            'tax_rate' => 18.00,
            'unit_price' => 50000.00,
            'is_active' => true,
        ]);

        $payload = [
            'customer_id' => $customer->id,
            'state_code' => '29',
            'type' => 'sales',
            'invoice_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'sent',
            'items' => [
                [
                    'product_id' => $product->id,
                    'hsn_code' => '8517',
                    'quantity' => 2,
                    'unit_price' => 50000.00,
                    'discount_percent' => 0.0, // Taxable = 100,000
                    'tax_rate' => 18.00,        // 18% IGST = 18,000
                ]
            ]
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/invoices', $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('gst_type', 'inter_state');
        $response->assertJsonPath('state_code', '29');
        $this->assertEquals(0.0, (float)$response->json('cgst_total'));
        $this->assertEquals(0.0, (float)$response->json('sgst_total'));
        $this->assertEquals(18000.0, (float)$response->json('igst_total'));
        $this->assertEquals(118000.0, (float)$response->json('total'));
    }

    public function test_invoice_pdf_endpoint_returns_hsn_summary_and_amount_in_words(): void
    {
        $customer = Customer::create([
            'company_name' => 'Tata Technologies Ltd',
            'state_code' => '27',
            'address_state' => 'Maharashtra',
            'gst_number' => '27AAACT9999P1Z2',
            'type' => 'enterprise',
            'status' => 'active',
        ]);

        $product = Product::create([
            'sku' => 'SW-LIC-PRO',
            'name' => 'Enterprise License Annual',
            'type' => 'service',
            'hsn_code' => '9973',
            'tax_rate' => 18.00,
            'unit_price' => 200000.00,
            'is_active' => true,
        ]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV-2026-TEST01',
            'customer_id' => $customer->id,
            'user_id' => $this->user->id,
            'type' => 'sales',
            'status' => 'sent',
            'invoice_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'subtotal' => 200000.00,
            'discount_total' => 0.0,
            'tax_total' => 36000.00,
            'total' => 236000.00,
            'amount_paid' => 0.0,
            'balance_due' => 236000.00,
            'place_of_supply' => 'Maharashtra',
            'state_code' => '27',
            'gst_type' => 'intra_state',
            'cgst_total' => 18000.00,
            'sgst_total' => 18000.00,
            'igst_total' => 0.0,
        ]);

        $invoice->items()->create([
            'product_id' => $product->id,
            'hsn_code' => '9973',
            'description' => 'Enterprise License Annual',
            'quantity' => 1,
            'unit_price' => 200000.00,
            'discount_percent' => 0.0,
            'taxable_value' => 200000.00,
            'tax_rate' => 18.00,
            'cgst_rate' => 9.0,
            'cgst_amount' => 18000.00,
            'sgst_rate' => 9.0,
            'sgst_amount' => 18000.00,
            'igst_rate' => 0.0,
            'igst_amount' => 0.0,
            'total' => 236000.00,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/invoices/{$invoice->id}/pdf");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'invoice',
            'company',
            'buyer',
            'hsn_summary',
            'amount_in_words',
        ]);

        $this->assertStringContainsString('Two Lakh Thirty Six Thousand', $response->json('amount_in_words'));
    }

    public function test_payment_recording_updates_invoice_balance_and_status(): void
    {
        $customer = Customer::create([
            'company_name' => 'Reliance Retail Solutions',
            'type' => 'enterprise',
            'status' => 'active',
        ]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV-2026-PAY01',
            'customer_id' => $customer->id,
            'user_id' => $this->user->id,
            'type' => 'sales',
            'status' => 'sent',
            'invoice_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'subtotal' => 100000.00,
            'tax_total' => 18000.00,
            'total' => 118000.00,
            'amount_paid' => 0.0,
            'balance_due' => 118000.00,
        ]);

        // Pay partial 50,000 via UPI
        $pay1 = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/invoices/{$invoice->id}/payment", [
                'amount' => 50000.00,
                'payment_method' => 'upi',
                'reference_number' => 'UPI-REF-99823101',
            ]);

        $pay1->assertStatus(201);
        $invoice->refresh();
        $this->assertEquals('partial', $invoice->status);
        $this->assertEquals(50000.00, $invoice->amount_paid);
        $this->assertEquals(68000.00, $invoice->balance_due);

        // Pay remaining 68,000 via NEFT/RTGS
        $pay2 = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/invoices/{$invoice->id}/payment", [
                'amount' => 68000.00,
                'payment_method' => 'bank_transfer',
                'reference_number' => 'NEFT-HDFC-88771122',
            ]);

        $pay2->assertStatus(201);
        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
        $this->assertEquals(118000.00, $invoice->amount_paid);
        $this->assertEquals(0.00, $invoice->balance_due);
    }
}
