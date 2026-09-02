<?php

namespace App\Services;

use App\Mail\DailyPaymentReportMail;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;
use Exception;

class DailyPaymentReportService
{
    protected CashfreeService $cashfreeService;

    public function __construct(CashfreeService $cashfreeService)
    {
        $this->cashfreeService = $cashfreeService;
    }

    /**
     * Dynamically apply SMTP & Sender settings from the database if configured.
     */
    public function configureDynamicMailer(): void
    {
        try {
            $smtpHost = Setting::get('smtp_host', 'email');
            $smtpPort = Setting::get('smtp_port', 'email');
            $smtpUsername = Setting::get('smtp_username', 'email');
            $smtpPassword = Setting::get('smtp_password', 'email');
            $senderEmail = Setting::get('sender_email', 'email');
            $senderName = Setting::get('sender_name', 'email');

            // Apply sender identity if configured
            if (!empty($senderEmail) && filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
                config([
                    'mail.from.address' => $senderEmail,
                    'mail.from.name' => $senderName ?: config('app.name', 'Maya Sree Fashion'),
                ]);
            }

            // In production/local, if host & credentials are provided in settings, configure dynamic SMTP
            if (!empty($smtpHost) && app()->environment() !== 'testing') {
                $port = (int) ($smtpPort ?: 587);
                $encryption = $port === 465 ? 'ssl' : 'tls';

                config([
                    'mail.default' => 'smtp',
                    'mail.mailers.smtp.host' => $smtpHost,
                    'mail.mailers.smtp.port' => $port,
                    'mail.mailers.smtp.username' => $smtpUsername ?: null,
                    'mail.mailers.smtp.password' => $smtpPassword ?: null,
                    'mail.mailers.smtp.encryption' => $encryption,
                    'mail.mailers.smtp.timeout' => 30,
                ]);

                if (app()->bound('mail.manager')) {
                    app('mail.manager')->forgetMailers();
                }
            }
        } catch (Throwable $e) {
            Log::warning("Failed to configure dynamic mailer in DailyPaymentReportService: " . $e->getMessage());
        }
    }

    /**
     * Generate comprehensive daily payment report data for a specific date.
     *
     * @param Carbon|null $targetDate Defaults to yesterday
     * @return array
     */
    public function generateReportData(?Carbon $targetDate = null): array
    {
        $tz = 'Asia/Kolkata';
        $targetDate = $targetDate ? $targetDate->copy()->setTimezone($tz) : Carbon::yesterday($tz);
        $startDateTime = $targetDate->copy()->startOfDay();
        $endDateTime = $targetDate->copy()->endOfDay();

        $formattedDate = $targetDate->format('Y-m-d');
        $displayDate = $targetDate->format('d M Y');


        // 1. Fetch Local Payments & Orders
        $payments = collect();
        $orders = collect();
        try {
            if (class_exists(Payment::class)) {
                $payments = Payment::with(['order.user'])
                    ->whereBetween('created_at', [$startDateTime, $endDateTime])
                    ->orderBy('created_at', 'desc')
                    ->get();
            }

            if (class_exists(Order::class)) {
                $orders = Order::with(['user', 'items'])
                    ->whereBetween('created_at', [$startDateTime, $endDateTime])
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        } catch (Throwable $dbEx) {
            Log::warning("DailyPaymentReportService: Database query error (might be running in CLI without DB): " . $dbEx->getMessage());
        }

        // 2. Fetch Cashfree Settlements & Recon API data
        $gatewaySettlements = [];
        $gatewayConfigured = false;
        $gatewayEnvironment = 'sandbox';

        try {
            $gatewayConfigured = $this->cashfreeService->isConfigured();
            $gatewayEnvironment = $this->cashfreeService->getEnvironment();

            if ($gatewayConfigured) {
                $filters = [
                    'start_date' => $startDateTime->toIso8601String(),
                    'end_date' => $endDateTime->toIso8601String(),
                ];
                $cfResponse = $this->cashfreeService->getSettlements($filters, null, 50);

                if (isset($cfResponse['settlements']) && is_array($cfResponse['settlements'])) {
                    $gatewaySettlements = $cfResponse['settlements'];
                } elseif (isset($cfResponse['data']) && is_array($cfResponse['data'])) {
                    $gatewaySettlements = $cfResponse['data'];
                } elseif (is_array($cfResponse) && array_is_list($cfResponse)) {
                    $gatewaySettlements = $cfResponse;
                }
            }
        } catch (Throwable $cfEx) {
            Log::warning("DailyPaymentReportService: Cashfree API fetch error: " . $cfEx->getMessage());
        }

        // 3. Calculate Local Database Metrics & Gateway Fallback
        $totalPaymentAttempts = $payments->count();
        $successfulPayments = $payments->filter(function ($p) {
            return in_array(strtolower((string)$p->status), ['captured', 'paid', 'success', 'completed']);
        });
        $failedPayments = $payments->filter(function ($p) {
            return in_array(strtolower((string)$p->status), ['failed', 'user_dropped', 'cancelled']);
        });
        $pendingPayments = $payments->filter(function ($p) {
            return in_array(strtolower((string)$p->status), ['pending']);
        });

        $totalOnlineCollection = (float) $successfulPayments->sum('amount');
        $successfulCount = $successfulPayments->count();
        $failedCount = $failedPayments->count();
        $pendingCount = $pendingPayments->count();

        // 4. Cashfree Settlement & Reconciliation Ledger
        $settlementAmount = 0.0;
        $serviceCharge = 0.0;
        $serviceTax = 0.0;
        $settlementUtrs = [];
        $settlementIds = [];
        $settlementDate = null;
        $settlementStatus = 'PENDING';

        if (!empty($gatewaySettlements)) {
            foreach ($gatewaySettlements as $sItem) {
                $amt = (float) ($sItem['event_settlement_amount'] ?? $sItem['settlement_amount'] ?? $sItem['amount_settled'] ?? $sItem['event_amount'] ?? $sItem['order_amount'] ?? $sItem['amount'] ?? 0.0);
                $charge = (float) ($sItem['payment_service_charge'] ?? $sItem['settlement_charge'] ?? $sItem['service_charge'] ?? 0.0);
                $tax = (float) ($sItem['payment_service_tax'] ?? $sItem['settlement_tax'] ?? $sItem['service_tax'] ?? 0.0);
                $utr = (string) ($sItem['settlement_utr'] ?? $sItem['payment_utr'] ?? $sItem['utr'] ?? '');
                $sId = (string) ($sItem['cf_settlement_id'] ?? $sItem['settlement_id'] ?? '');

                $settlementAmount += $amt;
                $serviceCharge += $charge;
                $serviceTax += $tax;

                if (!empty($utr) && $utr !== '—' && !in_array($utr, $settlementUtrs)) {
                    $settlementUtrs[] = $utr;
                }
                if (!empty($sId) && !in_array($sId, $settlementIds)) {
                    $settlementIds[] = $sId;
                }
                if (empty($settlementDate) && !empty($sItem['settlement_date'])) {
                    $settlementDate = Carbon::parse($sItem['settlement_date'])->format('d M Y, h:i A');
                }
            }
            $settlementStatus = 'SETTLED';

            // If local DB had no recorded payments, sync online collection from gateway settlements
            if ($totalOnlineCollection <= 0 && $settlementAmount > 0) {
                $totalOnlineCollection = $settlementAmount;
                $successfulCount = count($gatewaySettlements);
                $totalPaymentAttempts = count($gatewaySettlements);
            }
        } else {
            // Local fallback estimation
            $settlementAmount = $totalOnlineCollection;
            $serviceCharge = round($totalOnlineCollection * 0.019, 2); // 1.90% standard gateway MDR
            $serviceTax = round($serviceCharge * 0.18, 2); // 18% GST on MDR
        }

        $netBankCredited = max(0, $settlementAmount - ($serviceCharge + $serviceTax));
        $primaryUtr = !empty($settlementUtrs) ? implode(', ', $settlementUtrs) : 'Pending Settlement Cycle';

        $successRate = $totalPaymentAttempts > 0 ? round(($successfulCount / $totalPaymentAttempts) * 100, 1) : 0;

        // COD Orders Breakdown
        $codOrders = $orders->filter(function ($o) {
            return strtolower((string)$o->payment_method) === 'cod';
        });
        $codTotalAmount = (float) $codOrders->sum('grand_total');
        $codCount = $codOrders->count();

        // Total Combined Gross Revenue (Online + COD)
        $grossTotalRevenue = $totalOnlineCollection + $codTotalAmount;
        $totalOrdersCount = $payments->isNotEmpty() ? $orders->count() : ($successfulCount + $codCount);
        $averageOrderValue = $totalOrdersCount > 0 ? round($grossTotalRevenue / $totalOrdersCount, 2) : 0;

        // 5. Payment Method Breakdown (UPI, Cards, NetBanking, COD, etc.)
        $methodBreakdown = [
            'upi' => ['label' => 'UPI (GPay / PhonePe / Paytm)', 'amount' => 0.0, 'count' => 0, 'percentage' => 0],
            'card' => ['label' => 'Credit / Debit Cards', 'amount' => 0.0, 'count' => 0, 'percentage' => 0],
            'netbanking' => ['label' => 'NetBanking', 'amount' => 0.0, 'count' => 0, 'percentage' => 0],
            'wallet' => ['label' => 'Wallets', 'amount' => 0.0, 'count' => 0, 'percentage' => 0],
            'cod' => ['label' => 'Cash on Delivery (COD)', 'amount' => round($codTotalAmount, 2), 'count' => $codCount, 'percentage' => 0],
            'other' => ['label' => 'Other / Online', 'amount' => 0.0, 'count' => 0, 'percentage' => 0],
        ];

        if ($payments->isNotEmpty()) {
            foreach ($successfulPayments as $p) {
                $m = strtolower((string)$p->method);
                $amt = (float) $p->amount;

                if (str_contains($m, 'upi')) {
                    $methodBreakdown['upi']['amount'] += $amt;
                    $methodBreakdown['upi']['count']++;
                } elseif (str_contains($m, 'card') || str_contains($m, 'credit') || str_contains($m, 'debit')) {
                    $methodBreakdown['card']['amount'] += $amt;
                    $methodBreakdown['card']['count']++;
                } elseif (str_contains($m, 'net') || str_contains($m, 'bank') || str_contains($m, 'nb')) {
                    $methodBreakdown['netbanking']['amount'] += $amt;
                    $methodBreakdown['netbanking']['count']++;
                } elseif (str_contains($m, 'wallet')) {
                    $methodBreakdown['wallet']['amount'] += $amt;
                    $methodBreakdown['wallet']['count']++;
                } else {
                    $methodBreakdown['other']['amount'] += $amt;
                    $methodBreakdown['other']['count']++;
                }
            }
        } elseif (!empty($gatewaySettlements)) {
            foreach ($gatewaySettlements as $s) {
                $m = strtolower((string)($s['payment_group'] ?? 'upi'));
                $amt = (float) ($s['event_settlement_amount'] ?? $s['settlement_amount'] ?? $s['event_amount'] ?? $s['order_amount'] ?? $s['amount'] ?? 0.0);

                if (str_contains($m, 'upi')) {
                    $methodBreakdown['upi']['amount'] += $amt;
                    $methodBreakdown['upi']['count']++;
                } elseif (str_contains($m, 'card') || str_contains($m, 'credit') || str_contains($m, 'debit')) {
                    $methodBreakdown['card']['amount'] += $amt;
                    $methodBreakdown['card']['count']++;
                } elseif (str_contains($m, 'net') || str_contains($m, 'bank') || str_contains($m, 'nb')) {
                    $methodBreakdown['netbanking']['amount'] += $amt;
                    $methodBreakdown['netbanking']['count']++;
                } elseif (str_contains($m, 'wallet')) {
                    $methodBreakdown['wallet']['amount'] += $amt;
                    $methodBreakdown['wallet']['count']++;
                } else {
                    $methodBreakdown['other']['amount'] += $amt;
                    $methodBreakdown['other']['count']++;
                }
            }
        }

        // Calculate method percentages relative to gross revenue
        foreach ($methodBreakdown as $key => &$mItem) {
            $mItem['amount'] = round($mItem['amount'], 2);
            $mItem['percentage'] = $grossTotalRevenue > 0 ? round(($mItem['amount'] / $grossTotalRevenue) * 100, 1) : 0;
        }
        unset($mItem);


        // 6. Build Normalized Itemized Transactions Array
        $transactions = [];

        // If we have local payments, prioritize local DB with gateway enrichment
        if ($payments->isNotEmpty()) {
            foreach ($payments as $p) {
                $o = $p->order;
                $u = $o?->user;
                $custName = trim(($o?->shipping_first_name ?? '') . ' ' . ($o?->shipping_last_name ?? ''));
                if (empty($custName)) {
                    $custName = $u?->name ?? 'Customer';
                }

                $transactions[] = [
                    'created_at' => ($p->paid_at ?? $p->created_at)?->format('Y-m-d H:i:s') ?? date('Y-m-d H:i:s'),
                    'time_formatted' => ($p->paid_at ?? $p->created_at)?->format('h:i A') ?? date('h:i A'),
                    'order_number' => $o?->order_number ?? ("MSF-ORD-" . $p->order_id),
                    'gateway_order_id' => $p->gateway_order_id ?? ($o?->gateway_order_id ?? '—'),
                    'gateway_payment_id' => $p->gateway_payment_id ?? '—',
                    'customer_name' => $custName,
                    'customer_phone' => $o?->shipping_phone ?? ($u?->phone ?? '—'),
                    'customer_email' => $u?->email ?? ($o?->shipping_email ?? '—'),
                    'method' => strtoupper($p->method ?? 'ONLINE'),
                    'amount' => (float) $p->amount,
                    'order_status' => $o?->status ?? 'pending',
                    'payment_status' => $p->status,
                    'cashfree_status' => in_array(strtolower((string)$p->status), ['captured', 'paid', 'success']) ? 'SUCCESS' : strtoupper($p->status),
                    'settlement_utr' => $primaryUtr,
                    'failure_reason' => $p->failure_reason ?? '',
                    'is_cod' => false,
                ];
            }
        } elseif (!empty($gatewaySettlements)) {
            // Fallback: Populate directly from Cashfree Recon API if local DB had no records for the date
            foreach ($gatewaySettlements as $s) {
                $amt = (float) ($s['event_settlement_amount'] ?? $s['settlement_amount'] ?? $s['event_amount'] ?? $s['order_amount'] ?? $s['amount'] ?? 0.0);
                $eventTime = !empty($s['event_time']) ? Carbon::parse($s['event_time']) : $startDateTime;
                $utr = $s['settlement_utr'] ?? ($s['payment_utr'] ?? $primaryUtr);

                $transactions[] = [
                    'created_at' => $eventTime->format('Y-m-d H:i:s'),
                    'time_formatted' => $eventTime->format('h:i A'),
                    'order_number' => (string) ($s['order_id'] ?? 'MSF-ONLINE'),
                    'gateway_order_id' => (string) ($s['order_id'] ?? '—'),
                    'gateway_payment_id' => (string) ($s['cf_payment_id'] ?? $s['event_id'] ?? '—'),
                    'customer_name' => (string) ($s['customer_name'] ?? 'Customer'),
                    'customer_phone' => (string) ($s['customer_phone'] ?? '—'),
                    'customer_email' => (string) ($s['customer_email'] ?? '—'),
                    'method' => strtoupper((string) ($s['payment_group'] ?? 'UPI')),
                    'amount' => $amt,
                    'order_status' => 'paid',
                    'payment_status' => 'paid',
                    'cashfree_status' => strtoupper((string) ($s['event_status'] ?? 'SUCCESS')),
                    'settlement_utr' => $utr,
                    'failure_reason' => '',
                    'is_cod' => false,
                ];
            }
        }

        // Add COD orders to transactions ledger
        foreach ($codOrders as $cod) {
            $custName = trim(($cod->shipping_first_name ?? '') . ' ' . ($cod->shipping_last_name ?? ''));
            if (empty($custName)) {
                $custName = $cod->user?->name ?? 'Customer';
            }

            $transactions[] = [
                'created_at' => $cod->created_at?->format('Y-m-d H:i:s') ?? date('Y-m-d H:i:s'),
                'time_formatted' => $cod->created_at?->format('h:i A') ?? date('h:i A'),
                'order_number' => $cod->order_number ?? ("MSF-COD-" . $cod->id),
                'gateway_order_id' => 'N/A (COD)',
                'gateway_payment_id' => 'N/A (COD)',
                'customer_name' => $custName,
                'customer_phone' => $cod->shipping_phone ?? '—',
                'customer_email' => $cod->user?->email ?? ($cod->shipping_email ?? '—'),
                'method' => 'COD',
                'amount' => (float) $cod->grand_total,
                'order_status' => $cod->status ?? 'order_placed',
                'payment_status' => $cod->payment_status ?? 'pending',
                'cashfree_status' => 'COD_BOOKED',
                'settlement_utr' => 'COD (Collect on Delivery)',
                'failure_reason' => '',
                'is_cod' => true,
            ];
        }

        return [
            'report_date' => $formattedDate,
            'display_date' => $displayDate,
            'generated_at' => Carbon::now()->format('d M Y, h:i:s A'),
            'gateway_status' => [
                'is_configured' => $gatewayConfigured,
                'environment' => $gatewayEnvironment,
                'is_production' => $gatewayEnvironment === 'production',
            ],
            'kpis' => [
                'gross_total_revenue' => round($grossTotalRevenue, 2),
                'total_online_collection' => round($totalOnlineCollection, 2),
                'cod_total_amount' => round($codTotalAmount, 2),
                'net_bank_credited' => round($netBankCredited, 2),
                'total_fee_and_tax' => round($serviceCharge + $serviceTax, 2),
                'gateway_fee' => round($serviceCharge, 2),
                'gateway_tax' => round($serviceTax, 2),
                'total_payment_attempts' => $totalPaymentAttempts,
                'successful_count' => $successfulCount,
                'failed_count' => $failedCount,
                'pending_count' => $pendingCount,
                'cod_count' => $codCount,
                'total_orders_count' => $totalOrdersCount,
                'success_rate' => $successRate,
                'average_order_value' => $averageOrderValue,
                'settlement_status' => $settlementStatus,
                'settlement_utr' => $primaryUtr,
                'settlement_date' => $settlementDate ?: 'T+1 (Next Settlement Cycle)',
            ],
            'method_breakdown' => $methodBreakdown,
            'transactions' => $transactions,
            'transactions_count' => count($transactions),
        ];
    }

    /**
     * Generate RFC-compliant CSV content with UTF-8 BOM for spreadsheet software compatibility.
     *
     * @param array $reportData
     * @return string
     */
    public function generateCsvContent(array $reportData): string
    {
        $handle = fopen('php://temp', 'r+');

        // Output UTF-8 BOM for Microsoft Excel compatibility
        fputs($handle, "\xEF\xBB\xBF");

        // Report Meta Header Rows
        fputcsv($handle, ['MAYA SREE FASHION - DAILY PAYMENT & SETTLEMENT REPORT']);
        fputcsv($handle, ['Reporting Date', $reportData['display_date'] ?? date('Y-m-d')]);
        fputcsv($handle, ['Generated At', $reportData['generated_at'] ?? date('d M Y, h:i A')]);
        fputcsv($handle, ['Gross Total Revenue (INR)', number_format($reportData['kpis']['gross_total_revenue'] ?? 0, 2)]);
        fputcsv($handle, ['Online Collections (INR)', number_format($reportData['kpis']['total_online_collection'] ?? 0, 2)]);
        fputcsv($handle, ['Cash on Delivery (INR)', number_format($reportData['kpis']['cod_total_amount'] ?? 0, 2)]);
        fputcsv($handle, ['Net Bank Settlement (INR)', number_format($reportData['kpis']['net_bank_credited'] ?? 0, 2)]);
        fputcsv($handle, ['Settlement Bank UTR', $reportData['kpis']['settlement_utr'] ?? '—']);
        fputcsv($handle, []); // Blank separator line

        // Transaction Ledger Table Headers
        fputcsv($handle, [
            'Date & Time',
            'Order Number',
            'Cashfree Order ID',
            'Cashfree Payment ID',
            'Customer Name',
            'Customer Phone',
            'Customer Email',
            'Payment Method',
            'Amount (INR)',
            'Payment Status',
            'Gateway Status',
            'Settlement UTR',
            'Failure Reason / Remarks',
        ]);

        // Transaction Rows
        foreach ($reportData['transactions'] as $txn) {
            fputcsv($handle, [
                $txn['created_at'] ?? '',
                $txn['order_number'] ?? '',
                $txn['gateway_order_id'] ?? '',
                $txn['gateway_payment_id'] ?? '',
                $txn['customer_name'] ?? '',
                $txn['customer_phone'] ?? '',
                $txn['customer_email'] ?? '',
                $txn['method'] ?? '',
                number_format((float) ($txn['amount'] ?? 0), 2, '.', ''),
                strtoupper((string) ($txn['payment_status'] ?? '')),
                strtoupper((string) ($txn['cashfree_status'] ?? '')),
                $txn['settlement_utr'] ?? '',
                $txn['failure_reason'] ?? '',
            ]);
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        return $csvContent;
    }

    /**
     * Record execution log in database and persistent log file.
     */
    protected function recordLog(
        string $status,
        string $reportDate,
        Carbon $startTime,
        float $startMicrotime,
        string $channel,
        array $recipients = [],
        array $kpis = [],
        int $transactionsCount = 0,
        ?string $errorMessage = null
    ): void {
        $durationMs = (int) round((microtime(true) - $startMicrotime) * 1000);
        if ($durationMs < 0) $durationMs = 0;


        // 1. Write to database model (if table exists)
        try {
            if (class_exists(\App\Models\PaymentReportLog::class)) {
                \App\Models\PaymentReportLog::create([
                    'report_date' => $reportDate,
                    'triggered_at' => $startTime,
                    'status' => strtolower($status),
                    'channel' => strtolower($channel),
                    'recipients' => $recipients,
                    'gross_revenue' => (float) ($kpis['gross_total_revenue'] ?? 0.0),
                    'online_collection' => (float) ($kpis['total_online_collection'] ?? 0.0),
                    'cod_amount' => (float) ($kpis['cod_total_amount'] ?? 0.0),
                    'net_settled' => (float) ($kpis['net_bank_credited'] ?? 0.0),
                    'gateway_fee' => (float) ($kpis['gateway_fee'] ?? 0.0),
                    'gateway_tax' => (float) ($kpis['gateway_tax'] ?? 0.0),
                    'orders_count' => (int) ($kpis['total_orders_count'] ?? 0),
                    'transactions_count' => $transactionsCount,
                    'settlement_utr' => (string) ($kpis['settlement_utr'] ?? ''),
                    'settlement_status' => (string) ($kpis['settlement_status'] ?? ''),
                    'summary_payload' => $kpis,
                    'error_message' => $errorMessage,
                    'duration_ms' => $durationMs,
                ]);
            }
        } catch (Throwable $dbLogEx) {
            Log::warning("DailyPaymentReportService database log error: " . $dbLogEx->getMessage());
        }

        // 2. Append to dedicated log file: storage/logs/daily_payment_report.log
        try {
            $logPath = storage_path('logs/daily_payment_report.log');
            $recipientStr = !empty($recipients) ? implode(', ', $recipients) : 'none';
            $logEntry = sprintf(
                "[%s] [%s] [%s] Date: %s | Gross: ₹%s | Net: ₹%s | Txns: %d | UTR: %s | To: %s | Duration: %dms%s\n",
                Carbon::now('Asia/Kolkata')->format('Y-m-d H:i:s'),
                strtoupper($channel),
                strtoupper($status),
                $reportDate,
                number_format((float) ($kpis['gross_total_revenue'] ?? 0), 2),
                number_format((float) ($kpis['net_bank_credited'] ?? 0), 2),
                $transactionsCount,
                $kpis['settlement_utr'] ?? 'N/A',
                $recipientStr,
                $durationMs,
                $errorMessage ? " | Error: {$errorMessage}" : ""
            );
            @file_put_contents($logPath, $logEntry, FILE_APPEND | LOCK_EX);
        } catch (Throwable $fileLogEx) {
            Log::warning("DailyPaymentReportService file log error: " . $fileLogEx->getMessage());
        }
    }

    /**
     * Dispatch daily payment report email with CSV attachment and record audit logs.
     *
     * @param string|null $customDate YYYY-MM-DD or 'yesterday'
     * @param string|null $customRecipient Optional recipient override
     * @param bool $force Bypass enabled setting check
     * @param string $channel Execution source ('scheduled', 'cli', 'admin_api')
     * @return array
     */
    public function sendDailyReport(?string $customDate = null, ?string $customRecipient = null, bool $force = false, string $channel = 'scheduled'): array
    {
        $startTime = Carbon::now('Asia/Kolkata');
        $startMicrotime = microtime(true);

        try {
            // 1. Resolve Target Date
            $tz = 'Asia/Kolkata';
            $targetDate = Carbon::yesterday($tz);
            if (!empty($customDate)) {
                if (strtolower($customDate) === 'today') {
                    $targetDate = Carbon::today($tz);
                } elseif (strtolower($customDate) !== 'yesterday') {
                    $targetDate = Carbon::parse($customDate, $tz);
                }
            }

            $reportDateStr = $targetDate->format('Y-m-d');

            // 2. Check if daily reports are enabled in settings
            $isEnabled = Setting::get('daily_payment_report_enabled', 'email', true);
            if (is_string($isEnabled)) {
                $isEnabled = filter_var($isEnabled, FILTER_VALIDATE_BOOLEAN);
            }

            if (!$isEnabled && !$force && empty($customRecipient)) {
                $msg = 'Daily payment report is currently disabled in system settings.';
                Log::info("Daily payment report is disabled in system settings. Execution skipped.");
                $this->recordLog('skipped', $reportDateStr, $startTime, $startMicrotime, $channel, [], [], 0, $msg);
                return [
                    'success' => false,
                    'skipped' => true,
                    'message' => $msg,
                ];
            }

            // 3. Resolve primary recipient email(s)
            $primaryEmail = null;
            $ccList = [];

            if (!empty($customRecipient) && filter_var($customRecipient, FILTER_VALIDATE_EMAIL)) {
                $primaryEmail = $customRecipient;
            } else {
                // Read configured recipient settings
                $recipientsRaw = (string) Setting::get('daily_payment_report_recipients', 'email', '');
                if (!empty($recipientsRaw)) {
                    $parts = preg_split('/[,;\s]+/', $recipientsRaw);
                    foreach ($parts as $p) {
                        $cleaned = trim($p);
                        if (!empty($cleaned) && filter_var($cleaned, FILTER_VALIDATE_EMAIL)) {
                            if (empty($primaryEmail)) {
                                $primaryEmail = $cleaned;
                            } else {
                                $ccList[] = $cleaned;
                            }
                        }
                    }
                }

                // Fallbacks if no specific report recipients are set
                if (empty($primaryEmail)) {
                    $envEmail = trim((string) env('DAILY_PAYMENT_REPORT_RECIPIENT', ''));
                    if (!empty($envEmail) && filter_var($envEmail, FILTER_VALIDATE_EMAIL)) {
                        $primaryEmail = $envEmail;
                    }
                }
                if (empty($primaryEmail)) {
                    $primaryEmail = trim((string) Setting::get('primary_order_email', 'email', ''));
                }
                if (empty($primaryEmail) || !filter_var($primaryEmail, FILTER_VALIDATE_EMAIL)) {
                    $primaryEmail = trim((string) Setting::get('contact_email', 'general', ''));
                }
                if (empty($primaryEmail) || !filter_var($primaryEmail, FILTER_VALIDATE_EMAIL)) {
                    $primaryEmail = config('mail.from.address');
                }

                // Add configured secondary CC emails
                $ccRaw = (string) Setting::get('daily_payment_report_cc', 'email', '');
                if (!empty($ccRaw)) {
                    $ccParts = preg_split('/[,;\s]+/', $ccRaw);
                    foreach ($ccParts as $cp) {
                        $cleaned = trim($cp);
                        if (!empty($cleaned) && filter_var($cleaned, FILTER_VALIDATE_EMAIL) && strcasecmp($cleaned, $primaryEmail) !== 0 && !in_array($cleaned, $ccList)) {
                            $ccList[] = $cleaned;
                        }
                    }
                }
            }

            $allRecipients = array_values(array_filter(array_merge([$primaryEmail], $ccList)));

            if (empty($primaryEmail) || !filter_var($primaryEmail, FILTER_VALIDATE_EMAIL)) {
                $msg = "No valid recipient email configured for Daily Payment Report.";
                Log::warning($msg);
                $this->recordLog('failed', $reportDateStr, $startTime, $startMicrotime, $channel, [], [], 0, $msg);
                return [
                    'success' => false,
                    'message' => $msg,
                ];
            }

            // 4. Generate Report & CSV Buffer
            $reportData = $this->generateReportData($targetDate);
            $csvContent = $this->generateCsvContent($reportData);
            $csvFilename = 'daily_cashfree_payment_report_' . $reportData['report_date'] . '.csv';

            // 5. Configure dynamic SMTP
            $this->configureDynamicMailer();

            // 6. Dispatch Mail with retry for transient network hiccups
            $mailable = new DailyPaymentReportMail($reportData, $csvContent, $csvFilename);
            $pendingMail = Mail::to($primaryEmail);
            if (!empty($ccList)) {
                $pendingMail->cc($ccList);
            }

            retry(3, function () use ($pendingMail, $mailable) {
                $pendingMail->send($mailable);
            }, 1000);

            $recipientLog = $primaryEmail . (!empty($ccList) ? " (CC: " . implode(', ', $ccList) . ")" : "");
            Log::info("Daily payment report successfully dispatched for {$reportData['report_date']} to {$recipientLog}");

            // 7. Record Success Log
            $this->recordLog(
                'success',
                $reportData['report_date'],
                $startTime,
                $startMicrotime,
                $channel,
                $allRecipients,
                $reportData['kpis'] ?? [],
                $reportData['transactions_count'] ?? 0
            );

            return [
                'success' => true,
                'message' => "Daily payment report for {$reportData['display_date']} successfully dispatched to {$recipientLog}.",
                'report_date' => $reportData['report_date'],
                'recipients' => $allRecipients,
                'kpis' => $reportData['kpis'],
                'transactions_count' => $reportData['transactions_count'],
            ];

        } catch (Throwable $e) {
            $errorMsg = $e->getMessage();
            Log::error("DailyPaymentReportService@sendDailyReport failed: " . $errorMsg, [
                'trace' => $e->getTraceAsString(),
            ]);

            $this->recordLog(
                'failed',
                $targetDate?->format('Y-m-d') ?? date('Y-m-d'),
                $startTime,
                $startMicrotime,
                $channel,
                $allRecipients ?? [],
                [],
                0,
                $errorMsg
            );


            return [
                'success' => false,
                'message' => 'Failed to send daily payment report: ' . $errorMsg,
            ];
        }
    }

}

