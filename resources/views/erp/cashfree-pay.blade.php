<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Invoice #{{ $invoice->invoice_number }} - {{ $settings['company_name'] ?? 'Apex Enterprise Solutions' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
    <style>
        :root {
            --bg-base: #0a0f1d;
            --card-bg: rgba(17, 24, 39, 0.85);
            --card-border: rgba(99, 102, 241, 0.2);
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --cashfree-gradient: linear-gradient(135deg, #f59e0b 0%, #ec4899 50%, #8b5cf6 100%);
            --accent-emerald: #10b981;
            --accent-cyan: #06b6d4;
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
                radial-gradient(at 100% 100%, rgba(236, 72, 153, 0.12) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(6, 182, 212, 0.08) 0px, transparent 50%);
            background-attachment: fixed;
            color: var(--text-main);
            min-height: 100vh;
            padding: 30px 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .mono {
            font-family: 'JetBrains Mono', monospace;
        }

        .container {
            width: 100%;
            max-width: 980px;
        }

        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 24px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            backdrop-filter: blur(12px);
            border-radius: 16px;
            margin-bottom: 24px;
        }

        .company-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .company-logo {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: var(--cashfree-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 20px;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }

        .company-title {
            font-size: 16px;
            font-weight: 800;
            color: #ffffff;
        }

        .company-sub {
            font-size: 11px;
            color: var(--text-muted);
        }

        .secure-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #34d399;
            font-size: 11.5px;
            font-weight: 700;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 24px;
        }

        @media (max-width: 860px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            backdrop-filter: blur(16px);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding-bottom: 14px;
            margin-bottom: 18px;
        }

        .card-title {
            font-size: 15px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #ffffff;
        }

        .badge-status {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .badge-unpaid { background: rgba(244, 63, 94, 0.2); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.3); }
        .badge-partial { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        .badge-paid { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.02);
            padding: 14px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .info-label {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .info-value {
            font-size: 13px;
            font-weight: 700;
            color: #f1f5f9;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }

        .items-table th {
            text-align: left;
            padding: 10px 12px;
            color: var(--text-muted);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
        }

        .items-table td {
            padding: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: #e2e8f0;
        }

        .totals-box {
            background: rgba(15, 23, 42, 0.6);
            border-radius: 12px;
            padding: 16px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            font-size: 12.5px;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        .totals-row.grand {
            font-size: 16px;
            font-weight: 800;
            color: #ffffff;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 10px;
            margin-top: 10px;
        }

        .totals-row.balance {
            font-size: 18px;
            font-weight: 900;
            color: #f43f5e;
            border-top: 1px dashed rgba(244, 63, 94, 0.3);
            padding-top: 10px;
            margin-top: 6px;
        }

        /* Payment Form Box */
        .pay-amount-banner {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.15) 0%, rgba(236, 72, 153, 0.15) 100%);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 14px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }

        .pay-amount-label {
            font-size: 11.5px;
            text-transform: uppercase;
            color: var(--text-muted);
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .pay-amount-val {
            font-size: 32px;
            font-weight: 900;
            color: #ffffff;
            margin: 6px 0 4px 0;
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-label {
            display: block;
            font-size: 11.5px;
            font-weight: 700;
            color: #cbd5e1;
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 8px;
            color: #ffffff;
            font-size: 13px;
            font-family: inherit;
            outline: none;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
        }

        .btn-pay {
            width: 100%;
            padding: 14px 20px;
            border-radius: 10px;
            background: var(--cashfree-gradient);
            border: none;
            color: #ffffff;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(236, 72, 153, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.2s;
            margin-top: 18px;
        }

        .btn-pay:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(236, 72, 153, 0.45);
        }

        .btn-pay:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .payment-methods-strip {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 14px;
            margin-top: 18px;
            padding-top: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            flex-wrap: wrap;
        }

        .pm-badge {
            font-size: 10.5px;
            color: var(--text-muted);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .alert-box {
            padding: 14px;
            border-radius: 8px;
            font-size: 12.5px;
            margin-bottom: 16px;
            display: none;
        }
        .alert-error {
            background: rgba(244, 63, 94, 0.15);
            border: 1px solid rgba(244, 63, 94, 0.3);
            color: #fda4af;
        }
        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #6ee7b7;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 11px;
            color: var(--text-muted);
        }

        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Header Bar -->
        <div class="header-bar">
            <div class="company-brand">
                <div class="company-logo">
                    {{ strtoupper(substr($settings['company_name'] ?? 'A', 0, 1)) }}
                </div>
                <div>
                    <div class="company-title">{{ $settings['company_name'] ?? 'Apex Enterprise Solutions' }}</div>
                    <div class="company-sub">Official Online Invoice Payment Portal</div>
                </div>
            </div>
            <div class="secure-badge">
                <i data-lucide="shield-check" style="width: 16px; height: 16px;"></i>
                <span>256-Bit SSL Encrypted Checkout</span>
            </div>
        </div>

        <div class="grid">
            <!-- Left: Invoice Breakdown -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <i data-lucide="file-text" style="color: var(--accent-cyan); width: 18px; height: 18px;"></i>
                        Invoice #{{ $invoice->invoice_number }}
                    </div>
                    <div>
                        @if($invoice->balance_due <= 0)
                            <span class="badge-status badge-paid">Paid in Full</span>
                        @elseif($invoice->amount_paid > 0)
                            <span class="badge-status badge-partial">Partially Paid</span>
                        @else
                            <span class="badge-status badge-unpaid">Payment Due</span>
                        @endif
                    </div>
                </div>

                <!-- Customer Details Grid -->
                <div class="info-grid">
                    <div>
                        <div class="info-label">Billed To</div>
                        <div class="info-value">{{ $invoice->customer->company_name ?? 'Customer' }}</div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                            GSTIN: <span class="mono">{{ $invoice->customer->tax_number ?? 'Unregistered' }}</span>
                        </div>
                    </div>
                    <div>
                        <div class="info-label">Invoice Dates</div>
                        <div class="info-value">Issued: {{ $invoice->invoice_date ? $invoice->invoice_date->format('d M, Y') : now()->format('d M, Y') }}</div>
                        <div style="font-size: 11px; color: #fb7185; margin-top: 2px;">
                            Due: {{ $invoice->due_date ? $invoice->due_date->format('d M, Y') : 'Due on receipt' }}
                        </div>
                    </div>
                </div>

                <!-- Line Items Table -->
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Item & Description</th>
                            <th style="text-align: center;">Qty</th>
                            <th style="text-align: right;">Rate</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                        <tr>
                            <td>
                                <strong style="color:#ffffff;">{{ $item->product->name ?? 'Service / Product' }}</strong>
                                <div style="font-size: 10.5px; color: var(--text-muted);">HSN: {{ $item->hsn_code ?? '9983' }} | Tax: {{ $item->tax_rate }}%</div>
                            </td>
                            <td style="text-align: center;">{{ $item->quantity }}</td>
                            <td style="text-align: right;" class="mono">{{ $settings['currency_symbol'] ?? '₹' }}{{ number_format($item->unit_price, 2) }}</td>
                            <td style="text-align: right; font-weight:700;" class="mono">{{ $settings['currency_symbol'] ?? '₹' }}{{ number_format($item->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Totals Breakdown -->
                <div class="totals-box">
                    <div class="totals-row">
                        <span>Taxable Subtotal:</span>
                        <span class="mono">{{ $settings['currency_symbol'] ?? '₹' }}{{ number_format($invoice->subtotal - $invoice->discount_total, 2) }}</span>
                    </div>
                    <div class="totals-row">
                        <span>Applicable GST:</span>
                        <span class="mono">{{ $settings['currency_symbol'] ?? '₹' }}{{ number_format($invoice->tax_total, 2) }}</span>
                    </div>
                    <div class="totals-row grand">
                        <span>Invoice Grand Total:</span>
                        <span class="mono">{{ $settings['currency_symbol'] ?? '₹' }}{{ number_format($invoice->total, 2) }}</span>
                    </div>
                    @if($invoice->amount_paid > 0)
                    <div class="totals-row" style="color: #34d399; margin-top: 6px;">
                        <span>Amount Paid:</span>
                        <span class="mono">{{ $settings['currency_symbol'] ?? '₹' }}{{ number_format($invoice->amount_paid, 2) }}</span>
                    </div>
                    @endif
                    <div class="totals-row balance">
                        <span>Balance Outstanding:</span>
                        <span class="mono">{{ $settings['currency_symbol'] ?? '₹' }}{{ number_format($invoice->balance_due, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Right: Payment Action Card -->
            <div class="card" style="align-self: start;">
                <div class="card-header">
                    <div class="card-title">
                        <i data-lucide="credit-card" style="color: #f59e0b; width: 18px; height: 18px;"></i>
                        Pay via Cashfree
                    </div>
                    <span style="font-size: 10px; color: #818cf8; background: rgba(99,102,241,0.15); padding: 2px 8px; border-radius: 4px; font-weight: 700;">
                        {{ strtoupper($environment) }}
                    </span>
                </div>

                <div id="alertBox" class="alert-box"></div>

                @if($invoice->balance_due <= 0)
                    <div style="text-align: center; padding: 40px 10px;">
                        <i data-lucide="check-circle-2" style="width: 56px; height: 56px; color: #10b981; margin-bottom: 12px;"></i>
                        <h3 style="font-size: 18px; font-weight: 800; color: #ffffff; margin-bottom: 6px;">Invoice Fully Settled!</h3>
                        <p style="font-size: 12.5px; color: var(--text-muted); margin-bottom: 20px;">There is zero outstanding balance due on this tax invoice.</p>
                        <a href="/invoices/{{ $invoice->id }}/print" target="_blank" style="display: inline-block; padding: 10px 20px; background: rgba(255,255,255,0.1); color: #fff; text-decoration: none; border-radius: 8px; font-size: 13px; font-weight: 700;">
                            View Printable Invoice Receipt
                        </a>
                    </div>
                @else
                    <div class="pay-amount-banner">
                        <div class="pay-amount-label">Amount Payable Now</div>
                        <div class="pay-amount-val mono" id="displayAmount">
                            {{ $settings['currency_symbol'] ?? '₹' }}{{ number_format($invoice->balance_due, 2) }}
                        </div>
                        <div style="font-size: 11px; color: #a5b4fc;">Supports UPI, NetBanking, Cards & Wallets</div>
                    </div>

                    <form id="cashfreeForm" onsubmit="handleCustomerPayment(event)">
                        <input type="hidden" id="invoiceId" value="{{ $invoice->id }}">
                        
                        <div class="form-group">
                            <label class="form-label">Payment Amount ({{ $settings['currency_symbol'] ?? '₹' }})</label>
                            <input type="number" step="0.01" min="1" max="{{ $invoice->balance_due }}" id="payAmountInput" class="form-control mono" value="{{ $invoice->balance_due }}" required oninput="updateDisplayAmount(this.value)">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Customer Mobile Number (for OTP & Receipt)</label>
                            <input type="tel" id="customerPhone" class="form-control mono" placeholder="10-digit mobile number" value="{{ $invoice->customer->phone ?? '' }}" required pattern="[0-9]{10}">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Customer Email Address</label>
                            <input type="email" id="customerEmail" class="form-control" placeholder="billing@example.com" value="{{ $invoice->customer->email ?? '' }}" required>
                        </div>

                        <button type="submit" id="btnPayNow" class="btn-pay">
                            <i data-lucide="lock" style="width: 18px; height: 18px;"></i>
                            <span id="btnPayText">Proceed to Cashfree Checkout</span>
                        </button>
                    </form>

                    <div class="payment-methods-strip">
                        <span class="pm-badge"><i data-lucide="smartphone" style="width:13px; height:13px; color:#10b981;"></i> UPI / QR</span>
                        <span class="pm-badge"><i data-lucide="credit-card" style="width:13px; height:13px; color:#f59e0b;"></i> Cards</span>
                        <span class="pm-badge"><i data-lucide="landmark" style="width:13px; height:13px; color:#06b6d4;"></i> NetBanking</span>
                        <span class="pm-badge"><i data-lucide="wallet" style="width:13px; height:13px; color:#c084fc;"></i> Wallets</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="footer">
            <div>Powered by <strong>Cashfree Payments Gateway</strong> &bull; Secured with end-to-end encryption.</div>
            <div style="margin-top: 4px; color: #64748b;">{{ $settings['company_name'] ?? 'Apex Enterprise Solutions Pvt. Ltd.' }} &bull; GSTIN: {{ $settings['tax_id'] ?? 'N/A' }}</div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        const cfEnvironment = "{{ $environment }}";
        const cashfree = Cashfree({
            mode: cfEnvironment.toLowerCase() === 'production' ? 'production' : 'sandbox'
        });

        function updateDisplayAmount(val) {
            const num = parseFloat(val);
            if (!isNaN(num) && num > 0) {
                document.getElementById('displayAmount').innerText = "{{ $settings['currency_symbol'] ?? '₹' }}" + num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        }

        function showAlert(msg, type = 'error') {
            const box = document.getElementById('alertBox');
            box.className = 'alert-box alert-' + type;
            box.innerText = msg;
            box.style.display = 'block';
        }

        function hideAlert() {
            document.getElementById('alertBox').style.display = 'none';
        }

        async function handleCustomerPayment(e) {
            e.preventDefault();
            hideAlert();

            const invoiceId = document.getElementById('invoiceId').value;
            const amount = parseFloat(document.getElementById('payAmountInput').value);
            const phone = document.getElementById('customerPhone').value.trim();
            const email = document.getElementById('customerEmail').value.trim();

            if (isNaN(amount) || amount <= 0) {
                showAlert('Please enter a valid payment amount.');
                return;
            }

            const btn = document.getElementById('btnPayNow');
            const btnText = document.getElementById('btnPayText');
            btn.disabled = true;
            btnText.innerHTML = '<span class="spinner"></span> Connecting to Cashfree...';

            try {
                // 1. Create order on server
                const res = await fetch(`/api/public/invoices/${invoiceId}/cashfree/order`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        amount: amount,
                        customer_phone: phone,
                        customer_email: email
                    })
                });

                const data = await res.json();

                if (!res.ok || data.status !== 'success') {
                    throw new Error(data.message || 'Failed to initialize Cashfree checkout session.');
                }

                btnText.innerHTML = '<span class="spinner"></span> Launching Payment Modal...';

                // 2. Open Cashfree Checkout Modal
                const checkoutOptions = {
                    paymentSessionId: data.payment_session_id,
                    redirectTarget: "_modal"
                };

                cashfree.checkout(checkoutOptions).then(async (result) => {
                    if (result.error) {
                        btn.disabled = false;
                        btnText.innerText = 'Proceed to Cashfree Checkout';
                        showAlert('Checkout was dismissed or encountered an error: ' + (result.error.message || 'Payment cancelled.'));
                        return;
                    }

                    if (result.redirect) {
                        // In case of 3DS bank redirect
                        btnText.innerText = 'Redirecting to your bank...';
                        return;
                    }

                    if (result.paymentDetails) {
                        // Verify with server
                        btnText.innerHTML = '<span class="spinner"></span> Verifying settlement...';
                        const verifyRes = await fetch(`/api/public/invoices/${invoiceId}/cashfree/verify`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                order_id: data.order_id
                            })
                        });

                        const verifyData = await verifyRes.json();
                        if (verifyData.paid) {
                            window.location.href = `/cashfree/return?order_id=${data.order_id}`;
                        } else {
                            showAlert('Payment status: ' + (verifyData.message || 'Pending confirmation.'), 'info');
                            btn.disabled = false;
                            btnText.innerText = 'Proceed to Cashfree Checkout';
                        }
                    }
                });

            } catch (err) {
                console.error(err);
                showAlert(err.message || 'Network error occurred while connecting to payment gateway.');
                btn.disabled = false;
                btnText.innerText = 'Proceed to Cashfree Checkout';
            }
        }
    </script>
</body>
</html>
