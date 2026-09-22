<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - {{ $settings['company_name'] ?? 'Apex Enterprise' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --bg-base: #0a0f1d;
            --card-bg: rgba(17, 24, 39, 0.85);
            --card-border: rgba(99, 102, 241, 0.2);
            --cashfree-gradient: linear-gradient(135deg, #f59e0b 0%, #ec4899 50%, #8b5cf6 100%);
            --accent-emerald: #10b981;
            --accent-rose: #f43f5e;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-base);
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(236, 72, 153, 0.12) 0px, transparent 50%);
            background-attachment: fixed;
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px 16px;
        }

        .mono {
            font-family: 'JetBrains Mono', monospace;
        }

        .receipt-card {
            width: 100%;
            max-width: 580px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            backdrop-filter: blur(16px);
            border-radius: 20px;
            padding: 36px 32px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .receipt-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: var(--cashfree-gradient);
        }

        .status-icon-wrap {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            margin: 0 auto 18px auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .status-icon-success {
            background: rgba(16, 185, 129, 0.15);
            border: 2px solid rgba(16, 185, 129, 0.4);
            color: var(--accent-emerald);
        }
        .status-icon-failed {
            background: rgba(244, 63, 94, 0.15);
            border: 2px solid rgba(244, 63, 94, 0.4);
            color: var(--accent-rose);
        }

        .receipt-title {
            font-size: 22px;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 6px;
        }

        .receipt-sub {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 24px;
        }

        .amount-display {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
        }

        .amount-label {
            font-size: 11px;
            text-transform: uppercase;
            color: var(--text-muted);
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .amount-val {
            font-size: 32px;
            font-weight: 900;
            color: #ffffff;
            margin-top: 4px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px 20px;
            text-align: left;
            margin-bottom: 28px;
            font-size: 12.5px;
            background: rgba(15, 23, 42, 0.6);
            padding: 18px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 10.5px;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .detail-value {
            font-weight: 700;
            color: #e2e8f0;
            word-break: break-all;
        }

        .actions-row {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 20px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .btn-primary {
            background: var(--cashfree-gradient);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(236, 72, 153, 0.35);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(236, 72, 153, 0.5);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .footer-note {
            margin-top: 24px;
            font-size: 11px;
            color: #64748b;
        }
    </style>
</head>
<body>

    <div class="receipt-card">
        @if($success)
            <div class="status-icon-wrap status-icon-success">
                <i data-lucide="check-circle" style="width: 38px; height: 38px;"></i>
            </div>
            <h1 class="receipt-title">Payment Successful!</h1>
            <p class="receipt-sub">{{ $message }}</p>

            <div class="amount-display">
                <div class="amount-label">Amount Transacted</div>
                <div class="amount-val mono">
                    {{ $settings['currency_symbol'] ?? '₹' }}{{ number_format($payment ? $payment->amount : ($cfOrder ? $cfOrder->order_amount : 0), 2) }}
                </div>
            </div>

            <div class="details-grid">
                <div class="detail-item">
                    <span class="detail-label">Invoice Number</span>
                    <span class="detail-value mono">#{{ $invoice ? $invoice->invoice_number : 'N/A' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Customer Name</span>
                    <span class="detail-value">{{ $cfOrder ? $cfOrder->customer_name : ($invoice->customer->company_name ?? 'Customer') }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Cashfree Order ID</span>
                    <span class="detail-value mono">{{ $orderId }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">UTR / Reference</span>
                    <span class="detail-value mono">{{ $cfOrder->bank_reference ?? ($payment->reference_number ?? 'UTR-ACK-VERIFIED') }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Payment Mode</span>
                    <span class="detail-value" style="text-transform: uppercase;">{{ $cfOrder->payment_method ?? 'Cashfree Online' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Remaining Balance</span>
                    <span class="detail-value mono" style="color: {{ ($invoice && $invoice->balance_due > 0) ? '#fb7185' : '#34d399' }};">
                        {{ $settings['currency_symbol'] ?? '₹' }}{{ number_format($invoice ? $invoice->balance_due : 0, 2) }}
                    </span>
                </div>
            </div>

            <div class="actions-row">
                @if($invoice)
                <a href="/invoices/{{ $invoice->id }}/print" target="_blank" class="btn btn-primary">
                    <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Print GST Tax Invoice
                </a>
                @endif
                <a href="/" class="btn btn-secondary">
                    <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Return to ERP
                </a>
            </div>
        @else
            <div class="status-icon-wrap status-icon-failed">
                <i data-lucide="alert-triangle" style="width: 38px; height: 38px;"></i>
            </div>
            <h1 class="receipt-title">Payment Incomplete</h1>
            <p class="receipt-sub">{{ $message }}</p>

            <div class="amount-display">
                <div class="amount-label">Order Reference</div>
                <div class="detail-value mono" style="font-size: 16px; margin-top: 4px;">{{ $orderId }}</div>
            </div>

            <div class="actions-row">
                @if($invoice)
                <a href="/pay/invoice/{{ $invoice->id }}" class="btn btn-primary">
                    <i data-lucide="rotate-cw" style="width: 16px; height: 16px;"></i> Retry Payment
                </a>
                @endif
                <a href="/" class="btn btn-secondary">
                    <i data-lucide="home" style="width: 16px; height: 16px;"></i> Return Home
                </a>
            </div>
        @endif

        <div class="footer-note">
            Powered by Cashfree Payments &bull; Reference Token: {{ $orderId }}
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
