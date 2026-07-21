<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Str;

class ShippingService
{
    /**
     * Calculate shipping rate based on subtotal, pincode, method, and estimated weight.
     */
    public function calculateShippingFee(float $subtotal, string $pincode, string $method = 'standard', float $weightKg = 0.5): float
    {
        // Free shipping for orders >= ₹999 under standard delivery
        if ($subtotal >= 999 && $method === 'standard') {
            return 0.00;
        }

        $baseRate = ($method === 'express') ? 150.00 : 100.00;

        // Weight surcharge for parcels > 1.5kg
        if ($weightKg > 1.5) {
            $extraWeight = ceil($weightKg - 1.5);
            $baseRate += ($extraWeight * 40.00);
        }

        return round($baseRate, 2);
    }

    /**
     * Generate printable AWB Courier Shipping Label HTML.
     */
    public function generateAwbLabel(Order $order): string
    {
        $awb = $order->tracking_number ?? ('DEL' . rand(100000000, 999999999));
        $courier = strtoupper($order->courier_name ?? 'DELHIVERY EXPRESS');
        $date = now()->format('d-M-Y');

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <title>AWB SHIPPING LABEL - {$awb}</title>
            <style>
                body { font-family: monospace, sans-serif; padding: 20px; }
                .label-box { width: 380px; border: 2px solid #000; padding: 15px; background: #fff; }
                .courier-header { display: flex; justify-content: space-between; font-weight: bold; border-bottom: 2px solid #000; padding-bottom: 8px; font-size: 14px; }
                .barcode-area { text-align: center; margin: 15px 0; border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 10px 0; }
                .barcode { font-size: 24px; font-weight: bold; letter-spacing: 4px; }
                .address-section { font-size: 11px; margin-bottom: 10px; line-height: 1.4; }
                @media print { body { padding: 0; } }
            </style>
        </head>
        <body>
            <div class='label-box'>
                <div class='courier-header'>
                    <span>{$courier}</span>
                    <span>COD: ₹{$order->grand_total}</span>
                </div>

                <div class='barcode-area'>
                    <div>||| | ||||| || |||||| ||||| |||</div>
                    <div class='barcode'>*{$awb}*</div>
                </div>

                <div class='address-section'>
                    <strong>SHIP TO:</strong><br>
                    {$order->shipping_first_name} {$order->shipping_last_name}<br>
                    Phone: {$order->shipping_phone}<br>
                    {$order->shipping_address_line_1}, {$order->shipping_address_line_2}<br>
                    <strong>{$order->shipping_city}, {$order->shipping_state} - {$order->shipping_postal_code}</strong>
                </div>

                <div style='border-top: 1px solid #000; padding-top: 8px; font-size: 10px;'>
                    <strong>RETURN IF UNDELIVERED TO:</strong><br>
                    MAYA SREE FASHION HUB<br>
                    12/48 Cotton Mill Road, Tirupur, TN - 641604<br>
                    Order Ref: #{$order->order_number} | Date: {$date}
                </div>
            </div>
            <script>window.print();</script>
        </body>
        </html>";
    }

    /**
     * Generate Daily Pickup Manifest HTML for logistics partner handover.
     */
    public function generateManifest(array $orders): string
    {
        $manifestId = 'MNF-' . now()->format('Ymd') . '-' . rand(100, 999);
        $date = now()->format('d M Y, h:i A');
        $count = count($orders);

        $rowsHtml = '';
        foreach ($orders as $idx => $ord) {
            $num = $idx + 1;
            $awb = $ord->tracking_number ?? ('AWB' . rand(100000, 999999));
            $pay = strtoupper($ord->payment_method);
            $amt = number_format($ord->grand_total, 2);

            $rowsHtml .= "
            <tr>
                <td>{$num}</td>
                <td><strong>#{$ord->order_number}</strong></td>
                <td>{$awb}</td>
                <td>{$ord->shipping_first_name} {$ord->shipping_last_name} ({$ord->shipping_postal_code})</td>
                <td>{$pay}</td>
                <td>₹{$amt}</td>
                <td>[ &nbsp; ] Signed</td>
            </tr>";
        }

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <title>DAILY PICKUP MANIFEST - {$manifestId}</title>
            <style>
                body { font-family: sans-serif; padding: 25px; color: #0f172a; font-size: 13px; }
                .header { font-size: 20px; font-weight: bold; color: #4a0e2e; border-bottom: 2px solid #4a0e2e; padding-bottom: 10px; margin-bottom: 15px; }
                table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                th, td { padding: 8px 12px; border: 1px solid #cbd5e1; text-align: left; }
                th { background: #4a0e2e; color: #fff; font-size: 11px; }
                @media print { body { padding: 0; } }
            </style>
        </head>
        <body>
            <div class='header'>MAYA SREE FASHION - DAILY COURIER PICKUP MANIFEST</div>
            <div><strong>Manifest No:</strong> {$manifestId} &nbsp;&nbsp;|&nbsp;&nbsp; <strong>Date:</strong> {$date} &nbsp;&nbsp;|&nbsp;&nbsp; <strong>Total Packages:</strong> {$count}</div>

            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ORDER NO</th>
                        <th>AWB NUMBER</th>
                        <th>DESTINATION & PINCODE</th>
                        <th>PAYMENT</th>
                        <th>COLLECTION</th>
                        <th>AGENT SIGN</th>
                    </tr>
                </thead>
                <tbody>
                    {$rowsHtml}
                </tbody>
            </table>

            <div style='margin-top: 40px; display: flex; justify-content: space-between; font-size: 12px;'>
                <div>Dispatch Officer: ____________________</div>
                <div>Courier Pickup Agent: ____________________</div>
            </div>
            <script>window.print();</script>
        </body>
        </html>";
    }
}
