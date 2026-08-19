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
use App\Models\Inventory;
use App\Models\Commission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebSessionApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->seed(\Database\Seeders\DemoDataSeeder::class);
    }

    public function test_web_session_can_perform_all_11_module_actions(): void
    {
        $admin = User::where('email', 'admin@saleserp.com')->first();
        $this->actingAs($admin);

        // 1. Create Customer
        $custRes = $this->postJson('/api/customers', [
            'company_name' => 'Delta Tech Enterprises',
            'trade_name' => 'Delta Systems',
            'type' => 'enterprise',
            'gst_number' => '27AACCD9999F1Z1',
            'industry' => 'Cloud & AI',
            'credit_limit' => 200000,
            'payment_terms' => 'net_30',
        ]);
        $custRes->assertStatus(201);
        $customerId = $custRes->json('id');

        // 2. Create Lead
        $leadRes = $this->postJson('/api/leads', [
            'title' => 'Server Farm Expansion Q4',
            'company_name' => 'Delta Tech Enterprises',
            'contact_name' => 'John Wick',
            'email' => 'john@deltatech.example.com',
            'phone' => '+1 555-0199',
            'source' => 'website',
            'estimated_value' => 60000,
        ]);
        $leadRes->assertStatus(201);
        $leadId = $leadRes->json('id');

        // 3. Convert Lead to Opportunity
        $convertRes = $this->postJson("/api/leads/{$leadId}/convert", [
            'opportunity_title' => 'Delta Enterprise Expansion Deal',
            'amount' => 60000,
        ]);
        $convertRes->assertStatus(200);

        // 4. Create Opportunity directly
        $oppRes = $this->postJson('/api/opportunities', [
            'title' => 'AI Accelerator Deployment',
            'customer_id' => $customerId,
            'stage' => 'proposal',
            'amount' => 85000,
            'close_date' => now()->addDays(30)->toDateString(),
        ]);
        $oppRes->assertStatus(201);
        $oppId = $oppRes->json('id');

        // 5. Update Opportunity Stage
        $stageRes = $this->putJson("/api/opportunities/{$oppId}/stage", [
            'stage' => 'negotiation',
        ]);
        $stageRes->assertStatus(200);

        // 6. Create Quote with line items
        $product = Product::where('sku', 'HW-SRV-R900')->first() ?: Product::first();
        $quoteRes = $this->postJson('/api/quotes', [
            'customer_id' => $customerId,
            'valid_until' => now()->addDays(20)->toDateString(),
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => $product->unit_price,
                    'discount_percent' => 5,
                    'tax_rate' => 10,
                ]
            ]
        ]);
        $quoteRes->assertStatus(201);
        $quoteId = $quoteRes->json('id');

        // 7. Convert Quote to Order
        $orderRes = $this->postJson("/api/quotes/{$quoteId}/convert");
        $orderRes->assertStatus(200);
        $orderId = $orderRes->json('order.id');

        // 8. Update Order Status
        $orderStatusRes = $this->putJson("/api/orders/{$orderId}/status", [
            'status' => 'shipped',
            'warehouse_id' => 1,
        ]);
        $orderStatusRes->assertStatus(200);

        // 9. Generate Invoice from Order
        $invRes = $this->postJson("/api/orders/{$orderId}/invoice");
        $invRes->assertStatus(200);
        $invoiceId = $invRes->json('invoice.id');

        // 10. Record Payment on Invoice
        $invoice = Invoice::findOrFail($invoiceId);
        $payRes = $this->postJson("/api/invoices/{$invoiceId}/payment", [
            'amount' => $invoice->balance_due,
            'payment_method' => 'bank_transfer',
            'reference_number' => 'TXN-WIRE-9921',
        ]);
        $payRes->assertStatus(201)
            ->assertJsonPath('invoice.status', 'paid');

        // 11. Inventory Stock-In and Transfer
        $w1 = Warehouse::first();
        $w2 = Warehouse::skip(1)->first();
        $stockInRes = $this->postJson('/api/inventory/stock-in', [
            'product_id' => $product->id,
            'warehouse_id' => $w1->id,
            'quantity' => 10,
            'notes' => 'Bulk Restock',
        ]);
        $stockInRes->assertStatus(201);

        $transferRes = $this->postJson('/api/inventory/transfer', [
            'product_id' => $product->id,
            'from_warehouse_id' => $w1->id,
            'to_warehouse_id' => $w2->id,
            'quantity' => 3,
        ]);
        $transferRes->assertStatus(200);

        // 12. Calculate & Approve Commission
        $rep = User::where('role', 'sales_rep')->first();
        $commRes = $this->postJson('/api/commissions/calculate', [
            'user_id' => $rep->id,
            'period' => date('Y-m'),
            'commission_rate' => 5.0,
        ]);
        $commRes->assertStatus(200);
        $commId = $commRes->json('commission.id');

        $approveRes = $this->putJson("/api/commissions/{$commId}/approve");
        $approveRes->assertStatus(200);
    }

    public function test_direct_invoice_and_inventory_management_lifecycle(): void
    {
        $admin = User::where('email', 'admin@saleserp.com')->first();
        $this->actingAs($admin);

        $customer = Customer::first();
        $product = Product::first();
        $w1 = Warehouse::first();
        $w2 = Warehouse::skip(1)->first();

        // 1. Create a Direct Tax Invoice
        $directInvRes = $this->postJson('/api/invoices', [
            'customer_id' => $customer->id,
            'type' => 'sales',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => 'sent',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 3,
                    'unit_price' => $product->unit_price,
                    'discount_percent' => 10,
                    'tax_rate' => 18,
                ]
            ]
        ]);
        $directInvRes->assertStatus(201);
        $invId = $directInvRes->json('id');
        $this->assertNotNull($invId);

        // 2. Fetch Invoice PDF data
        $pdfRes = $this->getJson("/api/invoices/{$invId}/pdf");
        $pdfRes->assertStatus(200)
            ->assertJsonStructure(['invoice' => ['invoice_number', 'items', 'customer'], 'company']);

        // 3. Send Invoice
        $sendRes = $this->postJson("/api/invoices/{$invId}/send");
        $sendRes->assertStatus(200)
            ->assertJsonPath('invoice.status', 'sent');

        // 4. Record Partial & Full Payment
        $inv = Invoice::findOrFail($invId);
        $payPartRes = $this->postJson("/api/invoices/{$invId}/payment", [
            'amount' => 500,
            'payment_method' => 'credit_card',
            'reference_number' => 'CC-AUTH-8871',
        ]);
        $payPartRes->assertStatus(201)
            ->assertJsonPath('invoice.status', 'partial');

        $inv->refresh();
        $payFullRes = $this->postJson("/api/invoices/{$invId}/payment", [
            'amount' => $inv->balance_due,
            'payment_method' => 'bank_transfer',
            'reference_number' => 'WIRE-FINAL-9901',
        ]);
        $payFullRes->assertStatus(201)
            ->assertJsonPath('invoice.status', 'paid');
        $this->assertEquals(0, (float)$payFullRes->json('invoice.balance_due'));

        // 5. Create New Product SKU in Catalog
        $newProdRes = $this->postJson('/api/products', [
            'sku' => 'HW-NV-H100-PRO',
            'name' => 'Nvidia H100 80GB SXM5 Accelerator',
            'category' => 'Compute Hardware',
            'type' => 'product',
            'unit_price' => 32000.00,
            'cost_price' => 27000.00,
            'min_stock_level' => 2,
            'reorder_point' => 5,
        ]);
        $newProdRes->assertStatus(201);
        $newProdId = $newProdRes->json('id');

        // 6. Stock In new product into warehouse 1
        $stockRes = $this->postJson('/api/inventory/stock-in', [
            'product_id' => $newProdId,
            'warehouse_id' => $w1->id,
            'quantity' => 8,
            'notes' => 'Direct vendor inward delivery',
        ]);
        $stockRes->assertStatus(201);

        // 7. Adjust physical count
        $invRecord = Inventory::where('product_id', $newProdId)->where('warehouse_id', $w1->id)->first();
        $adjustRes = $this->putJson("/api/inventory/{$invRecord->id}", [
            'quantity' => 12,
            'reason' => 'Annual physical count audit',
        ]);
        $adjustRes->assertStatus(200)
            ->assertJsonPath('inventory.quantity', 12);

        // 8. Inter-warehouse transfer
        $transRes = $this->postJson('/api/inventory/transfer', [
            'product_id' => $newProdId,
            'from_warehouse_id' => $w1->id,
            'to_warehouse_id' => $w2->id,
            'quantity' => 4,
            'notes' => 'Redistribute stock to EU depot',
        ]);
        $transRes->assertStatus(200);

        $this->assertEquals(8, Inventory::where('product_id', $newProdId)->where('warehouse_id', $w1->id)->value('quantity'));
        $this->assertEquals(4, Inventory::where('product_id', $newProdId)->where('warehouse_id', $w2->id)->value('quantity'));
    }
}
