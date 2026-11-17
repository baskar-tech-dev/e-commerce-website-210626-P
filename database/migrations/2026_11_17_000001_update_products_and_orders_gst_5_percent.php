<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update column defaults if supported
        try {
            DB::statement("ALTER TABLE `products` ALTER COLUMN `gst_rate` SET DEFAULT 5.00");
        } catch (\Exception $e) {
            try {
                DB::statement("ALTER TABLE `products` CHANGE `gst_rate` `gst_rate` DECIMAL(5,2) NOT NULL DEFAULT '5.00'");
            } catch (\Exception $ex) {}
        }

        try {
            DB::statement("ALTER TABLE `order_items` ALTER COLUMN `tax_rate` SET DEFAULT 5.00");
        } catch (\Exception $e) {
            try {
                DB::statement("ALTER TABLE `order_items` CHANGE `tax_rate` `tax_rate` DECIMAL(5,2) NOT NULL DEFAULT '5.00'");
            } catch (\Exception $ex) {}
        }

        // 2. Update all existing products to 5% GST and standard HSN
        DB::table('products')
            ->where('gst_rate', '<=', 0)
            ->orWhereNull('gst_rate')
            ->update([
                'gst_rate' => 5.00,
                'tax_category' => 'standard',
                'hsn_code' => DB::raw("COALESCE(`hsn_code`, '6211')"),
            ]);

        // 3. Update order items for historical orders
        DB::statement("
            UPDATE `order_items`
            SET 
                `tax_rate` = 5.00,
                `tax_amount` = ROUND(`total_price` - (`total_price` / 1.05), 2)
            WHERE (`tax_amount` = 0.00 OR `tax_amount` IS NULL)
              AND `total_price` > 0
        ");

        // 4. Update orders for historical records
        $ordersToUpdate = DB::table('orders')
            ->where(function ($q) {
                $q->where('tax_amount', 0.00)->orWhereNull('tax_amount');
            })
            ->where('grand_total', '>', 0)
            ->get();

        foreach ($ordersToUpdate as $ord) {
            $items = DB::table('order_items')->where('order_id', $ord->id)->get();
            if ($items->isEmpty()) {
                continue;
            }

            $orderTaxSum = round($items->sum('tax_amount'), 2);
            $orderTaxableSubtotal = round($items->sum('total_price') - $orderTaxSum, 2);

            $shippingState = strtolower(trim($ord->shipping_state ?? ''));
            $isIntraState = in_array($shippingState, ['tamil nadu', 'tamilnadu', 'tn']) || empty($shippingState);

            if ($isIntraState) {
                $cgst = round($orderTaxSum / 2, 2);
                $sgst = round($orderTaxSum - $cgst, 2);
                $igst = 0.00;
            } else {
                $cgst = 0.00;
                $sgst = 0.00;
                $igst = $orderTaxSum;
            }

            DB::table('orders')->where('id', $ord->id)->update([
                'subtotal'    => $orderTaxableSubtotal,
                'tax_amount'  => $orderTaxSum,
                'cgst_amount' => $cgst,
                'sgst_amount' => $sgst,
                'igst_amount' => $igst,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
