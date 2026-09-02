<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Automated Scheduled Tasks
|--------------------------------------------------------------------------
|
| Send daily Cashfree payment & settlement reconciliation report to
| configured clients and finance stakeholders every morning at 8:30 AM IST.
|
*/
try {
    $reportTime = \Illuminate\Support\Facades\Schema::hasTable('settings')
        ? (\App\Models\Setting::get('daily_payment_report_time', 'email', '08:30') ?: '08:30')
        : '08:30';
} catch (\Throwable $e) {
    $reportTime = '08:30';
}

if (preg_match('/^([0-9]{1,2}):([0-9]{2})$/', trim($reportTime), $matches)) {
    $reportTime = sprintf('%02d:%02d', (int) $matches[1], (int) $matches[2]);
} else {
    $reportTime = '08:30';
}

Schedule::command('reports:send-daily-payment-report')
    ->dailyAt($reportTime)
    ->timezone('Asia/Kolkata')
    ->name('send-daily-payment-report')
    ->withoutOverlapping()
    ->runInBackground()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Scheduled Daily Payment Report failed to execute.');
    });

