<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailyPaymentReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $reportData;
    public ?string $csvContent;
    public string $csvFilename;

    /**
     * Create a new message instance.
     */
    public function __construct(array $reportData, ?string $csvContent = null, string $csvFilename = 'payment_report.csv')
    {
        $this->reportData = $reportData;
        $this->csvContent = $csvContent;
        $this->csvFilename = $csvFilename;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $displayDate = $this->reportData['display_date'] ?? date('d M Y');
        $totalRev = number_format($this->reportData['kpis']['gross_total_revenue'] ?? 0, 2);
        $ordersCount = $this->reportData['kpis']['total_orders_count'] ?? 0;

        return new Envelope(
            subject: "📊 Daily Payment & Settlement Report - {$displayDate} - ₹{$totalRev} ({$ordersCount} Orders)",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.daily_payment_report',
            with: [
                'report' => $this->reportData,
                'kpis' => $this->reportData['kpis'] ?? [],
                'methodBreakdown' => $this->reportData['method_breakdown'] ?? [],
                'transactions' => $this->reportData['transactions'] ?? [],
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if (empty($this->csvContent)) {
            return [];
        }

        return [
            Attachment::fromData(fn () => $this->csvContent, $this->csvFilename)
                ->withMime('text/csv'),
        ];
    }
}
