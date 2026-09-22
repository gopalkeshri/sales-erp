<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CashfreeOrder;
use App\Models\Invoice;
use App\Models\Setting;
use App\Services\CashfreeService;
use Illuminate\Http\Request;

class CashfreeWebController extends Controller
{
    protected CashfreeService $cashfreeService;

    public function __construct(CashfreeService $cashfreeService)
    {
        $this->cashfreeService = $cashfreeService;
    }

    /**
     * Display public customer payment portal for an invoice
     */
    public function showPublicPaymentPage(int $id)
    {
        $invoice = Invoice::with(['customer.contacts', 'contact', 'items.product', 'payments'])->findOrFail($id);
        $settings = Setting::getAllKeyValue();
        $isCashfreeReady = $this->cashfreeService->isEnabled() && !empty($this->cashfreeService->getAppId());
        $environment = $this->cashfreeService->getEnvironment();

        return view('erp.cashfree-pay', compact('invoice', 'settings', 'isCashfreeReady', 'environment'));
    }

    /**
     * Handle return redirect from Cashfree checkout
     */
    public function paymentReturn(Request $request)
    {
        $orderId = $request->query('order_id');
        $settings = Setting::getAllKeyValue();

        if (empty($orderId)) {
            return redirect()->route('login')->with('error', 'Missing Cashfree order reference.');
        }

        $cfOrder = CashfreeOrder::with('invoice.customer')->where('order_id', $orderId)->first();

        if (!$cfOrder) {
            return view('erp.cashfree-return', [
                'success' => false,
                'message' => 'Cashfree order not found in the system.',
                'orderId' => $orderId,
                'cfOrder' => null,
                'invoice' => null,
                'settings' => $settings,
            ]);
        }

        try {
            $result = $this->cashfreeService->verifyAndRecordPayment($orderId, null);
            $isPaid = $result['paid'];
            $invoice = $result['invoice'] ?? $cfOrder->invoice;
            $payment = $result['payment'] ?? null;

            return view('erp.cashfree-return', [
                'success' => $isPaid,
                'message' => $isPaid ? 'Payment successfully completed and verified!' : 'Payment was not completed or is pending.',
                'orderId' => $orderId,
                'cfOrder' => $result['order'] ?? $cfOrder,
                'invoice' => $invoice,
                'payment' => $payment,
                'settings' => $settings,
            ]);
        } catch (\Throwable $e) {
            return view('erp.cashfree-return', [
                'success' => false,
                'message' => 'Error verifying payment: ' . $e->getMessage(),
                'orderId' => $orderId,
                'cfOrder' => $cfOrder,
                'invoice' => $cfOrder->invoice,
                'payment' => null,
                'settings' => $settings,
            ]);
        }
    }
}
