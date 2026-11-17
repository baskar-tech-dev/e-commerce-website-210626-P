-- ============================================================
-- Maya Sree Fashion - GST 5% Migration & Apply Script
-- Description:
-- 1. Updates products to 5% GST and HSN 6211.
-- 2. Back-calculates 5% GST on past order_items from inclusive prices.
-- 3. Back-calculates and splits orders into Taxable Subtotal, CGST, SGST, IGST.
-- ============================================================

-- 1. Update Products Table
UPDATE `products`
SET 
    `gst_rate` = 5.00,
    `tax_category` = 'standard',
    `hsn_code` = COALESCE(`hsn_code`, '6211')
WHERE `gst_rate` = 0.00 OR `gst_rate` IS NULL;

-- Set default column value to 5.00 for products and order_items
ALTER TABLE `products` 
    ALTER COLUMN `gst_rate` SET DEFAULT 5.00;

ALTER TABLE `order_items` 
    ALTER COLUMN `tax_rate` SET DEFAULT 5.00;

-- 2. Back-calculate 5% Inclusive GST for all Order Items
UPDATE `order_items`
SET 
    `tax_rate` = 5.00,
    `tax_amount` = ROUND(`total_price` - (`total_price` / 1.05), 2)
WHERE (`tax_amount` = 0.00 OR `tax_amount` IS NULL)
  AND `total_price` > 0;

-- 3. Create Temporary Aggregation for Order Level Taxes
DROP TEMPORARY TABLE IF EXISTS `tmp_order_taxes`;

CREATE TEMPORARY TABLE `tmp_order_taxes` AS
SELECT 
    `order_id`,
    ROUND(SUM(`tax_amount`), 2) AS `order_tax_sum`,
    ROUND(SUM(`total_price` - `tax_amount`), 2) AS `order_subtotal_taxable`
FROM `order_items`
GROUP BY `order_id`;

-- 4. Update Intra-State Orders (Tamil Nadu -> CGST 2.5% + SGST 2.5%)
UPDATE `orders` o
JOIN `tmp_order_taxes` t ON o.`id` = t.`order_id`
SET 
    o.`subtotal`    = t.`order_subtotal_taxable`,
    o.`tax_amount`  = t.`order_tax_sum`,
    o.`cgst_amount` = ROUND(t.`order_tax_sum` / 2, 2),
    o.`sgst_amount` = t.`order_tax_sum` - ROUND(t.`order_tax_sum` / 2, 2),
    o.`igst_amount` = 0.00
WHERE LOWER(TRIM(COALESCE(o.`shipping_state`, ''))) IN ('tamil nadu', 'tamilnadu', 'tn', '')
  AND (o.`tax_amount` = 0.00 OR o.`tax_amount` IS NULL);

-- 5. Update Inter-State Orders (All States Outside Tamil Nadu -> IGST 5.0%)
UPDATE `orders` o
JOIN `tmp_order_taxes` t ON o.`id` = t.`order_id`
SET 
    o.`subtotal`    = t.`order_subtotal_taxable`,
    o.`tax_amount`  = t.`order_tax_sum`,
    o.`cgst_amount` = 0.00,
    o.`sgst_amount` = 0.00,
    o.`igst_amount` = t.`order_tax_sum`
WHERE LOWER(TRIM(COALESCE(o.`shipping_state`, ''))) NOT IN ('tamil nadu', 'tamilnadu', 'tn', '')
  AND (o.`tax_amount` = 0.00 OR o.`tax_amount` IS NULL);

-- Clean up temporary table
DROP TEMPORARY TABLE IF EXISTS `tmp_order_taxes`;

-- Done!
SELECT 
    COUNT(*) AS total_orders_updated,
    ROUND(SUM(tax_amount), 2) AS total_tax_calculated,
    ROUND(SUM(cgst_amount), 2) AS total_cgst,
    ROUND(SUM(sgst_amount), 2) AS total_sgst,
    ROUND(SUM(igst_amount), 2) AS total_igst
FROM `orders`
WHERE `tax_amount` > 0;
