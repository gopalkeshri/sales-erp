<?php

namespace App\Services;

use App\Models\CashfreeOrder;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class CashfreeService
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Get Cashfree Merchant App ID
     */
    public function getAppId(): string
    {
        $val = Setting::get('cashfree_app_id');
        return !empty($val) ? (string) $val : (string) config('services.cashfree.app_id', '');
    }

    /**
     * Get Cashfree Merchant Secret Key
     */
    public function getSecretKey(): string
    {
        $val = Setting::get('cashfree_secret_key');
        return !empty($val) ? (string) $val : (string) config('services.cashfree.secret_key', '');
    }

    /**
     * Get Cashfree Operating Environment (sandbox or production)
     */
    public function getEnvironment(): string
    {
        $val = Setting::get('cashfree_environment');
        return !empty($val) ? (string) $val : (string) config('services.cashfree.environment', 'sandbox');
    }

    /**
     * Get Cashfree API Version
     */
    public function getApiVersion(): string
    {
        $val = Setting::get('cashfree_api_version');
        return !empty($val) ? (string) $val : (string) config('services.cashfree.api_version', '2023-08-01');
    }

    /**
     * Get Cashfree Webhook Secret Key
     */
    public function getWebhookSecret(): string
    {
        $val = Setting::get('cashfree_webhook_secret');
        return !empty($val) ? (string) $val : (string) config('services.cashfree.webhook_secret', '');
    }

    /**
     * Check if Cashfree Gateway is enabled
     */
    public function isEnabled(): bool
    {
        $val = Setting::get('cashfree_enabled', '0');
        return $val === '1' || $val === 1 || $val === true;
    }

    /**
     * Get Base URL for Cashfree PG REST API
     */
    public function getBaseUrl(?string $environment = null): string
    {
        $env = $environment ?: $this->getEnvironment();
        if (strtolower($env) === 'production') {
            return 'https://api.cashfree.com/pg';
        }
        return 'https://sandbox.cashfree.com/pg';
    }

    /**
     * Standard HTTP Headers for Cashfree API
     */
    public function getHeaders(?string $appId = null, ?string $secretKey = null, ?string $apiVersion = null): array
    {
        return [
            'x-client-id' => $appId ?: $this->getAppId(),
            'x-client-secret' => $secretKey ?: $this->getSecretKey(),
            'x-api-version' => $apiVersion ?: $this->getApiVersion(),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Test connection & authentication against Cashfree PG API
     */
    public function testConnection(?string $appId = null, ?string $secretKey = null, ?string $environment = null): array
    {
        $appId = $appId ?: $this->getAppId();
        $secretKey = $secretKey ?: $this->getSecretKey();
        $environment = $environment ?: $this->getEnvironment();

        if (empty($appId) || empty($secretKey)) {
            return [
                'status' => 'error',
                'connected' => false,
                'message' => 'Cashfree App ID and Secret Key cannot be empty.',
            ];
        }

        $baseUrl = $this->getBaseUrl($environment);
        $headers = $this->getHeaders($appId, $secretKey);

        $startTime = microtime(true);
        try {
            // Ping Cashfree orders listing endpoint with limit=1 to test credentials
            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->get($baseUrl . '/orders', ['limit' => 1]);

            $latency = round((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                return [
                    'status' => 'success',
                    'connected' => true,
                    'latency_ms' => $latency,
                    'environment' => $environment,
                    'message' => "Successfully connected to Cashfree Payment Gateway ({$environment} mode). Credentials are valid.",
                ];
            }

            $errorBody = $response->json();
            $errorMessage = $errorBody['message'] ?? $response->body();

            if ($response->status() === 401 || $response->status() === 403) {
                return [
                    'status' => 'error',
                    'connected' => false,
                    'http_status' => $response->status(),
                    'message' => 'Authentication failed: Invalid Cashfree App ID or Secret Key for ' . $environment . ' environment.',
                ];
            }

            return [
                'status' => 'error',
                'connected' => false,
                'http_status' => $response->status(),
                'message' => 'Cashfree API returned error: ' . $errorMessage,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'connected' => false,
                'message' => 'Network / Connection error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Create an order in Cashfree and persist in database
     */
    public function createOrder(
        Invoice $invoice,
        float $amount,
        ?User $user = null,
        ?string $returnUrl = null,
        ?string $customerPhone = null,
        ?string $customerEmail = null
    ): CashfreeOrder {
        $appId = $this->getAppId();
        $secretKey = $this->getSecretKey();

        if (empty($appId) || empty($secretKey)) {
            throw new Exception('Cashfree Gateway credentials not configured. Please set App ID and Secret Key in Settings.');
        }

        if ($amount <= 0) {
            throw new Exception('Payment amount must be greater than zero.');
        }

        $balanceDue = (float) $invoice->balance_due;
        if ($balanceDue > 0 && $amount > ($balanceDue + 0.05)) {
            throw new Exception("Payment amount (₹{$amount}) cannot exceed the invoice balance due (₹{$balanceDue}).");
        }

        // Generate unique order ID (alphanumeric, underscore, hyphen, max 45 chars)
        $cleanInvNum = preg_replace('/[^a-zA-Z0-9]/', '', (string) $invoice->invoice_number);
        $orderId = 'CF_' . substr($cleanInvNum, 0, 15) . '_' . time() . '_' . substr(bin2hex(random_bytes(4)), 0, 6);

        // Sanitize customer details
        $customer = $invoice->customer;
        $customerId = 'CUST_' . ($customer ? $customer->id : 'GUEST');
        $customerName = $customer ? ($customer->company_name ?: 'Customer') : 'Customer';

        // Phone resolution: Cashfree requires valid 10-digit phone
        $rawPhone = $customerPhone ?: ($customer ? ($customer->phone ?: '9999999999') : '9999999999');
        $digitsOnly = preg_replace('/[^0-9]/', '', $rawPhone);
        if (strlen($digitsOnly) >= 10) {
            $formattedPhone = substr($digitsOnly, -10);
        } else {
            $formattedPhone = '9876543210';
        }

        // Email resolution
        $resolvedEmail = $customerEmail ?: ($customer ? ($customer->email ?: 'billing@example.com') : 'billing@example.com');
        if (!filter_var($resolvedEmail, FILTER_VALIDATE_EMAIL)) {
            $resolvedEmail = 'billing@customer.com';
        }

        // Return & Notify URLs
        $resolvedReturnUrl = $returnUrl ?: url('/cashfree/return') . '?order_id={order_id}';
        $notifyUrl = url('/api/cashfree/webhook');

        $payload = [
            'order_id' => $orderId,
            'order_amount' => round($amount, 2),
            'order_currency' => $invoice->currency ?: 'INR',
            'customer_details' => [
                'customer_id' => substr($customerId, 0, 45),
                'customer_name' => substr($customerName, 0, 95),
                'customer_email' => substr($resolvedEmail, 0, 95),
                'customer_phone' => $formattedPhone,
            ],
            'order_meta' => [
                'return_url' => $resolvedReturnUrl,
                'notify_url' => $notifyUrl,
            ],
            'order_note' => "Tax Invoice payment for #{$invoice->invoice_number}",
        ];

        $baseUrl = $this->getBaseUrl();
        $headers = $this->getHeaders();

        $response = Http::withHeaders($headers)
            ->timeout(15)
            ->post($baseUrl . '/orders', $payload);

        if (!$response->successful()) {
            $errData = $response->json();
            $message = $errData['message'] ?? $response->body();
            Log::error('Cashfree Create Order Error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => $payload,
            ]);
            throw new Exception("Cashfree order creation failed: {$message}");
        }

        $resData = $response->json();

        // Create local record
        $cfOrder = CashfreeOrder::create([
            'invoice_id' => $invoice->id,
            'order_id' => $orderId,
            'cf_order_id' => (string) ($resData['cf_order_id'] ?? ''),
            'payment_session_id' => $resData['payment_session_id'] ?? null,
            'order_amount' => round($amount, 2),
            'order_currency' => $resData['order_currency'] ?? 'INR',
            'status' => $resData['order_status'] ?? 'ACTIVE',
            'customer_id' => $customerId,
            'customer_name' => $customerName,
            'customer_email' => $resolvedEmail,
            'customer_phone' => $formattedPhone,
            'created_by' => $user?->id,
            'raw_response' => $resData,
        ]);

        return $cfOrder;
    }

    /**
     * Fetch order status from Cashfree PG
     */
    public function getOrderStatus(string $orderId): array
    {
        $baseUrl = $this->getBaseUrl();
        $headers = $this->getHeaders();

        $response = Http::withHeaders($headers)
            ->timeout(15)
            ->get($baseUrl . '/orders/' . urlencode($orderId));

        if (!$response->successful()) {
            throw new Exception('Failed to fetch Cashfree order: ' . ($response->json('message') ?? $response->body()));
        }

        return $response->json();
    }

    /**
     * Fetch payments list for an order from Cashfree PG
     */
    public function getOrderPayments(string $orderId): array
    {
        $baseUrl = $this->getBaseUrl();
        $headers = $this->getHeaders();

        $response = Http::withHeaders($headers)
            ->timeout(15)
            ->get($baseUrl . '/orders/' . urlencode($orderId) . '/payments');

        if (!$response->successful()) {
            return [];
        }

        return $response->json() ?? [];
    }

    /**
     * Verify payment with Cashfree servers and reconcile invoice
     */
    public function verifyAndRecordPayment(string $orderId, ?User $user = null): array
    {
        $cfOrder = CashfreeOrder::where('order_id', $orderId)->first();
        if (!$cfOrder) {
            throw new Exception("Cashfree order {$orderId} not found in database.");
        }

        $invoice = $cfOrder->invoice;
        if (!$invoice) {
            throw new Exception("Invoice associated with Cashfree order {$orderId} does not exist.");
        }

        // Fetch live status from Cashfree
        $remoteOrder = $this->getOrderStatus($orderId);
        $orderStatus = strtoupper($remoteOrder['order_status'] ?? 'ACTIVE');

        // Fetch payment details
        $payments = $this->getOrderPayments($orderId);
        $successfulPayment = null;

        foreach ($payments as $p) {
            if (isset($p['payment_status']) && strtoupper($p['payment_status']) === 'SUCCESS') {
                $successfulPayment = $p;
                break;
            }
        }

        // Update local CashfreeOrder details
        $cfPaymentId = $successfulPayment['cf_payment_id'] ?? ($remoteOrder['cf_order_id'] ?? null);
        $paymentGroup = $successfulPayment['payment_group'] ?? 'cashfree';
        $bankRef = $successfulPayment['bank_reference'] ?? ($successfulPayment['payment_completion_time'] ?? null);
        $paymentTime = isset($successfulPayment['payment_time']) ? date('Y-m-d H:i:s', strtotime($successfulPayment['payment_time'])) : now();

        $cfOrder->update([
            'status' => $orderStatus,
            'cf_payment_id' => (string) $cfPaymentId,
            'payment_method' => (string) $paymentGroup,
            'bank_reference' => (string) $bankRef,
            'payment_time' => $paymentTime,
            'raw_response' => array_merge((array) $cfOrder->raw_response, [
                'remote_order' => $remoteOrder,
                'payments' => $payments,
            ]),
        ]);

        if ($orderStatus === 'PAID' || $successfulPayment !== null) {
            // Check if payment already recorded in payments table
            $existingPayment = Payment::where('cashfree_order_id', $orderId)
                ->orWhere(function ($query) use ($cfPaymentId) {
                    if (!empty($cfPaymentId)) {
                        $query->where('transaction_id', (string) $cfPaymentId);
                    }
                })->first();

            if ($existingPayment) {
                return [
                    'status' => 'success',
                    'paid' => true,
                    'already_recorded' => true,
                    'order' => $cfOrder->fresh(),
                    'payment' => $existingPayment,
                    'invoice' => $invoice->fresh(['payments', 'customer']),
                ];
            }

            // Record the payment
            $paidAmount = (float) ($successfulPayment['payment_amount'] ?? $cfOrder->order_amount);
            $newPayment = $this->invoiceService->recordPayment($invoice, [
                'amount' => $paidAmount,
                'payment_method' => 'cashfree',
                'cashfree_order_id' => $orderId,
                'reference_number' => $bankRef ?: ($cfPaymentId ?: $orderId),
                'transaction_id' => (string) $cfPaymentId,
                'payment_date' => now()->toDateString(),
                'notes' => 'Cashfree Gateway settlement (' . strtoupper($paymentGroup) . '). CF Order: ' . $orderId . ($cfPaymentId ? ' | Payment ID: ' . $cfPaymentId : ''),
            ], $user);

            return [
                'status' => 'success',
                'paid' => true,
                'already_recorded' => false,
                'order' => $cfOrder->fresh(),
                'payment' => $newPayment,
                'invoice' => $invoice->fresh(['payments', 'customer']),
            ];
        }

        return [
            'status' => 'pending_or_failed',
            'paid' => false,
            'order_status' => $orderStatus,
            'order' => $cfOrder->fresh(),
            'invoice' => $invoice->fresh(),
        ];
    }

    /**
     * Verify Cashfree Webhook Signature (HMAC-SHA256)
     */
    public function verifyWebhookSignature(string $rawPayload, ?string $signature, ?string $timestamp): bool
    {
        $secret = $this->getWebhookSecret();
        if (empty($secret)) {
            // If webhook secret not configured, fallback to secret_key
            $secret = $this->getSecretKey();
        }

        if (empty($secret) || empty($signature) || empty($timestamp)) {
            return false;
        }

        $computedSignature = base64_encode(hash_hmac('sha256', $timestamp . $rawPayload, $secret, true));
        return hash_equals($computedSignature, $signature);
    }
}
