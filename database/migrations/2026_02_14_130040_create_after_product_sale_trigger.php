<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateAfterProductSaleTrigger extends Migration
{
    public function up()
    {
        DB::unprepared(<<<'SQL'
DROP TRIGGER IF EXISTS `after_product_sale_trigger`;
CREATE TRIGGER `after_product_sale_trigger` AFTER INSERT ON `product_sales` FOR EACH ROW
BEGIN
DECLARE trans_id INT;
DECLARE warehouse_id_val INT;
DECLARE product_qty_before INT;
DECLARE warehouse_qty_before INT;
DECLARE sales_ref_no VARCHAR(50);
DECLARE product_type VARCHAR(50);
SELECT id, warehouse_id, reference_no INTO trans_id, warehouse_id_val, sales_ref_no FROM sales WHERE id = NEW.sale_id FOR UPDATE;
SELECT qty, type INTO product_qty_before, product_type FROM products WHERE id = NEW.product_id FOR UPDATE;
IF NEW.variant_id IS NOT NULL THEN
    SELECT qty INTO product_qty_before FROM product_variants WHERE id = NEW.variant_id FOR UPDATE;
    IF product_type NOT IN('digital','combo') THEN
        UPDATE product_variants SET qty = qty - NEW.qty WHERE id = NEW.variant_id;
    END IF;
ELSE
    IF product_type NOT IN('digital','combo') THEN
        UPDATE products SET qty = qty - NEW.qty WHERE id = NEW.product_id;
    END IF;
END IF;
SELECT qty INTO warehouse_qty_before FROM product_warehouse WHERE product_id = NEW.product_id AND warehouse_id = warehouse_id_val FOR UPDATE;
IF product_type NOT IN('digital','combo') THEN
    UPDATE product_warehouse SET qty = qty - NEW.qty WHERE product_id = NEW.product_id AND warehouse_id = warehouse_id_val;
END IF;
IF product_type IN ('digital','combo') THEN
    INSERT INTO record (transaction_id, warehouse_id, product_id, reference_no, transaction_type, product_qty_before, product_qty_after, warehouse_qty_before, warehouse_qty_after)
    VALUES (trans_id, warehouse_id_val, NEW.product_id, sales_ref_no, 1, product_qty_before, product_qty_before, warehouse_qty_before, warehouse_qty_before);
ELSE
    INSERT INTO record (transaction_id, warehouse_id, product_id, reference_no, transaction_type, product_qty_before, product_qty_after, warehouse_qty_before, warehouse_qty_after)
    VALUES (trans_id, warehouse_id_val, NEW.product_id, sales_ref_no, 1, product_qty_before, (product_qty_before - NEW.qty), warehouse_qty_before, (warehouse_qty_before - NEW.qty));
END IF;
END;
SQL
        );
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS `after_product_sale_trigger`;');
    }
}
