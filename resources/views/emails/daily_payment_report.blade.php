<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Payment & Settlement Report - Maya Sree Fashion</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #FAF8F5;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1E293B;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            background-color: #FAF8F5;
            padding: 30px 15px;
            box-sizing: border-box;
        }
        .container {
            max-width: 660px;
            margin: 0 auto;
            background: #FFFFFF;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #E8DDD3;
            box-shadow: 0 4px 16px rgba(91, 22, 58, 0.05);
        }
        .header {
            background: linear-gradient(135deg, #5B163A 0%, #3D0E26 100%);
            color: #FFFFFF;
            padding: 28px 24px;
            text-align: center;
        }
        .brand-title {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 24px;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 0;
            color: #FAF8F5;
            font-weight: 700;
        }
        .brand-subtitle {
            font-size: 11px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #D4AF37;
            margin-top: 4px;
            font-weight: 600;
        }
        .alert-bar {
            background-color: #FAF0E6;
            border-bottom: 2px solid #D4AF37;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .alert-text {
            color: #5B163A;
            font-weight: 700;
            font-size: 14px;
            margin: 0;
        }
        .content {
            padding: 24px;
        }
        .section-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748B;
            font-weight: 700;
            margin: 24px 0 12px 0;
            border-bottom: 1px solid #F1F5F9;
            padding-bottom: 6px;
        }
        .section-title:first-child {
            margin-top: 0;
        }
        /* KPI Cards Grid */
        .kpi-grid {
            display: table;
            width: 100%;
            table-layout: fixed;
            margin-bottom: 20px;
        }
        .kpi-row {
            display: table-row;
        }
        .kpi-cell {
            display: table-cell;
            width: 50%;
            padding: 6px;
            box-sizing: border-box;
            vertical-align: top;
        }
        .kpi-box {
            background: #FAF8F5;
            border: 1px solid #E8DDD3;
            border-radius: 8px;
            padding: 14px;
            text-align: left;
        }
        .kpi-label {
            font-size: 11px;
            color: #64748B;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kpi-value {
            font-size: 20px;
            font-weight: 700;
            color: #5B163A;
            margin-top: 4px;
        }
        .kpi-value-green {
            color: #166534;
        }
        .kpi-sub {
            font-size: 11px;
            color: #64748B;
            margin-top: 4px;
        }
        /* Reconciliation Table */
        .recon-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            background: #FAF8F5;
            border: 1px solid #E8DDD3;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .recon-table td {
            padding: 10px 14px;
            border-bottom: 1px solid #E8DDD3;
        }
        .recon-table tr:last-child td {
            border-bottom: none;
        }
        .recon-label {
            color: #64748B;
            font-weight: 500;
        }
        .recon-value {
            text-align: right;
            font-weight: 600;
            color: #1E293B;
        }
        .recon-highlight td {
            background: #FFFDF7;
            font-weight: 700;
            font-size: 14px;
            color: #5B163A;
            border-top: 2px solid #5B163A;
        }
        /* Payment Mode Table */
        .methods-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 20px;
        }
        .methods-table th {
            background-color: #FAF8F5;
            color: #64748B;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #E8DDD3;
        }
        .methods-table td {
            padding: 9px 12px;
            border-bottom: 1px solid #F1F5F9;
        }
        /* Itemized Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #FAF8F5;
            color: #64748B;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            padding: 8px 10px;
            text-align: left;
            border-bottom: 1px solid #E8DDD3;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #F1F5F9;
            vertical-align: middle;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-paid {
            background: #DCFCE7;
            color: #166534;
        }
        .badge-pending {
            background: #FEF3C7;
            color: #92400E;
        }
        .badge-failed {
            background: #FEE2E2;
            color: #991B1B;
        }
        .badge-cod {
            background: #F1F5F9;
            color: #475569;
        }
        .badge-online {
            background: #EDE9FE;
            color: #5B163A;
        }
        .attachment-card {
            background: #FFFDF7;
            border: 1px dashed #D4AF37;
            border-radius: 8px;
            padding: 12px 16px;
            margin: 20px 0;
            font-size: 12px;
            color: #5B163A;
            display: flex;
            align-items: center;
        }
        .cta-container {
            text-align: center;
            margin: 28px 0 16px 0;
        }
        .btn-admin {
            display: inline-block;
            background-color: #5B163A;
            color: #FFFFFF !important;
            text-decoration: none;
            padding: 13px 26px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13px;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 10px rgba(91, 22, 58, 0.25);
        }
        .footer {
            background-color: #FAF8F5;
            border-top: 1px solid #E8DDD3;
            padding: 20px 24px;
            text-align: center;
            font-size: 11px;
            color: #94A3B8;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            
            <!-- Luxury Header -->
            <div class="header">
                <h1 class="brand-title">Maya Sree Fashion</h1>
                <div class="brand-subtitle">Daily Payment & Reconciliation Report</div>
            </div>

            <!-- Alert Bar -->
            <div class="alert-bar">
                <p class="alert-text">📅 Report Period: {{ $report['display_date'] ?? date('d M Y') }}</p>
                <span class="badge badge-paid">
                    {{ $kpis['settlement_status'] ?? 'PROCESSED' }}
                </span>
            </div>

            <div class="content">

                <!-- Executive KPIs -->
                <div class="section-title">Executive Performance Summary</div>
                <div class="kpi-grid">
                    <div class="kpi-row">
                        <div class="kpi-cell">
                            <div class="kpi-box">
                                <div class="kpi-label">Gross Collections</div>
                                <div class="kpi-value">₹{{ number_format($kpis['gross_total_revenue'] ?? 0, 2) }}</div>
                                <div class="kpi-sub">Total revenue across all orders</div>
                            </div>
                        </div>
                        <div class="kpi-cell">
                            <div class="kpi-box">
                                <div class="kpi-label">Net Bank Settlement</div>
                                <div class="kpi-value kpi-value-green">₹{{ number_format($kpis['net_bank_credited'] ?? 0, 2) }}</div>
                                <div class="kpi-sub">Credited to Bank Account</div>
                            </div>
                        </div>
                    </div>
                    <div class="kpi-row">
                        <div class="kpi-cell">
                            <div class="kpi-box">
                                <div class="kpi-label">Successful Payments</div>
                                <div class="kpi-value" style="color: #1E293B;">
                                    {{ $kpis['successful_count'] ?? 0 }} <span style="font-size: 12px; font-weight: normal; color: #64748B;">/ {{ $kpis['total_payment_attempts'] ?? 0 }} attempts</span>
                                </div>
                                <div class="kpi-sub" style="color: #166534; font-weight: 600;">
                                    {{ $kpis['success_rate'] ?? 100 }}% Success Rate
                                </div>
                            </div>
                        </div>
                        <div class="kpi-cell">
                            <div class="kpi-box">
                                <div class="kpi-label">Cash on Delivery</div>
                                <div class="kpi-value" style="color: #475569;">₹{{ number_format($kpis['cod_total_amount'] ?? 0, 2) }}</div>
                                <div class="kpi-sub">{{ $kpis['cod_count'] ?? 0 }} COD Orders Booked</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cashfree Gateway Settlement Ledger -->
                <div class="section-title">Cashfree Gateway Reconciliation</div>
                <table class="recon-table">
                    <tr>
                        <td class="recon-label">Gross Online Processed:</td>
                        <td class="recon-value">₹{{ number_format($kpis['total_online_collection'] ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="recon-label">Gateway Service Fee (MDR):</td>
                        <td class="recon-value" style="color: #991B1B;">-₹{{ number_format($kpis['gateway_fee'] ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="recon-label">GST on Gateway Fee (18%):</td>
                        <td class="recon-value" style="color: #991B1B;">-₹{{ number_format($kpis['gateway_tax'] ?? 0, 2) }}</td>
                    </tr>
                    <tr class="recon-highlight">
                        <td>Net Credited Settlement:</td>
                        <td class="recon-value" style="color: #166534;">₹{{ number_format($kpis['net_bank_credited'] ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="recon-label">Bank Settlement UTR:</td>
                        <td class="recon-value" style="font-family: monospace; color: #5B163A;">
                            {{ $kpis['settlement_utr'] ?? '—' }}
                        </td>
                    </tr>
                </table>

                <!-- Payment Modes Breakdown -->
                <div class="section-title">Payment Mode Distribution</div>
                <table class="methods-table">
                    <thead>
                        <tr>
                            <th>Payment Method</th>
                            <th style="text-align: center;">Txn Count</th>
                            <th style="text-align: right;">Amount (INR)</th>
                            <th style="text-align: right;">Share</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($methodBreakdown as $method)
                            @if($method['count'] > 0 || $method['amount'] > 0)
                            <tr>
                                <td style="font-weight: 600; color: #1E293B;">{{ $method['label'] }}</td>
                                <td style="text-align: center; color: #64748B;">{{ $method['count'] }}</td>
                                <td style="text-align: right; font-weight: 600;">₹{{ number_format($method['amount'], 2) }}</td>
                                <td style="text-align: right; color: #5B163A; font-weight: 700;">{{ $method['percentage'] }}%</td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>

                <!-- Top Transactions Ledger Preview -->
                @if(!empty($transactions))
                <div class="section-title">Recent Transactions Preview (Top {{ min(count($transactions), 10) }})</div>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Method</th>
                            <th style="text-align: right;">Amount</th>
                            <th style="text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(array_slice($transactions, 0, 10) as $txn)
                        <tr>
                            <td>
                                <strong style="color: #5B163A; font-family: monospace;">{{ $txn['order_number'] }}</strong>
                                <div style="font-size: 10px; color: #94A3B8;">{{ $txn['time_formatted'] ?? '' }}</div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #1E293B;">{{ $txn['customer_name'] }}</div>
                                <div style="font-size: 10px; color: #64748B;">{{ $txn['customer_phone'] }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $txn['is_cod'] ? 'badge-cod' : 'badge-online' }}">
                                    {{ $txn['method'] }}
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #1E293B;">
                                ₹{{ number_format($txn['amount'], 2) }}
                            </td>
                            <td style="text-align: center;">
                                @if(in_array(strtolower($txn['payment_status']), ['captured', 'paid', 'success']))
                                    <span class="badge badge-paid">PAID</span>
                                @elseif(in_array(strtolower($txn['payment_status']), ['failed', 'user_dropped']))
                                    <span class="badge badge-failed">FAILED</span>
                                @else
                                    <span class="badge badge-pending">{{ strtoupper($txn['payment_status']) }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif

                <!-- Attachment notice -->
                <div class="attachment-card">
                    <div>
                        📎 <strong>Attached Spreadsheet:</strong> Full itemized transaction ledger is attached as <code>daily_cashfree_payment_report_{{ $report['report_date'] ?? date('Y-m-d') }}.csv</code> for your accounting and spreadsheet records.
                    </div>
                </div>

                <!-- Admin Action Button -->
                <div class="cta-container">
                    <a href="{{ url('/admin/reports/payments') }}" class="btn-admin" target="_blank">
                        View Complete Reports on Admin Dashboard →
                    </a>
                </div>

            </div>

            <!-- Footer -->
            <div class="footer">
                <strong>Maya Sree Fashion</strong> &bull; Automated Daily Finance & Reconciliation System<br>
                Generated on {{ $report['generated_at'] ?? date('d M Y, h:i A') }} IST.
            </div>

        </div>
    </div>
</body>
</html>
