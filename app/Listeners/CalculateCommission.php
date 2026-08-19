<?php

namespace App\Listeners;

use App\Events\PaymentReceived;
use App\Services\CommissionService;

class CalculateCommission
{
    protected CommissionService $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    public function handle(PaymentReceived $event): void
    {
        $invoice = $event->payment->invoice;
        if ($invoice && $invoice->assigned_to) {
            $period = date('Y-m', strtotime($event->payment->payment_date));
            $this->commissionService->calculateUserCommission($invoice->assigned_to, $period);
        }
    }
}
