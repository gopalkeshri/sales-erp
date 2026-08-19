<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quote;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesErpApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->seed(\Database\Seeders\DemoDataSeeder::class);
    }

    public function test_auth_login_and_me_endpoints(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@saleserp.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'token_type', 'user']);

        $token = $response->json('token');

        $meResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/auth/me');

        $meResponse->assertStatus(200)
            ->assertJsonPath('user.email', 'admin@saleserp.com');
    }

    public function test_lead_creation_and_conversion(): void
    {
        $admin = User::where('email', 'admin@saleserp.com')->first();
        $token = $admin->createToken('test')->plainTextToken;

        // Create Lead
        $leadRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/leads', [
                'title' => 'Test Inbound Cloud Deal',
                'company_name' => 'Test Matrix Corp',
                'contact_name' => 'Neo Anderson',
                'email' => 'neo@matrix.example.com',
                'phone' => '+1 (555) 123-4567',
                'source' => 'website',
                'estimated_value' => 75000,
            ]);

        $leadRes->assertStatus(201);
        $leadId = $leadRes->json('id');

        // Convert Lead
        $convertRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/leads/{$leadId}/convert", [
                'opportunity_title' => 'Matrix Enterprise Deal',
                'amount' => 75000,
            ]);

        $convertRes->assertStatus(200)
            ->assertJsonPath('data.lead.status', 'converted');
    }

    public function test_opportunity_pipeline_and_stage_update(): void
    {
        $admin = User::where('email', 'admin@saleserp.com')->first();
        $token = $admin->createToken('test')->plainTextToken;

        $pipelineRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/opportunities/pipeline');

        $pipelineRes->assertStatus(200)
            ->assertJsonStructure(['prospecting', 'qualification', 'proposal', 'negotiation', 'closed_won', 'closed_lost']);

        $opp = Opportunity::first();
        $stageRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/opportunities/{$opp->id}/stage", [
                'stage' => 'negotiation',
            ]);

        $stageRes->assertStatus(200)
            ->assertJsonPath('stage', 'negotiation')
            ->assertJsonPath('probability', 75);
    }

    public function test_quote_to_order_conversion(): void
    {
        $admin = User::where('email', 'admin@saleserp.com')->first();
        $token = $admin->createToken('test')->plainTextToken;

        $quote = Quote::where('status', '!=', 'converted')->first();

        $convertRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/quotes/{$quote->id}/convert");

        $convertRes->assertStatus(200)
            ->assertJsonStructure(['message', 'order']);
    }

    public function test_invoice_creation_and_payment_recording(): void
    {
        $admin = User::where('email', 'admin@saleserp.com')->first();
        $token = $admin->createToken('test')->plainTextToken;

        $invoice = Invoice::where('status', '!=', 'paid')->first();

        $payRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/invoices/{$invoice->id}/payment", [
                'amount' => $invoice->balance_due,
                'payment_method' => 'bank_transfer',
                'reference_number' => 'TEST-WIRE-12345',
            ]);

        $payRes->assertStatus(201)
            ->assertJsonPath('invoice.status', 'paid')
            ->assertJsonPath('invoice.balance_due', '0.00');
    }

    public function test_inventory_transfer_between_warehouses(): void
    {
        $admin = User::where('email', 'admin@saleserp.com')->first();
        $token = $admin->createToken('test')->plainTextToken;

        $product = Product::where('type', 'product')->first();
        $w1 = Warehouse::first();
        $w2 = Warehouse::skip(1)->first();

        $transferRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/inventory/transfer', [
                'product_id' => $product->id,
                'from_warehouse_id' => $w1->id,
                'to_warehouse_id' => $w2->id,
                'quantity' => 2,
            ]);

        $transferRes->assertStatus(200)
            ->assertJsonStructure(['message', 'data']);
    }

    public function test_commission_calculation(): void
    {
        $admin = User::where('email', 'admin@saleserp.com')->first();
        $token = $admin->createToken('test')->plainTextToken;

        $rep = User::where('role', 'sales_rep')->first();

        $commRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/commissions/calculate', [
                'user_id' => $rep->id,
                'period' => date('Y-m'),
                'commission_rate' => 5.0,
            ]);

        $commRes->assertStatus(200)
            ->assertJsonStructure(['message', 'commission']);
    }

    public function test_dashboard_and_reports_endpoints(): void
    {
        $admin = User::where('email', 'admin@saleserp.com')->first();
        $token = $admin->createToken('test')->plainTextToken;

        $statsRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/dashboard/stats');

        $statsRes->assertStatus(200)
            ->assertJsonStructure(['total_revenue', 'total_pipeline', 'active_deals', 'win_rate']);

        $summaryRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/reports/sales-summary');

        $summaryRes->assertStatus(200)
            ->assertJsonStructure(['closed_won_amount', 'total_invoiced', 'total_collected', 'outstanding_balance']);
    }

    public function test_web_interface_renders_properly(): void
    {
        $admin = User::where('email', 'admin@saleserp.com')->first();
        $response = $this->actingAs($admin)->get('/');
        $response->assertStatus(200)
            ->assertSee('SALES ERP')
            ->assertSee('Executive Dashboard')
            ->assertSee('Lead Management')
            ->assertSee('Opportunity Pipeline');
    }
}
