<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commercial Quotation & Proposal - {{ $quote->quote_number }} - {{ $company['name'] }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 10mm 12mm 10mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            color: #0f172a;
            background-color: #f1f5f9;
            margin: 0;
            padding: 20px 0;
            font-size: 11px;
            line-height: 1.35;
        }

        .no-print-bar {
            max-width: 210mm;
            margin: 0 auto 16px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #0f172a;
            color: #ffffff;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #4f46e5;
            color: #ffffff;
        }
        .btn-primary:hover {
            background: #4338ca;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
        }
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        .quote-paper {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #ffffff;
            padding: 12mm 12mm;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            position: relative;
        }

        /* Bordered Box Utilities */
        .header-container {
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 10px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .company-title {
            font-size: 20px;
            font-weight: 900;
            color: #4338ca;
            margin: 0 0 2px 0;
            letter-spacing: -0.2px;
        }

        .company-tagline {
            font-size: 10.5px;
            color: #64748b;
            margin: 0 0 4px 0;
        }

        .company-meta {
            font-size: 10.5px;
            color: #334155;
            margin: 1px 0;
        }

        .quote-badge-box {
            text-align: right;
        }

        .quotation-badge {
            display: inline-block;
            background: #4f46e5;
            color: #ffffff;
            font-weight: 900;
            font-size: 13px;
            padding: 4px 14px;
            border-radius: 4px;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .quote-meta-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 12px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px 12px;
            margin-bottom: 12px;
        }

        .meta-col-title {
            font-size: 9.5px;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }

        .meta-party-name {
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 2px;
        }

        /* Items Table with Perfect Multi-Page Support */
        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            page-break-inside: auto;
            border: 1px solid #cbd5e1;
        }

        table.items-table thead {
            display: table-header-group;
        }

        table.items-table tfoot {
            display: table-footer-group;
        }

        table.items-table tr {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        table.items-table th {
            background: #0f172a !important;
            color: #ffffff !important;
            font-size: 9.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 6px 5px;
            border: 1px solid #334155;
        }

        table.items-table td {
            padding: 5px 5px;
            font-size: 10.5px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        table.items-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .mono {
            font-family: 'JetBrains Mono', monospace;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: 700; }
        .font-extrabold { font-weight: 800; }

        /* Summary & Settlement Section (Protected against awkward breaks) */
        .avoid-page-break {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: 1.25fr 1fr;
            gap: 12px;
            margin-bottom: 10px;
        }

        table.hsn-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
        }

        table.hsn-table th {
            background: #e2e8f0;
            color: #1e293b;
            font-weight: 800;
            padding: 4px 5px;
            border: 1px solid #cbd5e1;
        }

        table.hsn-table td {
            padding: 3px 5px;
            border: 1px solid #e2e8f0;
        }

        .totals-card {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px 12px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            margin-bottom: 3px;
            color: #334155;
        }

        .totals-row.grand-total {
            border-top: 2px solid #0f172a;
            padding-top: 5px;
            margin-top: 5px;
            font-size: 14px;
            font-weight: 900;
            color: #0f172a;
        }

        .words-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px 8px;
            margin-top: 8px;
        }

        .settlement-grid {
            display: grid;
            grid-template-columns: 1.3fr 1fr;
            gap: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px 12px;
            background: #f8fafc;
            margin-bottom: 8px;
        }

        .signatures-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px 14px;
            background: #f8fafc;
            margin-bottom: 8px;
        }

        .terms-bar {
            font-size: 9px;
            color: #64748b;
            border-top: 1px dashed #cbd5e1;
            padding-top: 6px;
            display: flex;
            justify-content: space-between;
        }

        /* PRINT STYLES OVERRIDES */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }

            .no-print-bar {
                display: none !important;
            }

            .quote-paper {
                width: 100% !important;
                min-height: auto !important;
                padding: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- Top Action Bar (Hidden in Print) -->
    <div class="no-print-bar">
        <div style="display:flex; align-items:center; gap:10px;">
            <span style="font-size: 14px; font-weight: 800;">Indian GST Pricing Proposal & Quotation</span>
            <span style="font-size: 12px; color: #94a3b8;">{{ $quote->quote_number }} ({{ $quote->items->count() }} Line Items)</span>
        </div>
        <div style="display:flex; gap:8px;">
            <button type="button" class="btn btn-secondary" onclick="window.close()">Close Window</button>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                Print / Save PDF
            </button>
        </div>
    </div>

    <!-- Printable Quote Paper -->
    <div class="quote-paper">
        <!-- 1. Header with Supplier Information -->
        <div class="header-container">
            <div>
                <h1 class="company-title">{{ $company['name'] }}</h1>
                <div class="company-tagline">{{ $company['tagline'] }}</div>
                <div class="company-meta">{{ $company['address'] }}, {{ $company['city'] }}, {{ $company['state'] }} - {{ $company['postal_code'] }}, {{ $company['country'] }}</div>
                <div class="company-meta">
                    <strong>GSTIN:</strong> <span class="mono font-extrabold" style="color:#4f46e5;">{{ $company['gstin'] }}</span> | 
                    <strong>PAN:</strong> <span class="mono font-bold">{{ $company['pan'] }}</span> | 
                    <strong>State Code:</strong> {{ $company['state_code'] }} ({{ $company['state'] }})
                </div>
                <div class="company-meta"><strong>Email:</strong> {{ $company['email'] }} | <strong>Phone:</strong> {{ $company['phone'] }}</div>
            </div>
            <div class="quote-badge-box">
                <div class="quotation-badge">PRICE QUOTATION</div>
                <div style="font-size: 9.5px; color: #64748b; font-weight: 700; text-transform: uppercase;">Commercial Proposal</div>
                <div style="font-size: 14px; font-weight: 900; color: #0f172a; margin-top: 3px;" class="mono">#{{ $quote->quote_number }}</div>
                <div style="font-size: 11px; color: #334155; margin-top: 1px;">Date: <strong>{{ $quote->created_at ? $quote->created_at->format('d/m/Y') : now()->format('d/m/Y') }}</strong></div>
                <div style="font-size: 11px; color: #4f46e5; margin-top: 1px;">Valid Until: <strong>{{ $quote->valid_until ? $quote->valid_until->format('d/m/Y') : '30 Days from Issue' }}</strong></div>
            </div>
        </div>

        <!-- 2. Buyer (Prepared For) & Tax Meta Grid -->
        <div class="quote-meta-grid">
            <div>
                <div class="meta-col-title">Proposal Prepared For:</div>
                <div class="meta-party-name">{{ $quote->customer->company_name ?? 'Client Entity' }}</div>
                @if($quote->contact)
                    <div style="font-size: 11px; font-weight: 700; color: #4338ca; margin-bottom: 2px;">
                        Attn: {{ $quote->contact->first_name }} {{ $quote->contact->last_name }} ({{ $quote->contact->title ?: 'Procurement' }})
                    </div>
                @endif
                <div style="color: #475569; font-size: 10.5px;">{{ $quote->customer->billing_street ?? ($quote->customer->address_street ?? 'Registered Business Address') }}</div>
                <div style="color: #475569; font-size: 10.5px;">{{ $quote->customer->billing_city ?? ($quote->customer->address_city ?? '') }}, {{ $buyer['state'] }} - {{ $quote->customer->postal_code ?? '' }}</div>
                <div style="margin-top: 4px; font-size: 10.5px;">
                    <strong>GSTIN / UIN:</strong> <span class="mono font-extrabold" style="color:#4f46e5;">{{ $quote->customer->gst_number ?? 'Unregistered Entity' }}</span>
                </div>
                <div style="font-size: 10.5px; color: #475569;">
                    <strong>PAN:</strong> <span class="mono">{{ $quote->customer->pan_number ?? 'N/A' }}</span> | 
                    <strong>State Code:</strong> {{ $buyer['state_code'] }} ({{ $buyer['state'] }})
                </div>
            </div>
            <div style="border-left: 1px solid #cbd5e1; padding-left: 12px;">
                <div class="meta-col-title">Tax & Proposal Parameters:</div>
                <div style="margin-bottom: 2px;"><strong>Place of Supply:</strong> <span class="font-bold">{{ $buyer['state'] }} ({{ $buyer['state_code'] }})</span></div>
                <div style="margin-bottom: 2px;">
                    <strong>GST Treatment:</strong> 
                    <span class="font-bold" style="color: {{ $buyer['is_intra_state'] ? '#16a34a' : '#9333ea' }};">
                        {{ $buyer['is_intra_state'] ? 'Intra-State (CGST + SGST)' : 'Inter-State (IGST)' }}
                    </span>
                </div>
                <div style="margin-bottom: 2px;"><strong>Assigned Executive:</strong> {{ $quote->assignedUser ? $quote->assignedUser->name : 'Commercial Sales Team' }}</div>
                <div style="margin-bottom: 2px;"><strong>Quote Status:</strong> <span class="font-extrabold" style="text-transform:uppercase; color:#4f46e5;">{{ $quote->status }}</span></div>
                @if($quote->opportunity)
                    <div><strong>Related Deal / Opportunity:</strong> {{ $quote->opportunity->title }}</div>
                @endif
            </div>
        </div>

        <!-- 3. Multi-Page Line Items Table (Supports 1 to 30+ items without clipping) -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 25px;" class="text-center">#</th>
                    <th class="text-left">Description of Goods / Services</th>
                    <th style="width: 60px;" class="text-center">HSN/SAC</th>
                    <th style="width: 50px;" class="text-center">Qty</th>
                    <th style="width: 65px;" class="text-right">Rate</th>
                    <th style="width: 40px;" class="text-right">Disc</th>
                    <th style="width: 75px;" class="text-right">Taxable ({{ $company['currency_symbol'] }})</th>
                    @if($buyer['is_intra_state'])
                        <th style="width: 70px;" class="text-right">CGST</th>
                        <th style="width: 70px;" class="text-right">SGST</th>
                    @else
                        <th style="width: 90px;" class="text-right" colspan="2">IGST</th>
                    @endif
                    <th style="width: 80px;" class="text-right">Total ({{ $company['currency_symbol'] }})</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quote->items as $idx => $it)
                    @php
                        $hsn = $it->hsn_code ?: ($it->product->hsn_code ?? 'N/A');
                        $desc = $it->description ?: ($it->product->name ?? 'Product / Service Item');
                        $taxable = (float)($it->taxable_value ?: ($it->quantity * $it->unit_price * (1 - ($it->discount_percent/100))));
                        $rate = (float)($it->tax_rate ?: 18);
                    @endphp
                    <tr>
                        <td class="text-center" style="color: #64748b;">{{ $idx + 1 }}</td>
                        <td>
                            <div class="font-bold" style="color: #0f172a;">{{ $desc }}</div>
                            @if($it->product && $it->product->sku)
                                <div style="font-size: 9px; color: #64748b;" class="mono">SKU: {{ $it->product->sku }}</div>
                            @endif
                        </td>
                        <td class="text-center mono font-bold" style="color: #334155;">{{ $hsn }}</td>
                        <td class="text-center">{{ $it->quantity }} {{ $it->product->unit ?? 'units' }}</td>
                        <td class="text-right">{{ number_format($it->unit_price, 2) }}</td>
                        <td class="text-right">{{ (float)$it->discount_percent }}%</td>
                        <td class="text-right font-bold">{{ number_format($taxable, 2) }}</td>
                        @if($buyer['is_intra_state'])
                            @php
                                $halfRate = $rate / 2;
                                $cgstAmt = $it->cgst_amount !== null ? (float)$it->cgst_amount : ($taxable * ($halfRate / 100));
                                $sgstAmt = $it->sgst_amount !== null ? (float)$it->sgst_amount : ($taxable * ($halfRate / 100));
                            @endphp
                            <td class="text-right" style="font-size: 10px;">{{ $halfRate }}% ({{ number_format($cgstAmt, 2) }})</td>
                            <td class="text-right" style="font-size: 10px;">{{ $halfRate }}% ({{ number_format($sgstAmt, 2) }})</td>
                        @else
                            @php
                                $igstAmt = $it->igst_amount !== null ? (float)$it->igst_amount : ($taxable * ($rate / 100));
                            @endphp
                            <td class="text-right" style="font-size: 10px;" colspan="2">{{ $rate }}% ({{ number_format($igstAmt, 2) }})</td>
                        @endif
                        <td class="text-right font-extrabold" style="color: #0f172a;">{{ number_format($it->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: #f1f5f9; font-weight: 800;">
                    <td colspan="3" class="text-right">Total ({{ $quote->items->count() }} Line Items):</td>
                    <td class="text-center">{{ $quote->items->sum('quantity') }}</td>
                    <td colspan="2"></td>
                    <td class="text-right">{{ number_format($quote->subtotal - $quote->discount_total, 2) }}</td>
                    @if($buyer['is_intra_state'])
                        <td class="text-right">{{ number_format($quote->cgst_total ?: ($quote->tax_total / 2), 2) }}</td>
                        <td class="text-right">{{ number_format($quote->sgst_total ?: ($quote->tax_total / 2), 2) }}</td>
                    @else
                        <td class="text-right" colspan="2">{{ number_format($quote->igst_total ?: $quote->tax_total, 2) }}</td>
                    @endif
                    <td class="text-right">{{ number_format($quote->total, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- 4. Tax Summary Breakdown & Totals (Protected against Page Break Split) -->
        <div class="avoid-page-break">
            <div class="summary-grid">
                <!-- HSN Summary Table -->
                <div>
                    <div class="meta-col-title">HSN / SAC Tax Breakdown:</div>
                    <table class="hsn-table">
                        <thead>
                            <tr>
                                <th>HSN/SAC</th>
                                <th class="text-right">Taxable</th>
                                @if($buyer['is_intra_state'])
                                    <th class="text-right">CGST</th>
                                    <th class="text-right">SGST</th>
                                @else
                                    <th class="text-right">IGST</th>
                                @endif
                                <th class="text-right">Total Tax</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hsn_summary as $hsn)
                                <tr>
                                    <td class="mono font-bold">{{ $hsn['hsn_code'] }}</td>
                                    <td class="text-right">{{ number_format($hsn['taxable_value'], 2) }}</td>
                                    @if($buyer['is_intra_state'])
                                        <td class="text-right">{{ $hsn['cgst_rate'] }}% ({{ number_format($hsn['cgst_amount'], 2) }})</td>
                                        <td class="text-right">{{ $hsn['sgst_rate'] }}% ({{ number_format($hsn['sgst_amount'], 2) }})</td>
                                    @else
                                        <td class="text-right">{{ $hsn['igst_rate'] }}% ({{ number_format($hsn['igst_amount'], 2) }})</td>
                                    @endif
                                    <td class="text-right font-bold">{{ number_format($hsn['total_tax'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Amount in Words -->
                    <div class="words-card">
                        <div class="meta-col-title">Total Proposal Value in Words:</div>
                        <div style="font-size: 11px; font-weight: 800; color: #4338ca;">{{ $amount_in_words }}</div>
                    </div>
                </div>

                <!-- Grand Totals Box -->
                <div class="totals-card">
                    <div class="totals-row">
                        <span>Taxable Value:</span>
                        <strong class="mono">{{ $company['currency_symbol'] }}{{ number_format($quote->subtotal - $quote->discount_total, 2) }}</strong>
                    </div>
                    @if($buyer['is_intra_state'])
                        <div class="totals-row">
                            <span>Central GST (CGST):</span>
                            <strong class="mono">{{ $company['currency_symbol'] }}{{ number_format($quote->cgst_total ?: ($quote->tax_total / 2), 2) }}</strong>
                        </div>
                        <div class="totals-row">
                            <span>State GST (SGST):</span>
                            <strong class="mono">{{ $company['currency_symbol'] }}{{ number_format($quote->sgst_total ?: ($quote->tax_total / 2), 2) }}</strong>
                        </div>
                    @else
                        <div class="totals-row">
                            <span>Integrated GST (IGST):</span>
                            <strong class="mono">{{ $company['currency_symbol'] }}{{ number_format($quote->igst_total ?: $quote->tax_total, 2) }}</strong>
                        </div>
                    @endif
                    <div class="totals-row grand-total">
                        <span>Grand Proposal Total:</span>
                        <span class="mono">{{ $company['currency_symbol'] }}{{ number_format($quote->total, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- 5. Banking Details & Terms -->
            <div class="settlement-grid">
                <div style="font-size: 10px;">
                    <div class="meta-col-title" style="color: #0f172a;">Direct Settlement & Advance Remittance Details:</div>
                    <div><strong>Bank:</strong> {{ $company['bank_name'] }}</div>
                    <div><strong>A/C No:</strong> <span class="mono font-extrabold">{{ $company['bank_account_no'] }}</span> | <strong>IFSC:</strong> <span class="mono font-bold" style="color:#4f46e5;">{{ $company['bank_ifsc'] }}</span></div>
                    <div><strong>Branch:</strong> {{ $company['bank_branch'] }}</div>
                    <div style="margin-top: 2px;"><strong>UPI VPA:</strong> <code class="mono" style="color: #4338ca; font-weight: 700;">{{ $company['upi_id'] }}</code></div>
                </div>
                <div style="font-size: 10px; color: #475569;">
                    <div class="meta-col-title" style="color: #0f172a;">Proposal Notes & Validity:</div>
                    <div>{{ $quote->notes ?: 'Quotation valid for 30 calendar days from issue. Prices subject to applicable Indian GST tax rates at time of dispatch.' }}</div>
                </div>
            </div>

            <!-- 6. Dual Signatures (Authorized + Client Acceptance) -->
            <div class="signatures-grid">
                <div>
                    <div style="font-size: 9.5px; color: #64748b; text-transform: uppercase;">For {{ $company['name'] }}</div>
                    <div style="font-size: 10.5px; font-weight: 800; color: #0f172a; border-top: 1px solid #cbd5e1; padding-top: 4px; margin-top: 28px;">
                        Authorized Sales Representative
                    </div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 9.5px; color: #64748b; text-transform: uppercase;">Client Acceptance Signature</div>
                    <div style="font-size: 10.5px; font-weight: 800; color: #0f172a; border-top: 1px solid #cbd5e1; padding-top: 4px; margin-top: 28px;">
                        Authorized Buyer Signature & Company Seal
                    </div>
                </div>
            </div>

            <!-- 7. Legal Terms -->
            <div class="terms-bar">
                <span><strong>Proposal Declaration:</strong> Prices valid as per quoted terms and subject to stock availability.</span>
                <span><strong>Jurisdiction:</strong> Subject to {{ $company['city'] }} Jurisdiction.</span>
            </div>
        </div>
    </div>

    @if(request()->query('autoprint') == '1')
    <script>
        window.addEventListener('load', () => {
            setTimeout(() => {
                window.print();
            }, 400);
        });
    </script>
    @endif
</body>
</html>
