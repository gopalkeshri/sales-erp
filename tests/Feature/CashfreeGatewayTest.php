<?php

namespace Tests\Feature;

use App\Models\CashfreeOrder;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\CashfreeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CashfreeGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Invoice $invoice;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->adminUser = User::where('role', 'admin')->first()
            ?? User::factory()->create([
                'name' => 'Admin Test',
                'email' => 'admin_test@example.com',
                'role' => 'admin',
                'is_active' => true,
            ]);

        $this->customer = Customer::create([
            'company_name' => 'Acme Technologies Ltd.',
            'email' => 'finance@acmetech.com',
            'phone' => '9876543210',
            'tax_number' => '27AAACA1234F1Z5',
            'status' => 'active',
            'created_by' => $this->adminUser->id,
        ]);

        $product = Product::create([
            'name' => 'Enterprise Cloud License',
            'sku' => 'SKU-CLOUD-101',
            'unit_price' => 10000.00,
            'is_active' => true,
        ]);

        $this->invoice = Invoice::create([
            'invoice_number' => 'INV-TEST-CF-1001',
            'customer_id' => $this->customer->id,
            'status' => 'sent',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'subtotal' => 10000.00,
            'discount_total' => 0.00,
            'tax_total' => 1800.00,
            'total' => 11800.00,
            'amount_paid' => 0.00,
            'balance_due' => 11800.00,
            'currency' => 'INR',
            'created_by' => $this->adminUser->id,
        ]);

        InvoiceItem::create([
            'invoice_id' => $this->invoice->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 10000.00,
            'tax_rate' => 18.00,
            'total' => 11800.00,
        ]);

        // Configure test Cashfree settings
        Setting::set('cashfree_enabled', '1', 'cashfree', 'boolean');
        Setting::set('cashfree_environment', 'sandbox', 'cashfree', 'string');
        Setting::set('cashfree_app_id', 'TEST_APP_ID_12345', 'cashfree', 'string');
        Setting::set('cashfree_secret_key', 'TEST_SECRET_KEY_67890', 'cashfree', 'string');
        Setting::set('cashfree_api_version', '2023-08-01', 'cashfree', 'string');
        Setting::set('cashfree_webhook_secret', 'TEST_WEBHOOK_SECRET_XYZ', 'cashfree', 'string');
    }

    public function test_cashfree_settings_are_stored_and_retrieved(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/settings');

        $response->assertStatus(200);
        $this->assertEquals('TEST_APP_ID_12345', $response->json('data.settings.cashfree_app_id'));
        $this->assertEquals('sandbox', $response->json('data.settings.cashfree_environment'));

        // Update settings
        $updateResp = $this->actingAs($this->adminUser)
            ->postJson('/api/settings', [
                'cashfree_app_id' => 'NEW_CF_APP_9999',
                'cashfree_environment' => 'production',
            ]);

        $updateResp->assertStatus(200);
        $this->assertEquals('NEW_CF_APP_9999', Setting::get('cashfree_app_id'));
        $this->assertEquals('production', Setting::get('cashfree_environment'));
    }

    public function test_cashfree_connection_test_endpoint(): void
    {
        // Mock Cashfree API response for orders list
        Http::fake([
            'https://sandbox.cashfree.com/pg/orders*' => Http::response([
                'data' => [],
            ], 200),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/settings/cashfree/test', [
                'cashfree_app_id' => 'TEST_APP_ID_12345',
                'cashfree_secret_key' => 'TEST_SECRET_KEY_67890',
                'cashfree_environment' => 'sandbox',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'connected' => true,
            ]);
    }

    public function test_cashfree_connection_test_fails_on_invalid_credentials(): void
    {
        Http::fake([
            'https://sandbox.cashfree.com/pg/orders*' => Http::response([
                'message' => 'Authentication failed. Invalid client credentials.',
            ], 401),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/settings/cashfree/test', [
                'cashfree_app_id' => 'INVALID_ID',
                'cashfree_secret_key' => 'INVALID_SECRET',
                'cashfree_environment' => 'sandbox',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'error',
                'connected' => false,
            ]);
    }

    public function test_authenticated_user_can_create_cashfree_order(): void
    {
        Http::fake([
            'https://sandbox.cashfree.com/pg/orders' => Http::response([
                'cf_order_id' => 987654321,
                'order_id' => 'CF_INVTESTCF1001_12345_abc',
                'entity' => 'order',
                'order_currency' => 'INR',
                'order_amount' => 11800.00,
                'order_status' => 'ACTIVE',
                'payment_session_id' => 'session_test_token_xyz987',
                'order_expiry_time' => now()->addDay()->toISOString(),
            ], 200),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/invoices/{$this->invoice->id}/cashfree/order", [
                'amount' => 11800.00,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'order_amount' => 11800.00,
                'order_currency' => 'INR',
                'payment_session_id' => 'session_test_token_xyz987',
            ]);

        $this->assertDatabaseHas('cashfree_orders', [
            'invoice_id' => $this->invoice->id,
            'order_amount' => 11800.00,
            'payment_session_id' => 'session_test_token_xyz987',
            'status' => 'ACTIVE',
        ]);
    }

    public function test_cashfree_payment_verification_records_payment_and_updates_invoice(): void
    {
        $orderId = 'CF_INVTESTCF1001_VERIFY_999';

        $cfOrder = CashfreeOrder::create([
            'invoice_id' => $this->invoice->id,
            'order_id' => $orderId,
            'cf_order_id' => '99887766',
            'payment_session_id' => 'session_token_verify',
            'order_amount' => 11800.00,
            'order_currency' => 'INR',
            'status' => 'ACTIVE',
            'customer_id' => 'CUST_' . $this->customer->id,
            'customer_name' => $this->customer->company_name,
            'customer_email' => $this->customer->email,
            'customer_phone' => '9876543210',
            'created_by' => $this->adminUser->id,
        ]);

        Http::fake([
            'https://sandbox.cashfree.com/pg/orders/' . $orderId => Http::response([
                'order_id' => $orderId,
                'cf_order_id' => 99887766,
                'order_amount' => 11800.00,
                'order_currency' => 'INR',
                'order_status' => 'PAID',
            ], 200),
            'https://sandbox.cashfree.com/pg/orders/' . $orderId . '/payments' => Http::response([
                [
                    'cf_payment_id' => 'cf_pay_11223344',
                    'order_id' => $orderId,
                    'payment_status' => 'SUCCESS',
                    'payment_amount' => 11800.00,
                    'payment_currency' => 'INR',
                    'payment_group' => 'upi',
                    'bank_reference' => 'UTR998877665544',
                    'payment_time' => now()->toIso8601String(),
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/invoices/{$this->invoice->id}/cashfree/verify", [
                'order_id' => $orderId,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'paid' => true,
            ]);

        // Verify local database state
        $this->invoice->refresh();
        $this->assertEquals(11800.00, (float) $this->invoice->amount_paid);
        $this->assertEquals(0.00, (float) $this->invoice->balance_due);
        $this->assertEquals('paid', $this->invoice->status);

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $this->invoice->id,
            'payment_method' => 'cashfree',
            'cashfree_order_id' => $orderId,
            'transaction_id' => 'cf_pay_11223344',
            'reference_number' => 'UTR998877665544',
        ]);

        $this->assertDatabaseHas('cashfree_orders', [
            'order_id' => $orderId,
            'status' => 'PAID',
            'cf_payment_id' => 'cf_pay_11223344',
        ]);
    }

    public function test_public_customer_can_create_order_and_verify_payment(): void
    {
        Http::fake([
            'https://sandbox.cashfree.com/pg/orders' => Http::response([
                'cf_order_id' => 554433221,
                'order_id' => 'CF_PUBLIC_INV_1001',
                'order_currency' => 'INR',
                'order_amount' => 5000.00,
                'order_status' => 'ACTIVE',
                'payment_session_id' => 'session_public_token_123',
            ], 200),
            'https://sandbox.cashfree.com/pg/orders/CF_PUBLIC_INV_1001' => Http::response([
                'order_id' => 'CF_PUBLIC_INV_1001',
                'order_status' => 'PAID',
            ], 200),
            'https://sandbox.cashfree.com/pg/orders/CF_PUBLIC_INV_1001/payments' => Http::response([
                [
                    'cf_payment_id' => 'cf_pay_public_999',
                    'order_id' => 'CF_PUBLIC_INV_1001',
                    'payment_status' => 'SUCCESS',
                    'payment_amount' => 5000.00,
                    'payment_group' => 'credit_card',
                    'bank_reference' => 'CARD-AUTH-8877',
                    'payment_time' => now()->toIso8601String(),
                ],
            ], 200),
        ]);

        // Public order creation (unauthenticated)
        $orderResp = $this->postJson("/api/public/invoices/{$this->invoice->id}/cashfree/order", [
            'amount' => 5000.00,
            'customer_phone' => '9876543210',
            'customer_email' => 'client@acme.com',
        ]);

        $orderResp->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'payment_session_id' => 'session_public_token_123',
            ]);

        $orderId = $orderResp->json('order_id');

        Http::fake([
            'https://sandbox.cashfree.com/pg/orders/' . $orderId => Http::response([
                'order_id' => $orderId,
                'order_status' => 'PAID',
            ], 200),
            'https://sandbox.cashfree.com/pg/orders/' . $orderId . '/payments' => Http::response([
                [
                    'cf_payment_id' => 'cf_pay_public_999',
                    'order_id' => $orderId,
                    'payment_status' => 'SUCCESS',
                    'payment_amount' => 5000.00,
                    'payment_group' => 'credit_card',
                    'bank_reference' => 'CARD-AUTH-8877',
                    'payment_time' => now()->toIso8601String(),
                ],
            ], 200),
        ]);

        // Public verify
        $verifyResp = $this->postJson("/api/public/invoices/{$this->invoice->id}/cashfree/verify", [
            'order_id' => $orderId,
        ]);

        $verifyResp->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'paid' => true,
            ]);

        $this->invoice->refresh();
        $this->assertEquals(5000.00, (float) $this->invoice->amount_paid);
        $this->assertEquals(6800.00, (float) $this->invoice->balance_due);
        $this->assertEquals('partial', $this->invoice->status);
    }

    public function test_cashfree_webhook_processes_valid_signature_and_records_payment(): void
    {
        $orderId = 'CF_WEBHOOK_ORDER_555';

        CashfreeOrder::create([
            'invoice_id' => $this->invoice->id,
            'order_id' => $orderId,
            'cf_order_id' => '11229988',
            'payment_session_id' => 'session_wh_xyz',
            'order_amount' => 11800.00,
            'order_currency' => 'INR',
            'status' => 'ACTIVE',
            'customer_id' => 'CUST_' . $this->customer->id,
            'customer_name' => $this->customer->company_name,
            'customer_email' => $this->customer->email,
            'customer_phone' => '9876543210',
        ]);

        Http::fake([
            'https://sandbox.cashfree.com/pg/orders/' . $orderId => Http::response([
                'order_id' => $orderId,
                'cf_order_id' => 11229988,
                'order_amount' => 11800.00,
                'order_status' => 'PAID',
            ], 200),
            'https://sandbox.cashfree.com/pg/orders/' . $orderId . '/payments' => Http::response([
                [
                    'cf_payment_id' => 'cf_pay_wh_887766',
                    'order_id' => $orderId,
                    'payment_status' => 'SUCCESS',
                    'payment_amount' => 11800.00,
                    'payment_group' => 'net_banking',
                    'bank_reference' => 'HDFC-NET-9911',
                    'payment_time' => now()->toIso8601String(),
                ],
            ], 200),
        ]);

        $payload = json_encode([
            'type' => 'PAYMENT_SUCCESS_WEBHOOK',
            'data' => [
                'order' => [
                    'order_id' => $orderId,
                ],
                'payment' => [
                    'cf_payment_id' => 'cf_pay_wh_887766',
                    'payment_status' => 'SUCCESS',
                ],
            ],
        ]);

        $timestamp = (string) time();
        $secret = 'TEST_WEBHOOK_SECRET_XYZ';
        $signature = base64_encode(hash_hmac('sha256', $timestamp . $payload, $secret, true));

        $server = $this->transformHeadersToServerVars([
            'x-webhook-signature' => $signature,
            'x-webhook-timestamp' => $timestamp,
            'CONTENT_TYPE' => 'application/json',
        ]);

        $response = $this->call('POST', '/api/cashfree/webhook', [], [], [], $server, $payload);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $this->invoice->refresh();
        $this->assertEquals(11800.00, (float) $this->invoice->amount_paid);
        $this->assertEquals('paid', $this->invoice->status);
    }

    public function test_cashfree_webhook_rejects_invalid_signature(): void
    {
        $payload = json_encode([
            'type' => 'PAYMENT_SUCCESS_WEBHOOK',
            'data' => [
                'order' => ['order_id' => 'CF_FAKE_ORDER'],
            ],
        ]);

        $server = $this->transformHeadersToServerVars([
            'x-webhook-signature' => 'INVALID_SIGNATURE_HERE',
            'x-webhook-timestamp' => (string) time(),
            'CONTENT_TYPE' => 'application/json',
        ]);

        $response = $this->call('POST', '/api/cashfree/webhook', [], [], [], $server, $payload);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Invalid webhook signature.',
            ]);
    }

    public function test_public_payment_page_and_return_page_render_successfully(): void
    {
        $payPageResp = $this->get("/pay/invoice/{$this->invoice->id}");
        $payPageResp->assertStatus(200)
            ->assertSee($this->invoice->invoice_number)
            ->assertSee('Pay via Cashfree')
            ->assertSee('Acme Technologies Ltd.');

        // Return page
        $orderId = 'CF_RETURN_TEST_123';
        CashfreeOrder::create([
            'invoice_id' => $this->invoice->id,
            'order_id' => $orderId,
            'cf_order_id' => '332211',
            'payment_session_id' => 'sess_ret',
            'order_amount' => 11800.00,
            'order_currency' => 'INR',
            'status' => 'PAID',
            'customer_name' => 'Acme Technologies Ltd.',
        ]);

        Http::fake([
            'https://sandbox.cashfree.com/pg/orders/' . $orderId => Http::response([
                'order_id' => $orderId,
                'order_status' => 'PAID',
            ], 200),
            'https://sandbox.cashfree.com/pg/orders/' . $orderId . '/payments' => Http::response([
                [
                    'cf_payment_id' => 'pay_ret_88',
                    'payment_status' => 'SUCCESS',
                    'payment_amount' => 11800.00,
                    'payment_group' => 'upi',
                ],
            ], 200),
        ]);

        $returnResp = $this->get("/cashfree/return?order_id={$orderId}");
        $returnResp->assertStatus(200)
            ->assertSee('Payment Successful!')
            ->assertSee($orderId);
    }
}
