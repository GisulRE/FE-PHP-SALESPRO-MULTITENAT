<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateAfterProductAdjustmentsTrigger extends Migration
{
    public function up()
    {
        DB::unprepared(<<<'SQL'
DROP TRIGGER IF EXISTS `after_product_adjustments_trigger`;
CREATE TRIGGER `after_product_adjustments_trigger` BEFORE INSERT ON `product_adjustments` FOR EACH ROW
BEGIN
DECLARE trans_id INT;
DECLARE warehouse_id_val INT;
DECLARE product_qty_before INT;
DECLARE warehouse_qty_before INT;
DECLARE purchase_ref_no VARCHAR(50);
DECLARE product_type VARCHAR(50);
SELECT id, warehouse_id, reference_no INTO trans_id, warehouse_id_val, purchase_ref_no FROM adjustments WHERE id = NEW.adjustment_id;
SELECT qty, type INTO product_qty_before, product_type FROM products WHERE id = NEW.product_id;
SELECT qty INTO warehouse_qty_before FROM product_warehouse WHERE product_id = NEW.product_id AND warehouse_id = warehouse_id_val;
IF NEW.action = '+' THEN
    INSERT INTO record (transaction_id, warehouse_id, product_id, reference_no, transaction_type, product_qty_before, product_qty_after, warehouse_qty_before, warehouse_qty_after)
    VALUES (trans_id, warehouse_id_val, NEW.product_id, purchase_ref_no, 5, (product_qty_before - NEW.qty), product_qty_before, warehouse_qty_before - NEW.qty, warehouse_qty_before);
ELSE
    INSERT INTO record (transaction_id, warehouse_id, product_id, reference_no, transaction_type, product_qty_before, product_qty_after, warehouse_qty_before, warehouse_qty_after)
    VALUES (trans_id, warehouse_id_val, NEW.product_id, purchase_ref_no, 5, (product_qty_before + NEW.qty), product_qty_before, (warehouse_qty_before + NEW.qty), warehouse_qty_before);
END IF;
END;
SQL
        );
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS `after_product_adjustments_trigger`;');
    }
}
