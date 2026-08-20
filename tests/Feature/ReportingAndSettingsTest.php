<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportingAndSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::where('role', 'admin')->first()
            ?? User::create([
                'name' => 'Admin User',
                'email' => 'admin_reporting@saleserp.com',
                'password' => bcrypt('password123'),
                'role' => 'admin',
                'is_active' => true,
            ]);

        $this->actingAs($this->user);
    }

    public function test_erp_web_page_renders_with_reporting_and_settings_data(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Reporting & Business Intelligence', false);
        $response->assertSee('General & System Settings', false);
        $response->assertSee('GST Tax Ledger', false);
        $response->assertSee('Banking, UPI & GST Tax Identification', false);
    }

    public function test_sales_summary_api_endpoint(): void
    {
        $response = $this->getJson('/api/reports/sales-summary');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_invoiced',
            'total_collected',
            'outstanding_balance',
            'collection_rate',
            'total_invoices_count',
            'paid_invoices_count',
        ]);
    }

    public function test_tax_summary_api_endpoint(): void
    {
        $customer = Customer::first() ?? Customer::create([
            'company_name' => 'Acme India Pvt Ltd',
            'contact_person' => 'Rajesh Sharma',
            'email' => 'rajesh@acmeindia.com',
            'phone' => '+91 9876543210',
            'gst_number' => '27ABCDE1234F1Z5',
            'pan_number' => 'ABCDE1234F',
            'state_code' => '27',
            'billing_state' => 'Maharashtra',
            'credit_limit' => 500000,
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);

        Invoice::create([
            'invoice_number' => 'INV-TEST-TAX-001',
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => 10000.00,
            'tax_rate' => 18.00,
            'tax_total' => 1800.00,
            'discount_total' => 0.00,
            'total' => 11800.00,
            'amount_paid' => 11800.00,
            'balance_due' => 0.00,
            'gst_type' => 'intra_state',
            'cgst_total' => 900.00,
            'sgst_total' => 900.00,
            'igst_total' => 0.00,
            'status' => 'paid',
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/reports/tax-summary');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'taxable_value',
            'cgst_total',
            'sgst_total',
            'igst_total',
            'tax_total',
            'invoices_count',
        ]);
        $this->assertGreaterThanOrEqual(10000.00, (float) $response->json('taxable_value'));
        $this->assertGreaterThanOrEqual(900.00, (float) $response->json('cgst_total'));
        $this->assertGreaterThanOrEqual(900.00, (float) $response->json('sgst_total'));
        $this->assertGreaterThanOrEqual(1800.00, (float) $response->json('tax_total'));
    }

    public function test_revenue_trends_api_endpoint(): void
    {
        $response = $this->getJson('/api/reports/revenue-trends?months=6');
        $response->assertStatus(200);
        $this->assertIsArray($response->json());
        $this->assertCount(6, $response->json());
    }

    public function test_top_performers_and_territory_reports(): void
    {
        $resPerformers = $this->getJson('/api/reports/top-performers');
        $resPerformers->assertStatus(200);

        $resTerritory = $this->getJson('/api/reports/territory-performance');
        $resTerritory->assertStatus(200);

        $resProducts = $this->getJson('/api/reports/product-performance');
        $resProducts->assertStatus(200);
    }

    public function test_get_and_update_settings_api(): void
    {
        $resGet = $this->getJson('/api/settings');
        $resGet->assertStatus(200);

        $updateData = [
            'company_name' => 'Apex Cloud Tech Pvt Ltd',
            'company_email' => 'finance@apexcloud.in',
            'tax_id' => '27XYZAB9999P1Z2',
            'company_pan' => 'XYZAB9999P',
            'company_state_code' => '27',
            'bank_name' => 'State Bank of India',
            'bank_account_no' => '98765432109876',
            'bank_ifsc' => 'SBIN0001234',
            'bank_branch' => 'Nariman Point, Mumbai',
            'upi_id' => 'apexcloud@sbi',
            'currency_symbol' => '₹',
            'default_currency' => 'INR',
            'default_tax_rate' => 18.00,
        ];

        $resPost = $this->postJson('/api/settings', $updateData);
        $resPost->assertStatus(200);
        $resPost->assertJson(['status' => 'success']);

        $this->assertEquals('Apex Cloud Tech Pvt Ltd', Setting::get('company_name'));
        $this->assertEquals('27XYZAB9999P1Z2', Setting::get('tax_id'));
        $this->assertEquals('State Bank of India', Setting::get('bank_name'));
        $this->assertEquals('₹', Setting::get('currency_symbol'));
    }

    public function test_cache_clear_and_reset_settings_api(): void
    {
        $resClear = $this->postJson('/api/settings/cache-clear');
        $resClear->assertStatus(200);
        $resClear->assertJson(['status' => 'success']);

        $resReset = $this->postJson('/api/settings/reset');
        $resReset->assertStatus(200);
        $resReset->assertJson(['status' => 'success']);
    }
}
