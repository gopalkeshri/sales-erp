<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Setting;
use App\Services\QuoteService;

class QuoteGstAndPrintTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Customer $intraCustomer;
    protected Customer $interCustomer;
    protected Product $product1;
    protected Product $product2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Admin Tester',
            'email' => 'admin_quote_test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        Setting::set('company_name', 'Apex Enterprise Solutions Pvt. Ltd.');
        Setting::set('company_state_code', '27');
        Setting::set('company_state', 'Maharashtra');
        Setting::set('tax_id', '27AAACA1234F1Z5');
        Setting::set('company_pan', 'AAACA1234F');
        Setting::set('default_currency', 'INR');
        Setting::set('currency_symbol', '₹');
        Setting::set('quote_prefix', 'QT-');
        Setting::set('order_prefix', 'ORD-');

        $this->intraCustomer = Customer::create([
            'company_name' => 'Mumbai Infotech Solutions Pvt Ltd',
            'state_code' => '27',
            'address_state' => 'Maharashtra',
            'gst_number' => '27AABCM5678K1Z2',
            'pan_number' => 'AABCM5678K',
            'type' => 'enterprise',
            'status' => 'active',
        ]);

        $this->interCustomer = Customer::create([
            'company_name' => 'Bangalore Cybernetics Ltd',
            'state_code' => '29',
            'address_state' => 'Karnataka',
            'gst_number' => '29AABCB1234L1Z8',
            'pan_number' => 'AABCB1234L',
            'type' => 'enterprise',
            'status' => 'active',
        ]);

        $this->product1 = Product::create([
            'sku' => 'SRV-001',
            'name' => 'Cloud Architecture Retainer SLA',
            'type' => 'service',
            'hsn_code' => '998313',
            'unit_price' => 50000.00,
            'cost_price' => 20000.00,
            'tax_rate' => 18.00,
            'unit' => 'months',
            'is_active' => true,
        ]);

        $this->product2 = Product::create([
            'sku' => 'HW-SRV-01',
            'name' => 'Enterprise Rack Server Node 2U',
            'type' => 'product',
            'hsn_code' => '847150',
            'unit_price' => 120000.00,
            'cost_price' => 80000.00,
            'tax_rate' => 18.00,
            'unit' => 'units',
            'is_active' => true,
        ]);
    }

    public function test_intra_state_quote_creation_calculates_cgst_and_sgst(): void
    {
        $quoteService = new QuoteService();

        $quote = $quoteService->createQuote([
            'customer_id' => $this->intraCustomer->id,
            'valid_until' => now()->addDays(30)->toDateString(),
        ], [
            [
                'product_id' => $this->product1->id,
                'quantity' => 2,
                'unit_price' => 50000.00,
                'discount_percent' => 10.00, // base 100,000, disc 10,000, taxable 90,000
                'tax_rate' => 18.00,
            ],
            [
                'product_id' => $this->product2->id,
                'quantity' => 1,
                'unit_price' => 120000.00,
                'discount_percent' => 0.00, // taxable 120,000
                'tax_rate' => 18.00,
            ],
        ], $this->user);

        // Taxable total = 90,000 + 120,000 = 210,000
        // CGST = 9% of 210,000 = 18,900
        // SGST = 9% of 210,000 = 18,900
        // Total Tax = 37,800
        // Grand Total = 247,800

        $this->assertEquals('intra_state', $quote->gst_type);
        $this->assertEquals(220000.00, (float)$quote->subtotal);
        $this->assertEquals(10000.00, (float)$quote->discount_total);
        $this->assertEquals(37800.00, (float)$quote->tax_total);
        $this->assertEquals(18900.00, (float)$quote->cgst_total);
        $this->assertEquals(18900.00, (float)$quote->sgst_total);
        $this->assertEquals(0.00, (float)$quote->igst_total);
        $this->assertEquals(247800.00, (float)$quote->total);

        $items = $quote->items;
        $this->assertCount(2, $items);
        $this->assertEquals('998313', $items[0]->hsn_code);
        $this->assertEquals(90000.00, (float)$items[0]->taxable_value);
        $this->assertEquals(8100.00, (float)$items[0]->cgst_amount);
        $this->assertEquals(8100.00, (float)$items[0]->sgst_amount);
    }

    public function test_inter_state_quote_creation_calculates_igst(): void
    {
        $quoteService = new QuoteService();

        $quote = $quoteService->createQuote([
            'customer_id' => $this->interCustomer->id,
            'state_code' => '29',
            'place_of_supply' => 'Karnataka',
            'valid_until' => now()->addDays(30)->toDateString(),
        ], [
            [
                'product_id' => $this->product2->id,
                'quantity' => 2,
                'unit_price' => 100000.00,
                'discount_percent' => 5.00, // taxable = 200,000 - 10,000 = 190,000
                'tax_rate' => 18.00,
            ],
        ], $this->user);

        // IGST = 18% of 190,000 = 34,200
        // Grand Total = 224,200

        $this->assertEquals('inter_state', $quote->gst_type);
        $this->assertEquals(0.00, (float)$quote->cgst_total);
        $this->assertEquals(0.00, (float)$quote->sgst_total);
        $this->assertEquals(34200.00, (float)$quote->igst_total);
        $this->assertEquals(34200.00, (float)$quote->tax_total);
        $this->assertEquals(224200.00, (float)$quote->total);
    }

    public function test_converting_gst_quote_to_sales_order(): void
    {
        $quoteService = new QuoteService();

        $quote = $quoteService->createQuote([
            'customer_id' => $this->intraCustomer->id,
            'valid_until' => now()->addDays(15)->toDateString(),
        ], [
            [
                'product_id' => $this->product1->id,
                'quantity' => 1,
                'unit_price' => 50000.00,
                'discount_percent' => 0.00,
                'tax_rate' => 18.00,
            ],
        ], $this->user);

        $order = $quoteService->convertToOrder($quote, $this->user);

        $this->assertEquals('converted', $quote->fresh()->status);
        $this->assertEquals($order->id, $quote->fresh()->converted_to_order_id);
        $this->assertEquals('confirmed', $order->status);
        $this->assertEquals($quote->total, $order->total);
        $this->assertCount(1, $order->items);
    }

    public function test_api_pdf_data_returns_hsn_breakdown_and_words(): void
    {
        $quoteService = new QuoteService();

        $quote = $quoteService->createQuote([
            'customer_id' => $this->intraCustomer->id,
        ], [
            [
                'product_id' => $this->product1->id,
                'quantity' => 1,
                'unit_price' => 50000.00,
                'discount_percent' => 0.00,
                'tax_rate' => 18.00,
            ],
        ], $this->user);

        $response = $this->actingAs($this->user)->getJson("/api/quotes/{$quote->id}/pdf");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'quote',
            'amount_in_words',
            'hsn_summary',
            'company' => [
                'name',
                'gstin',
                'pan',
                'bank_name',
                'bank_account_no',
                'bank_ifsc',
                'upi_id',
            ],
            'buyer',
        ]);

        $this->assertStringContainsString('Fifty Nine Thousand Rupees', $response->json('amount_in_words'));
        $this->assertEquals('27AAACA1234F1Z5', $response->json('company.gstin'));
    }

    public function test_standalone_print_quote_route_renders_successfully(): void
    {
        $quoteService = new QuoteService();

        $quote = $quoteService->createQuote([
            'customer_id' => $this->intraCustomer->id,
        ], [
            [
                'product_id' => $this->product1->id,
                'quantity' => 1,
                'unit_price' => 50000.00,
                'discount_percent' => 0.00,
                'tax_rate' => 18.00,
            ],
        ], $this->user);

        $response = $this->actingAs($this->user)->get("/quotes/{$quote->id}/print");

        $response->assertStatus(200);
        $response->assertSee('PRICE QUOTATION');
        $response->assertSee($quote->quote_number);
        $response->assertSee('HSN / SAC Tax Breakdown:');
        $response->assertSee('998313');
        $response->assertSee('Mumbai Infotech Solutions Pvt Ltd');
        $response->assertSee('Apex Enterprise Solutions Pvt. Ltd.');
    }
}
