<?php

namespace App\Console\Commands;

use App\Services\DailyPaymentReportService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Throwable;

class SendDailyPaymentReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:send-daily-payment-report 
                            {--date= : Target reporting date in YYYY-MM-DD format (or "yesterday", "today")} 
                            {--email= : Specific recipient email to send the report to (overrides settings)} 
                            {--dry-run : Preview report KPIs and transaction counts without sending an email} 
                            {--force : Force send even if disabled in settings}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch Cashfree payment gateway & order data, generate daily payment report, and email it to stakeholders.';

    /**
     * Execute the console command.
     */
    public function handle(DailyPaymentReportService $reportService): int
    {
        $dateInput = $this->option('date');
        $emailInput = $this->option('email');
        $isDryRun = (bool) $this->option('dry-run');
        $isForced = (bool) $this->option('force');

        // Resolve target date
        $targetDate = Carbon::yesterday();
        if (!empty($dateInput)) {
            if (strtolower($dateInput) === 'today') {
                $targetDate = Carbon::today();
            } elseif (strtolower($dateInput) !== 'yesterday') {
                try {
                    $targetDate = Carbon::parse($dateInput);
                } catch (Throwable $e) {
                    $this->error("Invalid date format provided: {$dateInput}. Please use YYYY-MM-DD.");
                    return 1;
                }
            }
        }

        $this->info("========================================================================");
        $this->info("  MAYA SREE FASHION • DAILY CASHFREE PAYMENT & SETTLEMENT REPORT");
        $this->info("========================================================================");
        $this->line("Target Date   : <comment>{$targetDate->format('d M Y')} ({$targetDate->format('Y-m-d')})</comment>");
        $this->line("Execution Time: <comment>" . Carbon::now()->format('d M Y, h:i:s A') . "</comment>");

        if ($isDryRun) {
            $this->warn("[DRY-RUN MODE ACTIVATED] Generating data preview only (no email will be sent).");
            
            $reportData = $reportService->generateReportData($targetDate);
            $kpis = $reportData['kpis'] ?? [];

            $this->newLine();
            $this->info("--- EXECUTIVE REPORT METRICS ---");
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Gross Total Revenue', '₹' . number_format($kpis['gross_total_revenue'] ?? 0, 2)],
                    ['Gross Online Collections', '₹' . number_format($kpis['total_online_collection'] ?? 0, 2)],
                    ['Cash on Delivery (COD)', '₹' . number_format($kpis['cod_total_amount'] ?? 0, 2)],
                    ['Net Bank Settlement', '₹' . number_format($kpis['net_bank_credited'] ?? 0, 2)],
                    ['Gateway Service Fee (MDR + GST)', '₹' . number_format($kpis['total_fee_and_tax'] ?? 0, 2)],
                    ['Total Payment Attempts', $kpis['total_payment_attempts'] ?? 0],
                    ['Successful Payments Count', $kpis['successful_count'] ?? 0],
                    ['Failed / Dropped Count', $kpis['failed_count'] ?? 0],
                    ['Success Rate', ($kpis['success_rate'] ?? 100) . '%'],
                    ['Settlement Status', $kpis['settlement_status'] ?? 'PENDING'],
                    ['Settlement Bank UTR', $kpis['settlement_utr'] ?? '—'],
                    ['Total Items in Ledger', $reportData['transactions_count'] ?? 0],
                ]
            );

            $this->info("Dry-run preview completed successfully.");
            return 0;
        }

        $this->line("Compiling Cashfree settlements & local transactions...");
        $channel = (!empty($emailInput) || !empty($dateInput) || $isForced) ? 'cli' : 'scheduled';
        $result = $reportService->sendDailyReport($targetDate->format('Y-m-d'), $emailInput, $isForced, $channel);


        if ($result['success']) {
            $this->info("✔ SUCCESS: " . $result['message']);
            if (!empty($result['recipients'])) {
                $this->line("Dispatched to: <comment>" . implode(', ', $result['recipients']) . "</comment>");
            }
            if (!empty($result['kpis'])) {
                $this->line("Total Revenue: <comment>₹" . number_format($result['kpis']['gross_total_revenue'] ?? 0, 2) . "</comment> | Orders: <comment>" . ($result['transactions_count'] ?? 0) . "</comment>");
            }
            return 0;
        }

        if (!empty($result['skipped'])) {
            $this->warn("⚠ SKIPPED: " . $result['message']);
            $this->line("Tip: Use --force to bypass the disabled setting check.");
            return 0;
        }

        $this->error("✖ FAILED: " . ($result['message'] ?? 'Unknown error occurred'));
        return 1;
    }
}
