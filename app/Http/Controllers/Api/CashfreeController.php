<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\CashfreeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class CashfreeController extends Controller
{
    protected CashfreeService $cashfreeService;

    public function __construct(CashfreeService $cashfreeService)
    {
        $this->cashfreeService = $cashfreeService;
    }

    /**
     * Create Cashfree Order for an Invoice (Authenticated ERP Users)
     */
    public function createOrder(Request $request, int $invoiceId): JsonResponse
    {
        $invoice = Invoice::with(['customer', 'payments'])->findOrFail($invoiceId);

        if ($invoice->status === 'paid' || (float) $invoice->balance_due <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'This invoice is already fully paid.',
            ], 422);
        }

        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:1',
            'customer_phone' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email|max:100',
        ]);

        $amount = isset($validated['amount']) ? (float) $validated['amount'] : (float) $invoice->balance_due;

        try {
            $order = $this->cashfreeService->createOrder(
                $invoice,
                $amount,
                $request->user(),
                null,
                $validated['customer_phone'] ?? null,
                $validated['customer_email'] ?? null
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Cashfree order created successfully.',
                'order_id' => $order->order_id,
                'payment_session_id' => $order->payment_session_id,
                'order_amount' => (float) $order->order_amount,
                'order_currency' => $order->order_currency,
                'environment' => $this->cashfreeService->getEnvironment(),
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'total' => (float) $invoice->total,
                    'balance_due' => (float) $invoice->balance_due,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify payment status with Cashfree servers (Authenticated ERP Users)
     */
    public function verifyPayment(Request $request, int $invoiceId): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|string',
        ]);

        $orderId = $request->input('order_id');

        try {
            $result = $this->cashfreeService->verifyAndRecordPayment($orderId, $request->user());

            if ($result['paid']) {
                return response()->json([
                    'status' => 'success',
                    'paid' => true,
                    'message' => $result['already_recorded']
                        ? 'Payment was already verified and recorded in the system.'
                        : 'Payment successfully verified via Cashfree and recorded into Invoice!',
                    'order' => $result['order'],
                    'payment' => $result['payment'],
                    'invoice' => $result['invoice'],
                ]);
            }

            return response()->json([
                'status' => 'pending',
                'paid' => false,
                'message' => "Payment is not in PAID status yet (Current status: {$result['order_status']}).",
                'order' => $result['order'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test connection to Cashfree Gateway with given or stored credentials
     */
    public function testConnection(Request $request): JsonResponse
    {
        $appId = $request->input('cashfree_app_id');
        $secretKey = $request->input('cashfree_secret_key');
        $environment = $request->input('cashfree_environment');

        $result = $this->cashfreeService->testConnection($appId, $secretKey, $environment);

        return response()->json($result);
    }

    /**
     * Public endpoint: Create Cashfree Order for Customer Payment Portal
     */
    public function publicCreateOrder(Request $request, int $invoiceId): JsonResponse
    {
        $invoice = Invoice::with(['customer', 'payments'])->findOrFail($invoiceId);

        if ($invoice->status === 'paid' || (float) $invoice->balance_due <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'This invoice is already settled and fully paid.',
            ], 422);
        }

        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:1',
            'customer_phone' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email|max:100',
        ]);

        $amount = isset($validated['amount']) ? (float) $validated['amount'] : (float) $invoice->balance_due;

        try {
            $order = $this->cashfreeService->createOrder(
                $invoice,
                $amount,
                null,
                route('cashfree.return') . '?order_id={order_id}',
                $validated['customer_phone'] ?? null,
                $validated['customer_email'] ?? null
            );

            return response()->json([
                'status' => 'success',
                'order_id' => $order->order_id,
                'payment_session_id' => $order->payment_session_id,
                'order_amount' => (float) $order->order_amount,
                'order_currency' => $order->order_currency,
                'environment' => $this->cashfreeService->getEnvironment(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Public endpoint: Verify payment status after customer checkout
     */
    public function publicVerifyPayment(Request $request, int $invoiceId): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|string',
        ]);

        $orderId = $request->input('order_id');

        try {
            $result = $this->cashfreeService->verifyAndRecordPayment($orderId, null);

            return response()->json([
                'status' => 'success',
                'paid' => $result['paid'],
                'message' => $result['paid'] ? 'Payment verified successfully!' : 'Payment awaiting confirmation.',
                'order' => $result['order'],
                'invoice' => $result['invoice'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Webhook receiver for asynchronous Cashfree event notifications
     */
    public function webhook(Request $request): JsonResponse
    {
        $rawPayload = $request->getContent();
        $signature = $request->header('x-webhook-signature');
        $timestamp = $request->header('x-webhook-timestamp');

        Log::info('Cashfree Webhook Received', [
            'headers' => [
                'x-webhook-signature' => $signature,
                'x-webhook-timestamp' => $timestamp,
            ],
            'payload' => $request->all(),
        ]);

        // Verify signature if webhook secret is configured
        $webhookSecret = $this->cashfreeService->getWebhookSecret();
        if (!empty($webhookSecret)) {
            if (!$this->cashfreeService->verifyWebhookSignature($rawPayload, $signature, $timestamp)) {
                Log::warning('Cashfree Webhook Signature Verification Failed');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid webhook signature.',
                ], 401);
            }
        }

        $data = $request->all();
        $eventType = $data['type'] ?? ($data['event'] ?? '');

        // Handle Payment / Order Success
        // Cashfree formats:
        // Type 1: { "data": { "order": { "order_id": "..." }, "payment": { "payment_status": "SUCCESS" } }, "type": "PAYMENT_SUCCESS_WEBHOOK" }
        // Type 2: { "orderId": "...", "txStatus": "SUCCESS" }
        $orderId = $data['data']['order']['order_id'] ?? ($data['orderId'] ?? ($data['data']['order_id'] ?? null));

        if ($orderId) {
            try {
                $this->cashfreeService->verifyAndRecordPayment($orderId, null);
                Log::info("Cashfree Webhook reconciled order {$orderId}");
            } catch (\Throwable $e) {
                Log::error("Error processing Cashfree webhook for order {$orderId}: " . $e->getMessage());
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Webhook received and processed successfully.',
        ]);
    }
}
