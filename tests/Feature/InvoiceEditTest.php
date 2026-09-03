<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceEditTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected string $token;
    protected Customer $customerMh;
    protected Customer $customerKa;
    protected Product $product1;
    protected Product $product2;
    protected InvoiceService $invoiceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::where('email', 'admin@saleserp.com')->first();
        $this->token = $this->user->createToken('invoice-edit-test')->plainTextToken;
        $this->invoiceService = app(InvoiceService::class);

        Setting::set('company_name', 'Apex Enterprise Solutions Pvt. Ltd.', 'string');
        Setting::set('company_state', 'Maharashtra', 'string');
        Setting::set('company_state_code', '27', 'string');
        Setting::set('company_gstin', '27AAACA1234F1Z5', 'string');
        Setting::set('currency_symbol', '₹', 'string');

        $this->customerMh = Customer::firstOrCreate([
            'company_name' => 'TechCorp Innovations Mumbai',
        ], [
            'email' => 'finance@techcorp-mumbai.in',
            'phone' => '+91 98200 12345',
            'gst_number' => '27AAACT1234F1Z1',
            'state_code' => '27',
            'address_state' => 'Maharashtra',
            'address_city' => 'Mumbai',
        ]);

        $this->customerKa = Customer::firstOrCreate([
            'company_name' => 'Silicon Valley Cloud Bangalore',
        ], [
            'email' => 'accounts@svcloud-blr.in',
            'phone' => '+91 98450 54321',
            'gst_number' => '29AAACS1234F1Z9',
            'state_code' => '29',
            'address_state' => 'Karnataka',
            'address_city' => 'Bengaluru',
        ]);

        $this->product1 = Product::firstOrCreate([
            'sku' => 'LAPTOP-PRO-15',
        ], [
            'name' => 'Enterprise Laptop 15-inch',
            'unit_price' => 50000.00,
            'cost_price' => 40000.00,
            'tax_rate' => 18.00,
            'hsn_code' => '8471',
            'unit' => 'Pcs',
            'is_active' => true,
        ]);

        $this->product2 = Product::firstOrCreate([
            'sku' => 'SERVER-RACK-42U',
        ], [
            'name' => 'Server Rack Cabinet 42U',
            'unit_price' => 20000.00,
            'cost_price' => 15000.00,
            'tax_rate' => 18.00,
            'hsn_code' => '8517',
            'unit' => 'Pcs',
            'is_active' => true,
        ]);
    }

    public function test_can_update_invoice_metadata(): void
    {
        $invoice = $this->invoiceService->createInvoice([
            'customer_id' => $this->customerMh->id,
            'status' => 'draft',
            'payment_terms' => 'net_30',
            'notes' => 'Initial terms',
        ], [
            [
                'product_id' => $this->product1->id,
                'quantity' => 1,
                'unit_price' => 50000.00,
                'discount_percent' => 0,
                'tax_rate' => 18.00,
            ],
        ], $this->user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/invoices/{$invoice->id}", [
                'status' => 'sent',
                'payment_terms' => 'net_45',
                'notes' => 'Updated terms with 45 days credit',
                'due_date' => now()->addDays(45)->toDateString(),
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'sent');
        $response->assertJsonPath('payment_terms', 'net_45');
        $response->assertJsonPath('notes', 'Updated terms with 45 days credit');

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'sent',
            'payment_terms' => 'net_45',
            'notes' => 'Updated terms with 45 days credit',
        ]);
    }

    public function test_can_update_invoice_line_items_and_recalculate_gst_totals(): void
    {
        $invoice = $this->invoiceService->createInvoice([
            'customer_id' => $this->customerMh->id,
            'status' => 'draft',
        ], [
            [
                'product_id' => $this->product1->id,
                'quantity' => 1,
                'unit_price' => 50000.00,
                'discount_percent' => 0,
                'tax_rate' => 18.00,
            ],
        ], $this->user);

        // Initially: 50,000 + 18% (9000) = 59,000 Total (CGST 4500, SGST 4500)
        $this->assertEquals(59000.00, (float) $invoice->total);

        // Edit invoice: 2x Product1 with 10% discount + 1x Product2 with 0% discount
        // Product1: 2 * 50,000 = 100,000 - 10,000 (10%) = 90,000 taxable. 18% GST = 16,200 (CGST 8,100, SGST 8,100)
        // Product2: 1 * 20,000 = 20,000 taxable. 18% GST = 3,600 (CGST 1,800, SGST 1,800)
        // Subtotal: 120,000. Discount: 10,000. Taxable: 110,000. Tax Total: 19,800. Grand Total: 129,800.
        $payload = [
            'customer_id' => $this->customerMh->id,
            'state_code' => '27',
            'items' => [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 2,
                    'unit_price' => 50000.00,
                    'discount_percent' => 10,
                    'tax_rate' => 18.00,
                ],
                [
                    'product_id' => $this->product2->id,
                    'quantity' => 1,
                    'unit_price' => 20000.00,
                    'discount_percent' => 0,
                    'tax_rate' => 18.00,
                ],
            ],
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/invoices/{$invoice->id}", $payload);

        $response->assertStatus(200);
        $response->assertJsonPath('subtotal', '120000.00');
        $response->assertJsonPath('discount_total', '10000.00');
        $response->assertJsonPath('cgst_total', '9900.00');
        $response->assertJsonPath('sgst_total', '9900.00');
        $response->assertJsonPath('igst_total', '0.00');
        $response->assertJsonPath('tax_total', '19800.00');
        $response->assertJsonPath('total', '129800.00');
        $response->assertJsonPath('balance_due', '129800.00');
        $response->assertJsonPath('gst_type', 'intra_state');

        $this->assertEquals(2, InvoiceItem::where('invoice_id', $invoice->id)->count());
    }

    public function test_can_update_invoice_place_of_supply_from_intra_to_inter_state(): void
    {
        // Start with Maharashtra Intra-state Invoice
        $invoice = $this->invoiceService->createInvoice([
            'customer_id' => $this->customerMh->id,
            'state_code' => '27',
            'place_of_supply' => 'Maharashtra',
        ], [
            [
                'product_id' => $this->product1->id,
                'quantity' => 1,
                'unit_price' => 50000.00,
                'discount_percent' => 0,
                'tax_rate' => 18.00,
            ],
        ], $this->user);

        $this->assertEquals('intra_state', $invoice->gst_type);
        $this->assertEquals(4500.00, (float) $invoice->cgst_total);
        $this->assertEquals(4500.00, (float) $invoice->sgst_total);
        $this->assertEquals(0.00, (float) $invoice->igst_total);

        // Edit place of supply to Karnataka (State Code 29)
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/invoices/{$invoice->id}", [
                'customer_id' => $this->customerKa->id,
                'state_code' => '29',
                'place_of_supply' => 'Karnataka',
                'items' => [
                    [
                        'product_id' => $this->product1->id,
                        'quantity' => 1,
                        'unit_price' => 50000.00,
                        'discount_percent' => 0,
                        'tax_rate' => 18.00,
                    ],
                ],
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('gst_type', 'inter_state');
        $response->assertJsonPath('cgst_total', '0.00');
        $response->assertJsonPath('sgst_total', '0.00');
        $response->assertJsonPath('igst_total', '9000.00');
        $response->assertJsonPath('tax_total', '9000.00');
        $response->assertJsonPath('total', '59000.00');
    }

    public function test_updating_invoice_with_existing_payment_recalculates_balance_due_accurately(): void
    {
        $invoice = $this->invoiceService->createInvoice([
            'customer_id' => $this->customerMh->id,
            'status' => 'sent',
        ], [
            [
                'product_id' => $this->product1->id,
                'quantity' => 1,
                'unit_price' => 50000.00,
                'discount_percent' => 0,
                'tax_rate' => 18.00,
            ],
        ], $this->user);

        // Total: 59,000. Record partial payment of 20,000
        $this->invoiceService->recordPayment($invoice, [
            'amount' => 20000.00,
            'payment_method' => 'upi',
            'reference_number' => 'UPI-REF-999',
        ], $this->user);

        $invoice->refresh();
        $this->assertEquals(20000.00, (float) $invoice->amount_paid);
        $this->assertEquals(39000.00, (float) $invoice->balance_due);
        $this->assertEquals('partial', $invoice->status);

        // Now edit the invoice: Add product 2 (20,000 + 18% = 23,600)
        // New grand total: 59,000 + 23,600 = 82,600
        // New balance due should be: 82,600 - 20,000 = 62,600
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/invoices/{$invoice->id}", [
                'items' => [
                    [
                        'product_id' => $this->product1->id,
                        'quantity' => 1,
                        'unit_price' => 50000.00,
                        'discount_percent' => 0,
                        'tax_rate' => 18.00,
                    ],
                    [
                        'product_id' => $this->product2->id,
                        'quantity' => 1,
                        'unit_price' => 20000.00,
                        'discount_percent' => 0,
                        'tax_rate' => 18.00,
                    ],
                ],
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('total', '82600.00');
        $response->assertJsonPath('amount_paid', '20000.00');
        $response->assertJsonPath('balance_due', '62600.00');
        $response->assertJsonPath('status', 'partial');

        $invoice->refresh();
        $this->assertEquals(82600.00, (float) $invoice->total);
        $this->assertEquals(20000.00, (float) $invoice->amount_paid);
        $this->assertEquals(62600.00, (float) $invoice->balance_due);
    }

    public function test_can_edit_invoice_via_web_session(): void
    {
        $invoice = $this->invoiceService->createInvoice([
            'customer_id' => $this->customerMh->id,
        ], [
            [
                'product_id' => $this->product1->id,
                'quantity' => 1,
                'unit_price' => 50000.00,
                'tax_rate' => 18.00,
            ],
        ], $this->user);

        $response = $this->actingAs($this->user)
            ->putJson("/api/invoices/{$invoice->id}", [
                'notes' => 'Updated via Web Session ERP interface',
                'type' => 'proforma',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('notes', 'Updated via Web Session ERP interface');
        $response->assertJsonPath('type', 'proforma');
    }

    public function test_invoice_edit_validation_errors(): void
    {
        $invoice = $this->invoiceService->createInvoice([
            'customer_id' => $this->customerMh->id,
        ], [
            [
                'product_id' => $this->product1->id,
                'quantity' => 1,
                'unit_price' => 50000.00,
                'tax_rate' => 18.00,
            ],
        ], $this->user);

        // Invalid customer ID
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/invoices/{$invoice->id}", [
                'customer_id' => 999999,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['customer_id']);

        // Invalid status
        $response2 = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/invoices/{$invoice->id}", [
                'status' => 'invalid_status_code',
            ]);

        $response2->assertStatus(422);
        $response2->assertJsonValidationErrors(['status']);
    }
}
