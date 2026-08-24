<?php

namespace App\Services;

use App\Mail\OrderPlacedNotificationMail;
use App\Mail\TestNotificationMail;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class OrderNotificationService
{
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
                ]);

                if (app()->bound('mail.manager')) {
                    app('mail.manager')->forgetMailers();
                }
            }
        } catch (Throwable $e) {
            Log::warning("Failed to configure dynamic mailer from database settings: " . $e->getMessage());
        }
    }

    /**
     * Send rich order placement notification to the configured primary & secondary emails.
     */
    public function sendOrderPlacedNotification(Order $order): bool
    {
        try {
            // 1. Check if email notifications are enabled in settings (default true)
            $isEnabled = Setting::get('order_notification_enabled', 'email', true);
            if (is_string($isEnabled)) {
                $isEnabled = filter_var($isEnabled, FILTER_VALIDATE_BOOLEAN);
            }

            if (!$isEnabled) {
                Log::info("Order placed notification is disabled in settings. Skipping for Order #{$order->order_number}");
                return false;
            }

            // 2. Resolve primary notification email
            $primaryEmail = trim((string) Setting::get('primary_order_email', 'email', ''));
            if (empty($primaryEmail) || !filter_var($primaryEmail, FILTER_VALIDATE_EMAIL)) {
                // Fallback to general contact email or global mail from address
                $primaryEmail = trim((string) Setting::get('contact_email', 'general', ''));
            }

            if (empty($primaryEmail) || !filter_var($primaryEmail, FILTER_VALIDATE_EMAIL)) {
                $primaryEmail = config('mail.from.address');
            }

            if (empty($primaryEmail) || !filter_var($primaryEmail, FILTER_VALIDATE_EMAIL)) {
                Log::warning("No valid primary recipient email configured for order notification. Skipping for Order #{$order->order_number}");
                return false;
            }

            // 3. Resolve optional additional CC emails
            $ccList = [];
            $additionalEmailsRaw = (string) Setting::get('additional_order_emails', 'email', '');
            if (!empty($additionalEmailsRaw)) {
                $parts = preg_split('/[,;\s]+/', $additionalEmailsRaw);
                foreach ($parts as $part) {
                    $cleaned = trim($part);
                    if (!empty($cleaned) && filter_var($cleaned, FILTER_VALIDATE_EMAIL) && strcasecmp($cleaned, $primaryEmail) !== 0) {
                        $ccList[] = $cleaned;
                    }
                }
            }

            // 4. Ensure order relations are eager loaded for rendering
            $order->loadMissing(['items.variant.product', 'user']);

            // 5. Configure dynamic mailer
            $this->configureDynamicMailer();

            // 6. Send Mail
            $mailable = new OrderPlacedNotificationMail($order);
            $pendingMail = Mail::to($primaryEmail);
            if (!empty($ccList)) {
                $pendingMail->cc($ccList);
            }

            $pendingMail->send($mailable);

            Log::info("Order placement notification email sent successfully for Order #{$order->order_number} to {$primaryEmail}" . (!empty($ccList) ? " (CC: " . implode(', ', $ccList) . ")" : ""));
            return true;

        } catch (Throwable $e) {
            // Catch all exceptions to prevent breaking the checkout flow
            Log::error("Failed to send order placed notification for Order #{$order->order_number}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Send test notification email to verify SMTP and recipient deliverability.
     */
    public function sendTestNotification(string $recipientEmail): array
    {
        if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Invalid recipient email address provided.',
            ];
        }

        try {
            $this->configureDynamicMailer();
            Mail::to($recipientEmail)->send(new TestNotificationMail($recipientEmail));

            return [
                'success' => true,
                'message' => "Test email successfully sent to {$recipientEmail}.",
            ];
        } catch (Throwable $e) {
            Log::error("Failed to send test email to {$recipientEmail}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage(),
            ];
        }
    }
}
